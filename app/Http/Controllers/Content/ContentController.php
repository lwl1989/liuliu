<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/28
 * Time: 19:25
 */

namespace App\Http\Controllers\Content;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\ArrayParse;
use App\Library\Constant\Common;
use App\Models\Common\Tags;
use App\Models\Content\Content;
use App\Models\Content\ContentComment;
use App\Models\Content\ContentCounts;
use App\Models\Content\ContentTags;
use App\Models\Content\Resources;
use App\Models\RegisterUsers\UserCoach;
use App\Models\RegisterUsers\UserCounts;
use App\Models\RegisterUsers\UserInfo;
use App\Models\RegisterUsers\UserOpLog;
use App\Models\RegisterUsers\Users;
use App\Models\RegisterUsers\UserZan;
use App\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    /**
     * @api               {get} /api/content/detail 获取内容详情
     *
     * @apiParam {String} id 内容id
     * @apiGroup          内容获取
     * @apiName           获取内容详情
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      'content':{
     *              "id": "1",
     *              "name": "真人秀",
     *              "sort": "1",
     *              "status": "1",
     *              "create_time": "1588069984",
     *              "update_time": "1588069984"
     *       },
     *       'user':{
     *          //用户属性
     *          'is_coach':1,
     *          'job':'xxx',
     *          'real_name':'xxx'
     *         },
     *      "tags":[{"id":"1","name":"ojbk"}],
     *      "topics":[{"id":"1","name":"ojbk"}],
     *      "resources":[{"id":"1","value":"httpxxxxxxx"}]
     *      "zan":"1",
     *      "zanCount":"100",
     *      "commentCount":"101"
     *   }
     */
    /**
     * @param Request $request
     * @return array
     */
    public function detail(Request $request): array
    {
        $id = $request->get('id');
        if (!$id) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => '参数错误'];
        }

        $content = Content::query()->where('id', $id)->first();
        if(!$content) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => '参数错误'];
        }
        var_dump($content);exit();

        $content = $content->toArray();
        $resource = Resources::query()->where('content_id', $id)->where('status', Common::STATUS_NORMAL)->get();
        $resources = [];
        if ($resource) {
            $resources = $resource->toArray();
        }

        $relations = ContentTags::query()->where('content_id', $id)->get();
        $tags = [];
        $topics = [];
        if ($relations) {
            $tagsIds = [];
            $topicsIds = [];
            foreach ($relations->toArray() as $item) {
                if ($item['type'] == 1) {
                    $tagsIds[] = $item['relation_id'];
                } else {
                    $topicsIds[] = $item['relation_id'];
                }
            }

            if (!empty($tagsIds)) {
                $t = Tags::query()->whereIn('id', $tagsIds)->get();
                if ($t) {
                    $tags = $t->toArray();
                }
            }
            if (!empty($topicsIds)) {
                $t1 = Tags::query()->whereIn('id', $topicsIds)->get();
                if ($t1) {
                    $topics = $t1->toArray();
                }
            }
        }
        $user = Users::query()->find($content['user_id'])->toArray();
        $user['is_coach'] = 0;
        $user['job'] = '';
        $user['real_name'] = '';
        $userCoach = UserCoach::query()->where('user_id', $content['user_id'])->where('status', Common::STATUS_NORMAL)->first();
        if ($userCoach) {
            $userCoach = $userCoach->toArray();
            $user['is_coach'] = 1;
            $user['job'] = $userCoach['job'];
            $user['real_name'] = $userCoach['real_name'];
        }
        try {
            $uid = Auth::id();
        } catch (\Exception $exception) {
            $uid = 0;
        }
        $zan = 0;
        $zanCount = 0;
        $commentCount = ContentComment::query()->where('content_id', $id)->where('status', Common::STATUS_NORMAL)->count();
        if ($uid > 0) {
            $exists = UserZan::query()->where('user_id', $uid)->where('typ', 1)->where('obj_id', $id)->first(['id']);
            if ($exists) {
                $zan = 1;
            }
            $zanCount = UserZan::query()->where('typ', 1)->where('obj_id', $id)->count();
        }
        return [
            'content' => $content,
            'resources' => $resources,
            'tags' => $tags,
            'topics' => $topics,
            'user' => $user,
            'zan' => $zan,
            'zanCount' => $zanCount,
            'commentCount' => $commentCount
        ];
    }

    /**
     * @api               {post} /api/content/release 发布文章
     * @apiGroup          内容操作
     * @apiName           发布文章
     *
     * @apiParam {String} title
     * @apiParam {String} typ  1 锦囊  3随记
     * @apiParam {String} content
     * @apiParam {String} template_id  1 图文 2 视频 3 音频
     * @apiParam {String} cover 封面图
     * @apiParam {List} resources 静态资源集合（资源类型和template绑定）
     * @apiParam {List} tag_ids  标签（支持多选？接口支持）
     * @apiParam {List} topic_ids 话题（支持多选？接口支持）
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     */
    public function release(Request $request): array
    {
        $uid = Auth::id();
        DB::beginTransaction();
        try {
            $params = ArrayParse::checkParamsArray(['title', 'typ', 'content', 'cover', 'template_id'], $request->input());
            $params['user_id'] = $uid;
            $cid = Content::query()->insertGetId($params);

            $tagParams = ArrayParse::checkParamsArray(['tag_ids'], $request->input());
            if (!is_array($tagParams['tag_ids'])) {
                throw new \Exception('ids为空', ErrorConstant::PARAMS_ERROR);
            }
            $tagParams['tag_ids'] = array_values($tagParams['tag_ids']);
            foreach ($tagParams['tag_ids'] as $tagId) {
                ContentTags::query()->insert([
                    'content_id' => $cid,
                    'relation_id' => $tagId
                ]);
            }

            $tagParams = ArrayParse::checkParamsArray(['topic_ids'], $request->input());
            if (!is_array($tagParams['topic_ids'])) {
                throw new \Exception('ids为空', ErrorConstant::PARAMS_ERROR);
            }
            $tagParams['topic_ids'] = array_values($tagParams['topic_ids']);
            foreach ($tagParams['topic_ids'] as $tagId) {
                ContentTags::query()->insert([
                    'content_id' => $cid,
                    'relation_id' => $tagId,
                    'typ' => 2
                ]);
            }

            $typ = Common::USER_OP_RELEASE;
            UserCounts::incrementOrCreate($uid, $typ);
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'op_typ_id' => $cid,
                'typ' => $typ
            ]);

            $resources = $request->input('resources', []);
            if (!empty($resources) && !is_array($resources)) {
                if ($params['template_id'] == 1) {
                    if (count($resources) > 3) {
                        return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => '参数错误(图片长度)'];
                    }
                } else {
                    if (count($resources) > 1) {
                        return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => '参数错误(资源长度)'];
                    }
                }
                foreach ($resources as $resource) {
                    Resources::query()->create([
                        'content_id' => $cid,
                        'status' => Common::STATUS_NORMAL,
                        'value' => $resource
                    ]);
                }
            }
            DB::commit();
            return ['id' => $cid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }

    /**
     * @api               {get} /api/content/ 频道内容列表
     *
     * @apiParam {String} tag_id
     * @apiParam {String} page
     * @apiParam {String} limit
     * @apiGroup          内容获取
     * @apiName           获取不同标签下文章内容
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "contents": [
     *          {
     *              'content':{
     *              "id": "1",
     *              "name": "真人秀",
     *              "sort": "1",
     *              "status": "1",
     *              "create_time": "1588069984",
     *              "update_time": "1588069984"
     *              },
     *              'user':{//userinfo}
     *          }
     *          ,//...
     *      ],
     *   }
     */
    public function myList(Request $request)
    {
        $tagId = $request->get('tag_id', 0);
        if (!$tagId) {
            $tagId = -2;
        }
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 15);

        $result = [];
        switch ($tagId) {
            case '-1':
                //todo:  关注
                break;
            case '-2':
                //todo: 推荐
                break;
            default:
                //todo:先期每个条目最多200条，没那么多人划那么多条，按发布时间逆序即可
                $contentBind = ContentTags::query()->where('relation_id', $tagId)->where('typ', 1)->orderBy('id', 'desc')->limit(200)->get()->toArray();
                if (!empty($contentBind)) {
                    $contentIds = array_column($contentBind, 'content_id');
                    $result = ContentService::getContentListView($contentIds, ($page - 1) * $limit, $limit);
                }
        }

        return [
            'contents' => $result
        ];
    }


    /**
     * @api               {get} /api/recommend/content 频道内容列表
     *
     * @apiParam {String} typ 1 锦囊 2素材 3随记
     * @apiGroup          首页数据
     * @apiName           获取首页推荐内容
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "contents": [
     *          {
     *              'content':{
     *              "id": "1",
     *              "name": "真人秀",
     *              "sort": "1",
     *              "status": "1",
     *              "create_time": "1588069984",
     *              "update_time": "1588069984"
     *              },
     *              'user':{//userinfo}
     *          }
     *          ,//...
     *      ],
     *   }
     */
    /**
     * @param Request $request
     * @return array
     */
    public function recommend(Request $request): array
    {
        $type = $request->get('type', 1);

        $result = Content::query()->where('typ', $type)->inRandomOrder()->take(10)->get()->toArray();
        $result = UserInfo::getUserInfoWithList($result);
        $result = ContentCounts::getContentsCounts($result);

        return [
            'contents' => $result
        ];
    }

}