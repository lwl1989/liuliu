<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/5/3
 * Time: 22:36
 */

namespace App\Http\Controllers\Content;


use App\Exceptions\ErrorConstant;
use App\Library\Constant\Common;
use App\Models\Content\Content;
use App\Models\Content\ContentCounts;
use App\Models\Content\ContentTags;
use App\Models\Content\Topics;
use App\Models\RegisterUsers\UserInfo;
use Illuminate\Http\Request;

class TopicController
{

    /**
     * @api               {get} /api/topic/recommend 首页推荐话题
     * @apiGroup          首页数据
     * @apiName           推荐话题
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response
     * [
     *     {
     *                  "id": "1",
     *                  "title": "休息休息",
     *                  "follow": "212000",
     *    },//...
     * ]
     *
     */
    public function recommend(): array
    {
        $topics = Topics::query()->where('status', Common::STATUS_NORMAL)
            ->orderBy('follow', 'desc')->get()->toArray();
        return [
            'topics' => $topics
        ];
    }

    /**
     * @api               {get} /api/topic/info/{topic_id} 话题页详情
     * @apiGroup          内容获取
     * @apiName           话题页详情
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response
     * {
     *      "topic":{
     *                  "id": "1",
     *                  "title": "休息休息",
     *                  "follow": "212000",
     *    },
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
    public function info(Request $request): array
    {
        $id = $request->route('id');
        $topic = Topics::query()->find($id);
        if (!$topic) {
            return ['code' => ErrorConstant::DATA_ERR, 'response' => '数据错误'];
        }
        $topic = $topic->toArray();
        $contentIds = ContentTags::query()->where('relation_id', $id)->where('typ', 2)->get(['content_id'])->toArray();
        $contents = [];
        if (!empty($contentIds)) {
            $contentIds = array_column($contentIds, 'content_id');
            $contents = Content::query()->whereIn('id', $contentIds)->get()->toArray();
            $contents = UserInfo::getUserInfoWithList($contents);
            $contents = ContentCounts::getContentsCounts($contents);
        }
        return [
            'topic' => $topic,
            'contents' => $contents
        ];
    }
}