<?php


namespace App\Http\Controllers\Common;


use App\Http\Controllers\HomeController;
use App\Library\Constant\Common;
use App\Library\CountryCode;
use App\Models\RegisterUsers\Users;

class CommonController extends HomeController
{

	public function webImportUserRelations() : array
	{

//        Users::query()
//            ->where('status',Common::STATUS_NORMAL)
//            ->where('deleted', Common::NO_DELETE)
//            ->where('login_token','')
//            ->update([
//                'fcm_token' =>  '',
//
//            ]);
		$users = Users::query()
			->where('status',Common::STATUS_NORMAL)
			->where('deleted', Common::NO_DELETE)
			->where('login_token','!=','')
			->orderBy('update_time')
			->get(['id','device_type','device_uuid','fcm_token'])->toArray();
		$result = [];
		$errors = [];
		if(!empty($users)) {
			foreach ($users as $user) {
				try {
					$result[] = \App\Library\Message\UserRelations::sendUser($user['id'], $user['device_type'], $user['device_uuid'], 0,  $user['fcm_token'], []);
				}catch (\Exception $e) {
					$errors[] =  $e->getMessage();
				}
			}
		}
		return ['result'=>$result,'errors'=>$errors];
	}
}