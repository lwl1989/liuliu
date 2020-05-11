<?php

namespace App\Http\Controllers\Users;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\Constant\Common;
use App\Models\Content\Content;
use App\Models\Content\ContentComment;
use App\Models\Content\ContentCounts;
use App\Models\RegisterUsers\UserCoach;
use App\Models\RegisterUsers\UserCounts;
use App\Models\RegisterUsers\UserInfo;
use App\Models\RegisterUsers\UserOpLog;
use App\Models\RegisterUsers\UserRelations;
use App\Models\RegisterUsers\Users;
use App\Models\RegisterUsers\UserSubTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * @api               {get} /api/user/center 个人中心上半部
     * @apiGroup          内容获取
     * @apiName           个人中心上半部
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *              "user":{
     *                  "id": "1",
     *                  "nickname": "休息休息",
     *                  "avatar": "https://xxxxxx/",
     *              },
     *              "tags"  :   [
     *                          {"id":"1","name":"教练标签"},//...
     *             ],
     *              "counts": {
     *                  "8":"12",
     *                  "9":"90"
     *              },
     *             "is_coach":"1" // 1认证了 2 未认证
     *    }
     *
     */
    /**
     * 个人中心
     * @param Request $request
     *
     * @return array
     */
    public function center(Request $request) : array
    {
        $uid = Auth::id();

        $info = UserInfo::query()->where('user_id',$uid)->first();
        $tags = UserSubTags::query()->where('user_id', $uid)->get()->toArray();

        $fansCount = 0;
        $fans = UserCounts::query()->where('user_id', $uid)->where('typ', Common::USER_OP_BE_FOLLOW)->first(['counts']);
        if($fans) {
            $fansCount = ($fans->toArray())['counts'];
        }

        $followCount = 0;
        $follows = UserCounts::query()->where('user_id', $uid)->where('typ', Common::USER_OP_FOLLOW)->first(['counts']);
        if($follows) {
            $followCount = ($follows->toArray())['counts'];
        }
        $isCoach = 0;
        $coach = UserCoach::query()->where('user_id', $uid)->where('status',Common::STATUS_NORMAL)->first();
        if($coach) {
            $isCoach = 1;
        }
        return [
            'user'  =>  $info,
            'tags'  =>  $tags,
            'counts'    =>  [
                Common::USER_OP_BE_FOLLOW => $fansCount,
                Common::USER_OP_FOLLOW  =>  $followCount
            ],
            'is_coach'  => $isCoach
        ];
    }
    /**
     * @api               {get} /api/user/follows/{uid} 我关注的教练
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
        $uid = $request->route('uid');

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
     * @api               {get} /api/user/contents/{uid} 我的feed流
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
    public function contents(Request $request): array
    {
        $uid = $request->route('uid');

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
            ->where('status', Common::STATUS_NORMAL)
            ->orderBy('create_time', 'desc')
            ->get()
            ->toArray();

        $contents = UserInfo::getUserInfoWithList($contents);
        $contents = ContentCounts::getContentsCounts($contents);

        return [
            'contents' => $contents
        ];
    }


    /**
 * @api               {post} /api/user/follow 关注
 * @apiGroup          用户操作
 * @apiName           关注
 *
 * @apiParam {String} content
 * @apiVersion        1.0.0
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function follow(Request $request): array
    {
        $sudId = $request->input('user_id');
        if (!$sudId) {
            return ['code' => ErrorConstant::PARAMS_ERROR];
        }

        $userId = Auth::id();
        $exists = UserRelations::query()->where('user_id', $userId)->where('re_user_id', $sudId)
            ->where('typ', 1)->first();
        if (!$exists) {
            UserRelations::query()->insert([
                'user_id' => $userId,
                'typ' => 1,
                're_user_id' => $sudId
            ]);

            UserOpLog::query()->insert([
                'user_id' => $userId,
                'op_typ_id' => $sudId,
                'typ' => Common::USER_OP_FOLLOW
            ]);
            UserOpLog::query()->insert([
                'user_id' => $sudId,
                'op_typ_id' => $userId,
                'typ' => Common::USER_OP_BE_FOLLOW
            ]);
            UserCounts::incrementOrCreate($userId, Common::USER_OP_FOLLOW);
            UserCounts::incrementOrCreate($sudId, Common::USER_OP_BE_FOLLOW);
        } else {
            $exists = $exists->toArray();
            if ($exists['status'] == Common::STATUS_DISABLE) {
                UserRelations::query()->where('id', $exists['id'])->update([
                    'status' => Common::STATUS_NORMAL
                ]);
            }
        }

        return [];
    }


    /**
     * @api               {post} /api/user/unfollow 取消关注
     * @apiGroup          用户操作
     * @apiName           取消关注
     *
     * @apiParam {String} content
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function unFollow(Request $request): array
    {
        $sudId = $request->input('user_id');
        if (!$sudId) {
            return ['code' => ErrorConstant::PARAMS_ERROR];
        }

        $userId = Auth::id();
        $exists = UserRelations::query()->where('user_id', $userId)->where('re_user_id', $sudId)
            ->where('typ', 1)->first();
        if (!$exists) {
           return [];
        } else {
            $exists = $exists->toArray();
            if ($exists['status'] == Common::STATUS_NORMAL) {
                UserRelations::query()->where('id', $exists['id'])->update([
                    'status' => Common::STATUS_DISABLE
                ]);
                UserCounts::incrementOrCreate($userId, Common::USER_OP_FOLLOW, -1);
                UserCounts::incrementOrCreate($sudId, Common::USER_OP_BE_FOLLOW, -1);
            }
        }
        return [];
    }

    /**
     * @api               {get} /api/user/comments/:uid 评论文章
     * @apiGroup          内容操作
     * @apiName           评论文章
     *
     * @apiParam {String} content
     * @apiParam {String} cid  文章id
     * @apiParam {String} pid  上一层评论id，第一层为0
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     */
    /**
     * @param Request $request
     * @return array
     */
    public function comments(Request $request) : array
    {
        $uid = $request->route('uid');
        //todo: 第二级评论回复不展示
        $comment = ContentComment::query()->where('user_id',$uid)->where('parent_id', 0)->get()->toArray();
        $result = [];
        if(!empty($comment)) {
            $contentIds = array_column($comment, 'content_id');

            $contents = Content::query()->whereIn('id', $contentIds)->get()->toArray();
            $contents = ContentCounts::getContentsCounts($contents);
            $contents = UserInfo::getUserInfoWithList($contents);
            $contents = array_column($contents, null, 'id');
            foreach ($comment as $item) {
                $result[] = [
                    'comment'   =>  $item,
                    'content'   =>  $contents[$item['content_id']]
                ];
            }
        }

        return $result;
    }
}