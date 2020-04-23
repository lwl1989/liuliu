<?php
namespace App\Http\Controllers\Users;

use App\Exceptions\ErrorConstant;
use App\Library\ArrayParse;
use App\Library\Auth\Encrypt;
use App\Library\Auth\RedisTokenCache;
use App\Library\Constant\Common;
use App\Library\Constant\Message;
use App\Library\Constant\User;
use App\Library\CountryCode;
use App\Library\Message\Badge;
use App\Library\Message\FcmMessage;
use App\Library\Message\UserRelations;
use App\Models\Admin;
use App\Models\Model;
use App\Models\RegisterUsers\UserOpLog;
use App\Models\RegisterUsers\UserProfile;
use App\Models\RegisterUsers\Users;
use App\Services\Gold\GoldSendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Services\RegisterUsers\UsersService;
use Illuminate\Support\Facades\Crypt;
use App\Library\RedisFacade as Redis;
use Illuminate\Support\Facades\Session;

class UsersController extends HomeController
{
	const UNION_TABLE = 'user_profile';

	/**
	 * 列表
	 * @param Request $request
	 * @return array
	 */
	public function select(Request $request) : array
	{
		$page		= $request->get('page', 1);
		$limit		= $request->get('limit', 10);
		$type		= $request->get('type', 0);
		$key		= $request->get('key', '');
		$device_type= $request->get('device_type', 0);
		$sex_type   = $request->get('sex_type', 0);
		$time_type  = $request->get('time_type','');
		$time       = $request->get('time','');
		$digitalize_type = $request->get('digitalize_type', '-1');

		if (!empty($time) && $time != 'null') {
			$time = explode(',', $time);
			$date_start = $time[0];
			$date_end = $time[1];
		}

		$conditions = $this->search($type, $key, $device_type, $date_start ?? '', $date_end ?? '', $time_type, $digitalize_type, $sex_type);
		$usersService = new UsersService();
		$users = $usersService::limit($conditions, $limit, $page, false, 1);
		$users = ArrayParse::encryptData($users,['id_number','residence','passport','email','source','source_id']);
		$users = $this->deal($users);

		return ['data' => $users];
	}

	/**
	 * 總數
	 * @param Request $request
	 * @return array
	 */
	public function count(Request $request) : array
	{
		$type		= $request->get('type', 0);
		$key		= $request->get('key', '');
		$device_type= $request->get('device_type', 0);
		$sex_type   = $request->get('sex_type', 0);
		$time_type  = $request->get('time_type','');
		$time       = $request->get('time','');
		$digitalize_type = $request->get('digitalize_type', '-1');

		if (!empty($time) && $time != 'null') {
			$time = explode(',', $time);
			$date_start = $time[0];
			$date_end = $time[1];
		}

		$conditions		= $this->search($type, $key, $device_type, $date_start ?? '', $date_end ?? '', $time_type, $digitalize_type, $sex_type);
		$usersService	= new UsersService();
		$count = $usersService::count($conditions, false, 1);

		return [ 'count' => $count ];
	}


	/**
	 * 獲取當前登入的用戶
	 * @param  Request $request
	 * @return array
	 */
	public function getAuth(Request $request) : array
	{
		$token = $request->getPassword();
		$md5 = md5($token);

		$user = Users::query()->where('id',Auth::id())->first()->toArray();

		//Redis::setex('user:login:'.$md5, 3600, json_encode($user));

		$user['country_code'] = $user['code'];
		$user['event_active'] = [
			User::GOLD_EVENT_REGISTER   =>  UsersService::getEventJoined(Auth::id(), User::GOLD_EVENT_REGISTER),
			User::GOLD_EVENT_DIGITAL_PERSON   =>  UsersService::getEventJoined(Auth::id(), User::GOLD_EVENT_DIGITAL_PERSON),
		];
		$user['admin_id'] = $this->hasBindAdmin($user['code'], $user['mobile']);

		//是否有查詢驗證碼的權限
		$user['is_password'] = false;
		if ($user['admin_id'] > 0) {
			$admin = Admin::query()
				->find($user['admin_id'], ['permissions'])
				->toArray();
			if (!empty($admin['permissions']) and !is_array($admin['permissions'])) {
				$admin['permissions'] = json_decode($admin['permissions']);

				if (in_array("password", $admin['permissions'])) {
					$user['is_password'] = true;
				}
			}
		}

		return ['code' => 0, 'response' => $user];
	}

