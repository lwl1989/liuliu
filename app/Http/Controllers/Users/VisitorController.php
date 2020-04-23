<?php
namespace App\Http\Controllers\Users;

use App\Library\Auth\Encrypt;
use App\Library\Constant\User;
use App\Library\Message\UserRelations;
use Illuminate\Http\Request;
use App\Library\RedisFacade as Redis;

class VisitorController
{
    public function generateToken(Request $request) : array
    {
        $uuid = $request->post('device_uuid',false);

        return Encrypt::getVisitorResult(!$uuid ? [] : ['device_uuid'=>$uuid]);
    }

    public function register(Request $request) : array
    {
        $uuid = $request->post('device_uuid',false);
        $type = $request->post('device_type',User::USERS_DEVICE_TYPE_IOS);
        $fcmToken = $request->post('fcm_token','x');
        $badge = intval($request->post('badge', 0));

        if ($uuid !== false) {
            try {
                UserRelations::sendUser(0, $type, $uuid, $badge, $fcmToken);
                Redis::hset('badge:device', $uuid, $badge);
            } catch (\Exception $exception) {
                $result['topics_error'] = $exception->getMessage();
            }
        }

        return ['uuid' => $uuid, 'badge' => $badge];
    }
}