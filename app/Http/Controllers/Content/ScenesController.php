<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/6/22
 * Time: 21:44
 */

namespace App\Http\Controllers\Content;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\ArrayParse;
use App\Library\Constant\Common;
use App\Models\Content\Scene;
use App\Models\Content\SceneReply;
use App\Models\RegisterUsers\UserCounts;
use App\Models\RegisterUsers\UserInfo;
use App\Models\RegisterUsers\UserOpLog;
use App\Models\RegisterUsers\UserZan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScenesController extends Controller
{
    /**
     * @api               {get} /api/scene/index 场景首页
     *
     * @apiGroup          场景
     * @apiName           获取场景列表
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "scenes": [
     *          {
     *              "id": "1",
     *              "name": "真人秀",
     *              "remark": "1",
     *              "status": "1",
     *              "reply_counts":"33",
     *              'reply_users':[{//userinfo}]
     *          }
     *          ,//...
     *      ],
     *   }
     */
    /**
     *
     * @return array
     */
    public function index()
    {
        $scenes = Scene::query()->inRandomOrder()->take(2)
            ->where('status', Common::STATUS_NORMAL)
            ->get()->toArray();
        $scenes = UserInfo::getUserInfoWithList($scenes, 'user_id');
        $scenes = SceneReply::limitCounts($scenes, 3);

        return [
            'scenes' => $scenes,
        ];
    }
    /**
     * @api               {get} /api/scenes 获取场景列表
     *
     * @apiParam {String} page
     * @apiParam {String} limit
     * @apiParam {String} sort   排序 sort | time
     * @apiGroup          场景
     * @apiName           获取场景列表
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "scenes": [
     *          {
     *              "id": "1",
     *              "name": "真人秀",
     *              "remark": "1",
     *              "status": "1",
     *              "reply_counts":"33",
     *              'reply_users':[{//userinfo}]
     *          }
     *          ,//...
     *      ],
     *   }
     */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function page(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'time');
        if ($sort != 'time') {
            $sort = 'sort';
        }else{
            $sort = 'update_time';
        }

        $scenes = Scene::query()
            ->where('status', Common::STATUS_NORMAL)
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->orderBy($sort, 'desc')
            ->get()->toArray();
        $scenes = UserInfo::getUserInfoWithList($scenes, 'user_id');
        $scenes = SceneReply::limitCounts($scenes, 3);

        return [
            'scenes' => $scenes,
        ];
    }


    /**
     * @api               {get} /api/scene/detail/{scene_id} 获取场景详情
     *
     * @apiGroup          场景
     * @apiName           获取场景详情
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "scene":
     *          {
     *              "id": "1",
     *              "name": "真人秀",
     *              "remark": "1",
     *              "status": "1",
     *              "reply_counts":"33",
     *              "user_opinion"："xxxxx"
     *          }
     *          ,
     *        "user":{//userinfo}
     *
     *   }
     */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function detail(Request $request)
    {
        $scene = Scene::query()->find($request->route('scene_id'));
        if ($scene) {
            $scene = $scene->toArray();
        }
        $scene = SceneReply::limitCounts([$scene], 0)[0];

        return [
            'scene' => $scene,
            'user' => UserInfo::query()->find($scene['user_id'])->toArray()
        ];
    }


    /**
     * @api               {get} /api/scene/reply/replies/{scene_id} 获取场景意见列表
     *
     * @apiParam {String} page
     * @apiParam {String} limit
     * @apiGroup          场景
     * @apiName           获取场景意见列表
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "replies":[
     *          {
     *              "id": "1",
     *              "user_id": "1",
     *              "value": "今天天气真的好",
     *              "zan_count","0",
     *             "user":{//userinfo}
     *          }]
     *
     *
     *
     *   }
     */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function replies(Request $request)
    {
        try {
            $uid = Auth::id();
        } catch (\Exception $e) {
            $uid = 0;
        }
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 15);
        $replies = SceneReply::query()
            ->where('scene_id', $request->route('scene_id'))
            ->where('status', Common::STATUS_NORMAL)
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->orderBy('create_time', 'desc')
            ->get()->toArray();

        if (!empty($replies)) {
            $zanCount = UserZan::query()->where('typ', UserZan::UserZanScene)
                ->whereIn('obj_id', array_column($replies, 'user_id'))
                ->groupBy('obj_id')->get(['count(*)', 'obj_id'])->toArray();
            $zanCount = array_column($zanCount, 'count(*)', 'obj_id');
            $replies = UserInfo::getUserInfoWithList($replies, 'user_id');


            foreach ($replies as &$reply) {
                $reply['zan_count'] = 0;
                if (isset($zanCount[$reply['id']])) {
                    $reply['zan_count'] = $zanCount[$reply['id']];
                }
                unset($reply);
            }

            if ($uid > 0) {
                $zan = UserZan::query()->where('typ', UserZan::UserZanScene)
                    ->whereIn('obj_id', array_column($replies, 'user_id'))
                    ->where('user_id', $uid)
                    ->select(['obj_id'])->get()->toArray();
                $zan = array_column($zan, 'obj_id');
                foreach ($replies as &$reply) {
                    $reply['zan'] = 0;
                    if (in_array($zan, $replies['id'])) {
                        $reply['zan'] = '1';
                    }
                    unset($reply);
                }
            }
        }
        return [
            'replies' => $replies,
        ];
    }

    /**
     * @api               {get} /api/scene/release 发布场景
     *
     * @apiGroup          场景
     * @apiName           发布场景
     *
     * @apiParam {String} name          场景名
     * @apiParam {String} remark        补充
     * @apiParam {String} user_opinion  发布者意见
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
    public function release(Request $request): array
    {
        $uid = Auth::id();
        DB::beginTransaction();
        try {
            $post = $request->input();
            $params = ArrayParse::checkParamsArray(['name', 'remark', 'user_opinion'], $post);
            $params['user_id'] = $uid;
            $typ = Common::USER_OP_RELEASE_SCENE;
            $cid = Scene::query()->insertGetId($params);

            UserCounts::incrementOrCreate($uid, $typ);
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'op_typ_id' => $cid,
                'typ' => $typ
            ]);
            DB::commit();
            return ['id' => $cid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }

    /**
     * @api               {post} /api/scene/reply/{scene_id} 发布场景意见
     *
     * @apiGroup          场景
     * @apiName           发布场景意见
     *
     * @apiParam {String} value         意见内容
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
    public function reply(Request $request): array
    {
        $uid = Auth::id();
        DB::beginTransaction();
        try {
            $params = ArrayParse::checkParamsArray(['value'], $request->input());
            $params['user_id'] = $uid;
            $typ = Common::USER_OP_REPLY_SCENE;
            $cid = Scene::query()->insertGetId($params);

            UserCounts::incrementOrCreate($uid, $typ);
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'op_typ_id' => $cid,
                'typ' => $typ
            ]);
            DB::commit();
            return ['id' => $cid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }

}