	/**
	 * 客戶端變化token post請求這個接口
	 * @param Request $request
	 * @return array
	 */
	public function changeToken(Request $request)
	{
		$token = $request->post('fcm_token', '');

		if($token == '') {
			return ['code'=>ErrorConstant::PARAMS_LOST];
		}

		//fcm_token去重(因為ios同一隻手機登錄兩個賬號 fcm_token值相同)
		$userIds = Users::query()->where('fcm_token', $token)->get(['id'])->toArray();
		if (count($userIds) > 0) {
			foreach ($userIds as $v) {
				Users::query()->where('id', $v['id'])->update(['fcm_token' => '']);
			}
		}

		$users = new UsersService();

		$users->setAttr('fcm_token', $token);
		try {
			$user = Auth::user()->toArray();
			$badge = Badge::getBadge($user['device_uuid'], $user['id']);
			UserRelations::sendUser($user['id'], $user['device_type'], $user['device_uuid'], $badge, $token);
		}catch (\Exception $e) {

		}
		$row = $users->update(Auth::id());
		RedisTokenCache::clearAttr();
		return [ 'row' =>  $row ];
	}

	/**
	 * 更新用戶資料
	 * @param Request $request
	 * @return array
	 */
	public function upProfile(Request $request) : array
	{
		$profile = ArrayParse::arrayCopy([
			'id_number', 'nationality', 'address', 'email','name','passport',
			'sex', 'student_card', 'car_card', 'agedness_card', 'disability_card',
			'passport','residence', 'digitalize_type'
		], $request->all());

		if(count($profile) == 0) {
			return ['code' => ErrorConstant::PARAMS_LOST];
		}

		$sendNum = 0;
		try {
			$id = Auth::id();
			$existsProfile = Auth::user()->toArray();

			$needSend = UsersService::updateProfile($profile, $existsProfile);
			if (!empty($needSend) and is_array($needSend)) {
				/** @var Model $user */
				$name = $profile['name'] ?? ($existsProfile['name'] ?? '');
				foreach ($needSend as $key) {
					$num = GoldSendService::sendToUserRegister($id, $key);
					if ($num > 0) {
						$sendNum+=$num;
						FcmMessage::sendGoldMessage([
							'platform' => $existsProfile['platform'] ?? '',
							'token' => $existsProfile['fcm_token'] ?? '',
						], $num, $name, User::getUserGoldType($key));
					}
				}
			}

			UsersService::actionLog($id, User::ACTION_LOG_TYPE_USER_UPDATE, 'USER UPDATE PROFILE');
		} catch (\Exception $exception) {
			return ['code' => ErrorConstant::SYSTEM_ERR];
		}

		return ['golds' => $sendNum];
	}

