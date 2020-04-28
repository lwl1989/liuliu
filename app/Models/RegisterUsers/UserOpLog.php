<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


class UserOpLog extends Model
{
    public $table = 'user_op_log';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public static function incrementOrCreate($uid, int $typ)
    {
        $exists = UserOpLog::query()->where('user_id', $uid)->where('typ', $typ)->first('id');
        if (!empty($exists)) {
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'counts' => 1,
                'typ' => $typ
            ]);
        } else {
            UserOpLog::query()->where('user_id', $uid)->where('typ', $typ)->increment('counts');
        }
    }

}