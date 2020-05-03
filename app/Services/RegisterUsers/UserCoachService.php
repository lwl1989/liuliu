<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/5/3
 * Time: 14:31
 */

namespace App\Services\RegisterUsers;


use App\Models\RegisterUsers\UserCoach;
use App\Models\RegisterUsers\UserCoachTags;
use App\Services\ServiceBasic;

class UserCoachService extends ServiceBasic
{
    protected $model = UserCoach::class;

    public function getOne($id, bool $isUserId = true): array
    {
        if ($isUserId) {
            $coach = UserCoach::query()->where('user_id', $id)->first();
        } else {
            $coach = UserCoach::query()->where('id', $id)->first();
        }

        if (empty($coach)) {
            return [];
        }

        $coach = $coach->toArray();
        $tags = UserCoachTags::query()->where('coach_id', $coach)->get()->toArray();
        $coach['tags'] = $tags;

        return $coach;
    }
}