	/**
	 * 詳情
	 * @param Request $request
	 * @param string $encrypted
	 * @return array
	 */
	public function get(Request $request, string $encrypted = '') : array
	{
		if ($encrypted === '') {
			$id = $request->input('id', 0);
			if ($id == 0) {
				return [];
			}
		} else {
			try {
				$arr = json_decode(Crypt::decryptString($encrypted), true);
				if (!isset($arr['id'])) {
					throw new \Exception('can\'t find id');
				}

				$id = $arr['id'];
			} catch (\Exception $exception) {
				return ['code' => ErrorConstant::DATA_ERR];
			}
		}

		$user = new UsersService();
		$data = $user->getOne($id);

		if ($encrypted !== '') {
			$data = ArrayParse::encryptData($data);
			unset($data['fcm_token']);
			unset($data['login_token']);
			unset($data['device_uuid']);
		}

		/**! 後台才需要拼接 !**/
		if (strpos($request->path(), 'api/') === false) {
			$data['create_time'] = date('Y-m-d H:i', strtotime($data['create_time']));
			$data['device_name'] = $this->getDeviceName($data['device_type']);
			$data['digitalization'] = $this->turnType($data['digitalize_type']);
			$data['nationality'] = CountryCode::getCountryName($data['nationality']);
			$data['sex'] = $data['sex'] == 1 ? '男' : '女';

			/**! 加密三個字段 !**/
			if (!empty($data['id_number'])) {
				$data['id_number'] = Crypt::encrypt($data['id_number'] . ':' . mt_rand(0, 100));
			}

			if (!empty($data['residence'])) {
				$data['residence'] = Crypt::encrypt($data['residence'] . ':' . mt_rand(0, 100));
			}

			if (!empty($data['passport'])) {
				$data['passport'] = Crypt::encrypt($data['passport'] . ':' . mt_rand(0, 100));
			}

			/**! 用戶的最後操作時間 !**/
			$opLastTime = UserOpLog::query()
				->where('op_id', $data['user_id'])
				->whereIn('op_type', [
					User::ACTION_LOG_TYPE_LOGIN,
					User::ACTION_LOG_TYPE_QUESTIONNAIRE,
					User::ACTION_LOG_TYPE_ACTIVITY,
					User::ACTION_LOG_TYPE_USE_GOLD
				])
				->orderByDesc('id')
				->first(['create_time']);
			$data['op_last_time'] = is_null($opLastTime) ? '' : date('Y-m-d H:i', strtotime($opLastTime->getAttribute('create_time')));

			/**! 最後更新時間(用戶自己修改資料或管理員修改手機號) !**/
			$lastUpdateTime= UserOpLog::query()
				->where('op_id', $data['user_id'])
				->whereIn('op_type', [
					User::ACTION_LOG_TYPE_USER_UPDATE,
					User::ACTION_LOG_TYPE_ADMIN_UPDATE,
				])
				->orderByDesc('id')
				->first();

			$data['last_update_log'] = '';
			$data['last_update_time'] = '';
			if (!is_null($lastUpdateTime)) {
				$data['last_update_time'] = date('Y-m-d H:i', strtotime($lastUpdateTime->getAttribute('create_time')));
				if ($lastUpdateTime->getAttribute('op_type') == User::ACTION_LOG_TYPE_USER_UPDATE) {
					$data['last_update_time'] .= ' '.$data['name'];
				} else {
					$account = Admin::query()->find($lastUpdateTime->getAttribute('ad_id'), ['account']);
					$data['last_update_time'] = $data['last_update_time'].' '.(is_null($account) ? '' : $account->getAttribute('account'));

					$opIns = $lastUpdateTime->getAttribute('op_ins');
					$data['last_update_log'] = ($opIns ? '('.$opIns.') ' : '').$data['last_update_time'];
				}
			}

			$data['acl'] = (strpos(json_decode(Session::get('user'), true)['permissions'], 'users_decode') !== false);
		}

		return ['data' => $data];
	}

	/**
	 * 獲取用戶綁定的admin auth token
	 * @return array
	 * @throws \Exception
	 */
	public function adminToken()
	{
		$users = Users::query()->find(Auth::id(), ['code', 'mobile']);
		if (is_null($users)) {
			return [];
		}

		$adminId = $this->hasBindAdmin($users->getAttribute('code'), $users->getAttribute('mobile'));
		if ($adminId == 0) {
			return [];
		}

		return [
			'id' => $adminId,
			'token' => Encrypt::generateToken(['uid' => $adminId])
		];
	}

	/**
	 * 編輯
	 * @param Request $request
	 * @return array
	 */
	public function update(Request $request) : array
	{
		$id = $request->input('id',0);
		if ($id == 0) {
			return [];
		}

		$type = $request->input('type');
		switch ($type) {
			case 'mobile':
				$mobile = $request->input('mobile');
				if ($mobile == null) {
					$mobile = '';
				}

				$params['mobile'] = ltrim(ltrim($mobile, '0'), '886');
				$params['update_time']	= date('Y-m-d H:i:s', time());

				//判斷新手機號是否已有人使用
				$remain = Users::query()
					->where('mobile', $params['mobile'])
					->get(['id'])
					->toArray();
				if (count($remain) > 0) {
					return ['row' => 2];
				}

				//若原賬號為管理者或縣府員工權限 綁定的手機號需變更
				$users = Users::query()->find($id, ['mobile'])->toArray();//原手機號

				$profile = Admin\AdminProfile::query()
					->where('mobile', $users['mobile'])
					->get(['id'])
					->toArray();

				if (count($profile) > 0) {
					Admin\AdminProfile::query()
						->where('id', $profile[0]['id'])
						->update([
							'mobile' => $params['mobile']
						]);
				}

				//變更手機號 發消息提示客戶端 重新登錄
				$users = Users::query()
					->find($id, ['fcm_token', 'device_type'])
					->toArray();
				if (!empty($users)) {
					$tokens[] = [
						'platform'  => $users['device_type'],
						'token'     => $users['fcm_token']
					];
					FcmMessage::sendSystemMessage(Message::COMMON_EVENT_CHANGE_USERS_MOBILE, $tokens);
				}

				break;
			default :
				return ['row' => 0];
				break;
		}

		UsersService::actionLog($id, User::ACTION_LOG_TYPE_ADMIN_UPDATE, $request->input('content', ''), Auth::id());

		$users = new UsersService();
		foreach ($params as $key=>$value) {
			$users->setAttr($key,$value);
		}

		return [ 'row' => $users->update($id) ];
	}

