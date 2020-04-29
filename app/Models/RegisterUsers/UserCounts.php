<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


/**
 * Class UserCounts
 * @package App\Models\RegisterUsers
 * @author  author  李文龙 <liwenlong@inke.cn>
 */
class UserCounts extends Model
{
    public $table = 'user_counts';


    public $timestamps = false;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public static function incrementOrCreate($uid, int $typ)
    {
        $exists = UserCounts::query()->where('user_id', $uid)->where('typ', $typ)->first('id');
        if (!empty($exists)) {
            UserCounts::query()->insert([
                'user_id' => $uid,
                'counts' => 1,
                'typ' => $typ
            ]);
        } else {
            UserCounts::query()->where('user_id', $uid)->where('typ', $typ)->increment('counts');
        }
    }

}