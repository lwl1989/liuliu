<?php

namespace App\Models\RegisterUsers;

use App\Library\Constant\Common;
use App\Models\Model;


class UserRelations extends Model
{
    public $table = 'user_relation';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public static function followRelation($userId, array $userIds) : array
    {
        if(empty($userIds)) {
            return [];
        }

        $relations = UserRelations::query()->where('user_id', $userId)
            ->whereIn('re_user_id', $userIds)->where('status',Common::STATUS_NORMAL)
            ->get()->toArray();
        $relations = array_column($relations, null, 're_user_id');

        $res = [];
        foreach ($userIds as $userId) {
            $res[$userId] = 0;
            if(isset($relations[$userId])) {
                $res[$userId] = 1;
            }
        }
        var_dump($res, $relations);
        return $res;
    }
}