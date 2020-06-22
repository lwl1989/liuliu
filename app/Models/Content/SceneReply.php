<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/6/22
 * Time: 21:45
 */

namespace App\Models\Content;


use App\Library\Constant\Common;
use App\Models\Model;
use App\Models\RegisterUsers\UserInfo;

class SceneReply extends Model
{

    public $table = 'scene_reply';

    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public static function limitCounts(array $list, int $replyUserGet = 3): array
    {
        if (empty($list)) {
            return $list;
        }

        $ids = array_column($list, 'id');
        $replyCounts = self::query()->whereIn('scene_id', $ids)->where('status',Common::STATUS_NORMAL)
            ->select('count(*) as count,scene_id')->groupBy('scene_id')->get()->toArray();
        $replyCounts = array_column($replyCounts, 'count', 'scene_id');
        foreach ($list as $index => $item) {
            $list[$index]['reply_counts'] = 0;
            if (isset($replyCounts[$item['id']])) {
                $list[$index]['reply_counts'] = $replyCounts[$item['id']];
            }
        }
        if ($replyUserGet > 0) {
            $replies = self::query()->whereIn('scene_id', $ids)->where('status',Common::STATUS_NORMAL)
                ->select('user_id,scene_id')->get()->toArray();
            $replies = array_column($replies, 'user_id', 'scene_id');
            $replyUserIds = array_column($replies, 'user_id');
            $replyUserIds = array_unique($replyUserIds);
            if(!empty($replyUserIds)) {
                $users = UserInfo::query()->whereIn('user_id', $replyUserIds)->get()->toArray();
                $users = array_column($users, null, 'user_id');

                foreach ($list as $index => $item) {
                    $list[$index]['reply_users'] = [];
                    foreach ($replies as $sceneId=>$userId) {
                        if($item['id'] == $sceneId) {
                            if(isset($users[$userId])) {
                                $list[$index]['reply_users'][] = $users[$userId];
                            }
                        }
                    }
                }
            }

        }
        return $list;
    }
}