	public function decodeField(Request $request)
	{
		$data = $request->input('decode_data', '');
		$data = Crypt::decrypt($data);

		return explode(':', $data)[0];
	}

	private function hasBindAdmin($code, $mobile)
	{
		$admin = Admin\AdminProfile::query()
			->join('admin', 'admin.id', 'admin_profile.admin_id')
			->where('admin_profile.code', $code)
			->where('admin_profile.mobile', $mobile)
			->where('admin.status', Common::STATUS_NORMAL)
			->where('admin.deleted', Common::NO_DELETE)
			->first(['admin_profile.admin_id','admin.permissions']);

		if (!is_null($admin) && stripos($admin->getAttribute('permissions'), 'message_activity') !== false) {
			$adminId = $admin->getAttribute('admin_id');
		}

		return $adminId ?? 0;
	}

	private function search($type, $key, $device_type, $date_start, $date_end, $time_type, $digitalize_type, $sex_type)
	{
		$conditions = [];

		if ($key != '') {
			switch ($type){
				case '1':
					$conditions['mobile'] = ['like','%'.$key.'%'];
					break;
				case 2;
					$conditions[self::UNION_TABLE . '.uid'] = ['like','%'.$key.'%'];
					break;
				case 3:
					$conditions[self::UNION_TABLE . '.name'] = ['like','%'.$key.'%'];
					break;
				case 4:
					$conditions[self::UNION_TABLE . '.email'] = ['like','%'.$key.'%'];
					break;
				case 5:
					$conditions[self::UNION_TABLE . '.address'] = ['like','%'.$key.'%'];
					break;
				case 6:
					$code = CountryCode::searchCountryCode($key);
					$conditions[self::UNION_TABLE . '.nationality'] = ['in', $code];
					break;
				case 7:
					$conditions[self::UNION_TABLE . '.id_number'] = ['like','%'.$key.'%'];
					break;
			};
		}

		if ($device_type != 0) {
			$conditions['device_type'] = $device_type;
		}

		if (intval($sex_type) !== 0 && intval($digitalize_type) !== -1) {
			$sex = UserProfile::query()
				->where('sex', $sex_type)
				->where('digitalize_type', $digitalize_type)
				->get(['user_id'])
				->toArray();

			$conditions['id'] = ['in', array_column($sex, 'user_id')];
		} else {
			if (intval($sex_type) !== 0) {
				$sex = UserProfile::query()
					->where('sex', $sex_type)
					->get(['user_id'])
					->toArray();

				$conditions['id'] = ['in', array_column($sex, 'user_id')];
			}

			if (intval($digitalize_type) !== -1) {
				$users = UserProfile::query()
					->where('digitalize_type', $digitalize_type)
					->get(['user_id'])
					->toArray();

				$conditions['id'] = ['in', array_column($users, 'user_id')];
			}
		}

		if (!empty($date_start) && !empty($date_end)) {
			if ($time_type === '1') {
				$conditions['create_time'] = ['between' , [$date_start , $date_end]];
			} else {
				$conditions['update_time'] = ['between' , [$date_start , $date_end]];
			}
		}

		return $conditions;
	}

	private function deal($users)
	{
		if( isset($users) && count($users) > 0 ){
			foreach ($users as &$v) {
				$v['mobile']		= $v['code'] . ' ' . $v['mobile'];
				$v['device_name']	= $this->getDeviceName($v['device_type']);
				$v['digitalization']= $this->turnType($v['digitalize_type']);
				//$v['id_number']		= $this->unionIdNumber($v);
				$v['nationality']	= CountryCode::getCountryName($v['nationality']);
			}
		}

		return $users;
	}

	private function getDeviceName($deviceType)
	{
		return User::getDeviceTypeString(intval($deviceType));
	}

	private function turnType($digitalizeType)
	{
		return $digitalizeType == User::USERS_DIGITALIZE_TYPE_ON ? '是' : '否';
	}
}