<?php
namespace App\Http\Controllers\Users;

use App\Exceptions\ErrorConstant;
use App\Library\ArrayParse;
use App\Library\Auth\Encrypt;
use App\Library\Auth\RedisTokenCache;
use App\Library\Constant\Common;
use App\Library\CountryCode;
use App\Models\Admin;
use App\Models\Model;
use App\Models\RegisterUsers\UserOpLog;
use App\Models\RegisterUsers\UserProfile;
use App\Models\RegisterUsers\Users;
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
}