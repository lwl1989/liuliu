<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/5/3
 * Time: 22:36
 */

namespace App\Http\Controllers\Content;


use App\Exceptions\ErrorConstant;
use App\Library\ArrayParse;
use App\Library\Constant\Common;
use App\Models\Content\Content;
use App\Models\Content\ContentCounts;
use App\Models\Content\ContentTags;
use App\Models\Content\Topics;
use App\Models\RegisterUsers\UserInfo;
use App\Models\RegisterUsers\UserTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController
{

    /**
     * @api               {get} /api/topics 话题列表
     * @apiGroup          话题
     * @apiName           话题列表
     * @apiParam {String} page
     * @apiParam {String} limit
     * @apiParam {String} sort   排序 follow | time
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response
     * [
     *     {
     *                  "id": "1",
     *                  "title": "休息休息",
     *                  "follow": "212000",
     *                  "followed":"1"
     *    },//...
     * ]
     *
     */
    /**
     * @param Request $request
     * @return array
     */
    public function page(Request $request): array
    {
        $limit = $request->query('limit', 20);
        $page = $request->query('page', 1);
        if ($page < 0) {
            $page = 1;
        }
        $order = $request->query('order', 'time');
        if ($order != 'time') {
            $order = 'follow';
        } else {
            $order = 'update_time';
        }

        $topics = Topics::query()->where('status', Common::STATUS_NORMAL)->offset(($page - 1) * $limit)->limit($limit)
            ->orderBy($order, 'desc')
            ->get()->toArray();

        $followed = [];
        if (!empty($topics)) {
            $followed = UserTopic::query()->where('topic_id', array_column($topics, 'id'))
                ->select(['topic_id'])
                ->where('status', Common::STATUS_NORMAL)
                ->where('user_id', Auth::id())
                ->get()->toArray();
            $followed = array_column($followed, 'topic_id');
        }
        foreach ($topics as &$topic) {
            $topic['followed'] = 0;
            if (in_array($topic['id'], $followed)) {
                $topic['followed'] = 1;
            }
            unset($topic);
        }

        return [
            'topics' => $topics
        ];
    }

    /**
     * @api               {get} /api/recommend/topic 首页推荐话题
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
     *                  "followed":"1"
     *    },//...
     * ]
     *
     */
    public function recommend(): array
    {
        $topics = Topics::query()->where('status', Common::STATUS_NORMAL)->inRandomOrder()->limit(10)->get()->toArray();

        $followed = [];
        if (!empty($topics)) {
            $followed = UserTopic::query()->where('topic_id', array_column($topics, 'id'))
                ->select(['topic_id'])
                ->where('status', Common::STATUS_NORMAL)
                ->where('user_id', Auth::id())
                ->get()->toArray();
            $followed = array_column($followed, 'topic_id');
        }
        foreach ($topics as &$topic) {
            $topic['followed'] = 0;
            if (in_array($topic['id'], $followed)) {
                $topic['followed'] = 1;
            }
            unset($topic);
        }

        return [
            'topics' => $topics
        ];
    }

    /**
     * @api               {get} /api/topic/info/{topic_id} 话题页详情
     * @apiGroup          话题
     * @apiName           话题页详情
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response
     * {
     *      "topic":{
     *                  "id": "1",
     *                  "title": "休息休息",
     *                  "follow": "212000",
     *                  "followed":"1"
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
        $topic['followed'] = 0;
        $followed = UserTopic::query()->where('topic_id',$id)->where('user_id',Auth::id())->first();
        if($followed) {
            $topic['followed'] = 1;
        }
        $contentIds = ContentTags::query()->where('relation_id', $id)->where('typ', 2)->get(['content_id'])->toArray();
        $contents = [];
        if (!empty($contentIds)) {
            $contentIds = array_column($contentIds, 'content_id');
            $contents = Content::query()->whereIn('id', $contentIds)->get()->toArray();
            $contents = UserInfo::getUserInfoWithList($contents);
            $contents = ContentCounts::getContentsCounts($contents);
        }

        $topic['user'] = [];

        $user = UserInfo::query()->where('user_id', $topic['user_id'])->first();
        if($user) {
            $topic['user'] = $user->toArray();
        }

        return [
            'topic' => $topic,
            'contents' => $contents
        ];
    }


    /**
     * @api               {post} /api/topic/release 发布话题
     * @apiGroup          话题
     * @apiName           发布话题
     * @apiVersion        1.0.0
     *
     * @apiParam {String} title 话题标题
     * @apiParam {string} desc  详情
     *
     * @apiSuccessExample Success-Response
     *      HTTP/1.1 200 OK
     */
    /**
     * @param Request $request
     *
     * @return array
     * @throws
     */
    public function release(Request $request): array
    {
        $params = ArrayParse::checkParamsArray(['title','desc'], $request->input());
        $exists = Topics::query()->where('title', $params['title'])->first();
        if ($exists) {
            return ['code' => ErrorConstant::DATA_ERR, 'response' => '话题已存在'];
        }
        try {
            $params['user_id'] = Auth::id();
            $params['follow'] = 1;
            $tid = Topics::query()->insertGetId($params);
            UserTopic::query()->insert([
                'user_id' => $params['user_id'],
                'topic_id' => $tid,
                'sub_time' => date('Y-m-d H:i:s')
            ]);
            return [
                'tic' => $tid
            ];
        } catch (\Exception $e) {
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }


    /**
     * @api               {post} /api/topic/follow 关注话题
     * @apiGroup          话题
     * @apiName           关注话题
     * @apiVersion        1.0.0
     *
     * @apiParam {String} topic_id 话题id
     *
     * @apiSuccessExample Success-Response
     *      HTTP/1.1 200 OK
     */
    /**
     * @param Request $request
     *
     * @return array
     * @throws
     */
    public function follow(Request $request): array
    {
        $sudId = $request->input('topic_id');
        if (!$sudId) {
            return ['code' => ErrorConstant::PARAMS_ERROR];
        }

        $userId = Auth::id();
        $exists = UserTopic::query()->where('user_id', $userId)->where('topic_id', $sudId)->first();
        if (!$exists) {
            UserTopic::query()->insert([
                'user_id' => $userId,
                'topic_id' => $sudId
            ]);
            Topics::query()->where('id', $sudId)->increment('follow');
        } else {
            $exists = $exists->toArray();
            if ($exists['status'] == Common::STATUS_DISABLE) {
                UserTopic::query()->where('id', $exists['id'])->update([
                    'status' => Common::STATUS_NORMAL
                ]);
                Topics::query()->where('id', $sudId)->increment('follow');
            }
        }

        return [];
    }

    /**
     * @api               {post} /api/topic/unfollow 取消关注话题
     * @apiGroup          话题
     * @apiName          取消关注话题
     * @apiVersion        1.0.0
     *
     * @apiParam {String} topic_id 话题id
     *
     * @apiSuccessExample Success-Response
     *  HTTP/1.1 200 OK
     */
    /**
     * @param Request $request
     *
     * @return array
     * @throws
     */
    public function unFollow(Request $request): array
    {
        $sudId = $request->input('topic_id');
        if (!$sudId) {
            return ['code' => ErrorConstant::PARAMS_ERROR];
        }
        $topic = Topics::query()->where('id', $sudId)->first();
        if (!$topic) {
            return ['code' => ErrorConstant::PARAMS_ERROR];
        }

        $userId = Auth::id();
        if ($topic->id == $userId) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => '发布者不能取消关注'];
        }

        $exists = UserTopic::query()->where('user_id', $userId)->where('topic_id', $sudId)->first();
        if ($exists) {
            $exists = $exists->toArray();
            if ($exists['status'] == Common::STATUS_NORMAL) {
                UserTopic::query()->where('id', $exists['id'])->update([
                    'status' => Common::STATUS_DISABLE
                ]);
                Topics::query()->where('id', $sudId)->increment('follow', -1);
            }
        }

        return [];
    }
}