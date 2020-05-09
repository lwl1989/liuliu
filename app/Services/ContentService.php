<?php


namespace App\Services;


use App\Models\Content\Content;
use App\Models\Content\ContentCounts;
use App\Models\RegisterUsers\UserInfo;

class ContentService
{

    public static function getContentListView($ids, $offset, $limit): array
    {
        $result = Content::query()->whereIn('id', $ids)->limit($limit)->offset($offset)->get()->toArray();
//        echo '<pre>';
//        var_dump($result, $ids);
        $result = UserInfo::getUserInfoWithList($result);
        $result = ContentCounts::getContentsCounts($result);
//        $userIds = array_column($result, 'user_id');
//        $userInfos = UserInfo::query()->whereIn('user_id', $userIds)->get()->toArray();
//        $userInfos = array_column($userInfos, null, 'user_id');
//
//        $mapResult = [];
//        foreach ($result as $value) {
//            if (isset($userInfos[$value['user_id']])) {
//                $mapResult[] = [
//                    'content' => $value,
//                    'user' => $userInfos[$value['user_id']]
//                ];
//            }
//        }
        return $result;
    }
}