<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


/**
 * Class UserInfo
 * @package App\Models\RegisterUsers
 * @author  author  李文龙 <liwenlong@inke.cn>
 */
class UserInfo extends Model
{
    public $table = 'user_info';


    public $timestamps = false;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


    public static function getUserInfoWithList(array $list, string $userIdKey = 'user_id'): array
    {
        if (empty($list)) {
            return $list;
        }
        if (!isset($list[0][$userIdKey])) {
            return $list;
        }
        $userIds = array_column($list, $userIdKey);
        $users = UserInfo::query()->whereIn('user_id', $userIds)->get()->toArray();
        $users = array_column($users, null, 'user_id');
        foreach ($list as &$item) {
            $item['user'] = $users[$item['user_id']];
            unset($item);
        }
        return $list;
    }
}