<?php

namespace App\Http\Controllers\Users;


use App\Http\Controllers\Controller;
use App\Library\Constant\Common;
use App\Models\Content\Content;
use App\Models\Content\ContentCounts;
use App\Models\RegisterUsers\UserInfo;
use App\Models\RegisterUsers\UserRelations;
use App\Models\RegisterUsers\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * @api               {get} /api/user/follows 我关注的教练
     * @apiGroup          内容获取
     * @apiName           我关注的教练
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response
     * [
     *     {
     *                  "user_id": "1",
     *                  "nickname": "休息休息",
     *                  "avatar": "https://xxxxxx/",
     *    },//...
     * ]
     */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function follows(Request $request): array
    {
        $uid = Auth::id();

        $relations = UserRelations::query()
            ->where('user_id', $uid)
            ->where('status', Common::STATUS_NORMAL)
            ->where('typ', 1)
            ->get()
            ->toArray();

        if (empty($relations)) {
            return ['coaches' => []];
        }

        $userIds = array_column($relations, 're_user_id');
        $coaches = Users::query()->whereIn('user_id', $userIds)->get()->toArray();

        return ['coaches' => $coaches];
    }

    /**
     * @api               {get} /api/user/contents 我的feed流
     * @apiGroup          内容获取
     * @apiName           我的feed流
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response
     * {
     *    "contents":[
     *          {
     *                "id":"1",
     *                    "title":"xxxxx",
     *                    "content":"xxxxxxxxxxxx",
     *                    "user":{
     *                          "user_id":"1",
     *                          "avatar":"",
     *                          "nickname":"xxxx"
     *                      },
     *                    "counts":{
     *                      "3":"100",
     *                      "6":"13567746",
     *                    }
     *          }
     *      ]
     * }
     */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function contents(Request $request) : array
    {
        $uid = Auth::id();

        $relations = UserRelations::query()
            ->where('user_id', $uid)
            ->where('status', Common::STATUS_NORMAL)
            ->where('typ', 1)
            ->get()
            ->toArray();

        if (empty($relations)) {
            return ['contents' => []];
        }

        $userIds = array_column($relations, 're_user_id');
        $contents = Content::query()
            ->whereIn('user_id', $userIds)
            ->where('status',Common::STATUS_NORMAL)
            ->orderBy('create_time','desc')
            ->get()
            ->toArray();

        $contents = UserInfo::getUserInfoWithList($contents);
        $contents = ContentCounts::getContentsCounts($contents);

        return [
            'contents'  =>  $contents
        ];
    }
}