<?php

namespace App\Services\RegisterUsers;

use App\Library\Auth\RedisTokenCache;
use App\Library\Constant\Common;
use App\Library\Constant\User;
use App\Models\Department\Department;
use App\Models\Department\DepartmentGroup;
use App\Models\RegisterUsers\GroupUserRelation;
use App\Models\RegisterUsers\UserFcmSub;
use App\Models\RegisterUsers\UserGold;
use App\Models\RegisterUsers\UserOpLog;
use App\Models\RegisterUsers\UserProfile;
use App\Services\Gold\GoldDistributeService;
use App\Services\ServiceBasic;
use App\Models\RegisterUsers\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UsersService extends ServiceBasic
{
    protected $model = Users::class;


    public static function getEventJoined($userId, int $eventType): bool
    {
        $query = UserGold::query();
        $exits = $query->where('user_id', $userId)
            ->where('event_type', $eventType)
            ->first(['id']);
        if (empty($exits)) {
            return false;
        }
        return true;
    }


    /**
     * sub topics
     * @param $departmentId
     * @param $userId
     * @param bool $un
     * @return bool
     */
    public static function subDepartment($departmentId, $userId, bool $un = false): bool
    {
        $exists = UserFcmSub::query()->where('user_id', '=', $userId)
            ->where('department_id', '=', $departmentId)
            ->first(['status', 'id']);

        if (!empty($exists)) {
            $exists = $exists->toArray();
            if ($un) {
                if ($exists['status'] == Common::STATUS_NORMAL) {
                    UserFcmSub::query()
                        ->where('id', '=', $exists['id'])
                        ->update(['status' => Common::STATUS_DISABLE]);
                }
            } else {
                if ($exists['status'] == Common::STATUS_DISABLE) {
                    UserFcmSub::query()
                        ->where('id', '=', $exists['id'])
                        ->update(['status' => Common::STATUS_NORMAL]);
                }
            }
        } else {
            UserFcmSub::query()->insert([
                'user_id' => $userId,
                'department_id' => $departmentId,
                'status' => $un ? Common::STATUS_DISABLE : Common::STATUS_NORMAL
            ]);
        }
        return true;
    }

    /**
     * @param $userId
     * @return array
     */
    public static function getUserDisableDepartment($userId) : array
    {
        return UserFcmSub::query()
            ->where('user_fcm.user_id', '=', $userId)
            ->where('user_fcm.status',Common::STATUS_DISABLE)
            ->join('department', function ($query) {
                $query->on('department.id', '=', 'user_fcm.department_id')
                    ->where('department.status', Common::STATUS_NORMAL)
                    ->where('department.deleted', '=', Common::NO_DELETE);
            })
            ->get(['department.mark'])
	        ->toArray();
    }

    /***
     * @param $userId
     * @return array
     */
    public static function getUserSubDepartment($userId): array
    {
        $count = UserFcmSub::query()
            ->where('user_id', '=', $userId)
            ->join('department', function ($query) {
                $query->on('department.id', '=', 'user_fcm.department_id')
                    ->where('department.status', Common::STATUS_NORMAL)
                    ->where('department.deleted', '=', Common::NO_DELETE);
            })
            ->count('user_id');

//        $departmentCount = Department::query()
//            ->where('status', Common::STATUS_NORMAL)
//            ->where('deleted', '=', Common::NO_DELETE)
//            ->count('id');

        $departments = Department::query()
            ->where('deleted', '=', Common::NO_DELETE)
            ->where('status', Common::STATUS_NORMAL)
            ->get(['id', 'name', 'mark as topic'])->toArray();
        $departments = array_column($departments, null, 'id');

        $exists = [];
        if (count($departments) != $count) {

            $existsUser = [];
            if ($count > 0) {
                $exists = UserFcmSub::query()
                    ->where('user_id', '=', $userId)
                    ->take($count)->get()->toArray();
                $existsUser = array_column($exists, null, 'department_id');
            }
            foreach ($departments as $departmentId => $department) {
                if (!isset($existsUser[$departmentId])) {
                    $exists[] = [
                        'user_id' => $userId,
                        'department_id' => $departmentId,
                        'name' => $department['name'],
                        'topic' => $department['topic'],
                        'status' => Common::STATUS_NORMAL,
                        'create_time' => date('Y-m-d H:i:s')
                    ];
                    UserFcmSub::query()->insert([
                        'user_id' => $userId,
                        'department_id' => $departmentId
                    ]);
                }else{
                    $exists[] = [
                        'user_id' => $userId,
                        'department_id' => $departmentId,
                        'name' => $department['name'],
                        'topic' => $department['topic'],
                        'status' => Common::STATUS_DISABLE,
                        'create_time' => date('Y-m-d H:i:s')
                    ];
                }
            }
        } else {
            $exists = UserFcmSub::query()->where('user_id', '=', $userId)
                ->join('department', function ($query) {
                    $query->on('department.id', '=', 'user_fcm.department_id')
                        ->where('department.deleted', '=', Common::NO_DELETE);
                })
                ->take($count)
                ->get(['user_fcm.user_id', 'user_fcm.status', 'user_fcm.create_time',
                    'user_fcm.department_id', 'department.name as name',
                    'department.mark as topic'])
                ->toArray();
        }
        return $exists;
    }

    /**
     * 更新其他用戶屬性
     * @param array $profile
     * @param array $user
     * @return array
     */
    public static function updateProfile(array $profile, array $user = []): array
    {
        if (empty($user)) {
            $user = Auth::user()->toArray();
        }
        if(isset($profile['mobile'])) {
            unset($profile['mobile']);
        }
        $id = $user['id'];
        $need = [];

        $exists = UserProfile::query()->where('user_id', '=', $id)->first(['id']);
        if (!empty($exists)) {
            UserProfile::query()->where('user_id', '=', $id)->update($profile);
            if (isset($profile['digitalize_type']) and $profile['digitalize_type'] == User::USERS_DIGITALIZE_TYPE_ON) {
                $sent = UserGold::query()->where('user_id', $id)
                    ->where('event_type', User::GOLD_EVENT_DIGITAL_PERSON)
                    ->first(['id']);
                if (empty($sent)) {
                    array_push($need, User::GOLD_EVENT_DIGITAL_PERSON);
                }
            }
        } else {
            UserProfile::query()->insert(array_merge($profile, ['user_id' => $id]));
            if (isset($profile['digitalize_type']) and $profile['digitalize_type'] === User::USERS_DIGITALIZE_TYPE_ON) {
                array_push($need, User::GOLD_EVENT_DIGITAL_PERSON);
            }
        }

        $sendProfile = false;

        if (isset($profile['email'])) {
            if (isset($user['address']) and !empty($user['address'])) {
                $sendProfile = true;
            }
        } else if (isset($profile['address'])) {
            if (isset($user['email']) and !empty($user['email'])) {
                $sendProfile = true;
            }
        }

        if ($sendProfile) {
            $sent = UserGold::query()->where('user_id', $id)
                ->where('event_type', User::GOLD_EVENT_REGISTER)
                ->first(['id']);
            if (empty($sent)) {
                array_push($need, User::GOLD_EVENT_REGISTER);
            }
        }
        RedisTokenCache::clearAttr();
        return $need;
    }

    /**
     * 獲取所有用戶token
     * @param $department_id
     * @return array
     */
    public static function departmentTopic($department_id): array
    {
        $distributeService = new GoldDistributeService();
        $id = $distributeService->resetStage($department_id);

        $department = Department::query()
            ->where('id', $id)
            ->get(['department.mark'])
            ->toArray();

        return ['mark' => $department[0]['mark']];
    }

    /**
     * 獲取指定群組用戶token
     * @param $group_id
     * @return array
     */
    public static function groupUserToken($group_id)
    {
    	//群組被刪除 不可發送
    	$group = DepartmentGroup::query()->find($group_id, ['deleted'])->toArray();
    	if ($group['deleted'] == Common::DELETED) {
    		return [];
	    }

        //帳號隸屬業務單位對應的縣民群組
        $users = GroupUserRelation::query()
            ->where('group_id', '=', $group_id)
            ->get(['group_user_relation.user_id'])
            ->toArray();

        if (isset($users) && count($users) > 0) {
            $user_id = array_column($users, 'user_id');
            $token = Users::query()
                ->whereIn('id', $user_id)
	            ->where('fcm_token', '!=', '')
                ->get(['id', 'fcm_token', 'device_type'])
                ->toArray();

	        $empty_token = Users::query()
		        ->whereIn('id', $user_id)
		        ->where('fcm_token', '')
		        ->get(['id', 'fcm_token', 'device_type'])
		        ->count();

            return ['data' => self::deal($token), 'empty_token' => $empty_token];
        } else {
            return [];
        }
    }

    /**
     * 獲取個人用戶token
     * @param $send_person
     * @return array
     */
    public static function groupPersonToken($send_person)
    {
        $token = Users::query()->whereIn('id', $send_person)
            ->get(['fcm_token', 'device_type'])
            ->toArray();

        return self::deal($token);
    }

    public static function deal($token)
    {
        if (isset($token) && count($token) > 0) {
			$arr_ios = [];
			$arr_android = [];

            foreach ($token as $k => $v) {
            	if ($v['device_type'] == 1) {
            		$token_android[] = $v['fcm_token'];

            		$arr_android = [
            			'platform' => $v['device_type'],
						'token'		=> $token_android
					];
				}

				if ($v['device_type'] == 2) {
					$token_ios[] = $v['fcm_token'];

					$arr_ios = [
						'platform' => $v['device_type'],
						'token'		=> $token_ios
					];
				}
            }

            if (count($arr_android) == 0 && count($arr_ios) > 0) {
				return $arr_ios;
			}

			if (count($arr_android) > 0 && count($arr_ios) == 0) {
				return $arr_android;
			}

			if (count($arr_android) > 0 && count($arr_ios) > 0) {
				return [$arr_android, $arr_ios];
			}
        } else {
            return [];
        }
    }

    /**
     * 判斷用戶是否存在
     * @param string $code
     * @param string $mobile
     * @return array
     */
    public static function userExistsByMobile(string $code = '', string $mobile): array
    {
        $query = Users::query();
        if($code != '') {
            //兼容查詢
            $query->where('code', '=', $code);
        }
        $user = $query
            ->where('mobile', '=', ltrim($mobile,'0'))
            ->where('status', '=', Common::STATUS_NORMAL)
            ->where('deleted', '=', Common::NO_DELETE)
            ->first(['id','fcm_token as token','device_type as platform','code','mobile','admin_id', 'device_uuid']);

        if (empty($user)) {
            return [];
        }
        $user = $user->toArray();
        $profile = UserProfile::query()->where('user_id', $user['id'])
            ->first(['name']);
        if(!empty($profile)) {
            $user = array_merge($user, $profile->toArray());
        }
        return $user;
    }

    /**
     * 記錄日誌
     * @param $userId
     * @param string $uuid
     * @param string $ip
     * @param string $mobile
     */
    public static function loginLog($userId, string $uuid, string $ip, string $mobile = '')
    {
        $userLog = new Class extends Model
        {
            protected $table = 'user_login_log';
            const CREATED_AT = 'create_time';
            const UPDATED_AT = 'update_time';
        };

        $userLog = new $userLog();
        $userLog->user_id = $userId;
        $userLog->mobile = $mobile;
        $userLog->uuid = $uuid;
        $userLog->ip = $ip;
        $userLog->save();
    }

    /**
     * 行為日誌記錄
     * @param $userId
     * @param $opType
     * @param string $opMsg
     * @param int $adminId
     */
    public static function actionLog($userId, $opType, $opMsg = '', $adminId = 0)
    {
        UserOpLog::query()
            ->insert([
                'op_id' => $userId,
                'ad_id' => $adminId,
                'op_type' => $opType,
                'op_ins' => $opMsg,
                'create_time' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * 判斷是否已經登入
     * @param $userId
     * @param string $uuid
     * @param array|null $user
     * @return bool
     */
    public static function checkClientHasLogin($userId, string $uuid, array $user = null): bool
    {
        if ($user == null) {
            $user = self::getModelInstance()->newQuery()->where('id', '=', $userId)->first(
                ['fcm_token', 'device_type', 'device_uuid']
            );
            $user = $user->toArray();
        }
        //需要更換設備
        if ($user['device_uuid'] != $uuid) {
            return true;
        }

        return false;
    }

    public static function limit(array $conditions, int $limit = 15, int $page = 1, bool $deleted = false, int $status = 1, bool $exitsDelete = true): array
    {
        $query = self::_getQuery($conditions, $deleted, $status, $exitsDelete);

        $field = array_merge(['users.*'], ['user_profile.*']);

        $list = $query->skip(($page - 1) * $limit)
            ->join('user_profile', function ($query) {
                $query->on('users.id', '=', 'user_profile.user_id');
            })
            ->take($limit)
            ->orderByDesc('users.id')
            ->get($field)
            ->toArray();

        return $list;
    }

    public function getOne($id): array
    {
        $user = Users::query()
            ->where('id',$id)
            ->first();

        $profile = UserProfile::query()->where('user_id', $id)->first();

        if (empty($user) || empty($profile)) {
            return [];
        }
        $obj = array_merge($user->toArray(), $profile->toArray());
        $obj['id'] = $id;

        return $obj;
    }

    public static function count(array $conditions, bool $deleted = false, int $status = 1,bool $exitsDelete=true): int
    {
        $query = self::_getQuery($conditions, $deleted, $status);

        $count = $query->join('user_profile', function ($query) {
            $query->on('users.id', '=', 'user_profile.user_id');
        })->count();

        return intval($count);
    }

    public function update($id): int
    {
        $model = self::getModelInstance()->newQuery();

		return $model->newQuery()
            ->join('user_profile', function ($model) {
                $model->on('users.id', '=', 'user_profile.user_id');
            })
            ->where('users.id', $id)
            ->update($this->attr);
    }

    public function getUuid($type, $key): array
    {
        $model = self::getModelInstance();

        if ($type == 1) {//手機號碼
            $profile = $model->newQuery()
                ->join('user_profile', function ($model) {
                    $model->on('users.id', '=', 'user_profile.user_id');
                })
                ->where('users.mobile', $key)
				->orWhere('users.mobile', ltrim($key, '0'))
				->orWhere('users.mobile', '0'.$key)
                ->get(['users.id', 'name', 'id_number', 'mobile']);

            if (empty($profile)) {
                return [];
            }

            return $profile->toArray();
        }

        if ($type == 2) {//身份證號
            $profile = $model->newQuery()
                ->join('user_profile', function ($model) {
                    $model->on('users.id', '=', 'user_profile.user_id');
                })
                ->where('user_profile.id_number', $key)
                ->get(['users.id', 'name', 'id_number', 'mobile']);

            if (empty($profile)) {
                return [];
            }

            return $profile->toArray();
        }

        return [];
    }
}