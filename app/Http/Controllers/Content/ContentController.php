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
use App\Models\Content\Content;
use App\Models\Content\ContentComment;
use App\Models\Content\ContentCounts;
use App\Models\Content\ContentTags;
use App\Models\RegisterUsers\UserCounts;
use App\Models\RegisterUsers\UserOpLog;
use App\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
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
     * @apiParam {List} tag_ids  标签（支持多选？接口支持）
     * @apiParam {List} topic_ids 话题（支持多选？接口支持）
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     */
    public function release(Request $request): array
    {
        DB::beginTransaction();
        $uid = Auth::id();
        try {
            $params = ArrayParse::checkParamsArray(['title', 'typ', 'content', 'cover','template_id'], $request->input());
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
     *  @apiParam {String} page
     *   @apiParam {String} limit
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



}