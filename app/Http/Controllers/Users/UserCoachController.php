<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/5/3
 * Time: 14:27
 */

namespace App\Http\Controllers\Users;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\ArrayParse;
use App\Library\Constant\Common;
use App\Models\Common\Tags;
use App\Models\Content\Content;
use App\Models\Question\QuestionRelation;
use App\Models\Question\QuestionReply;
use App\Models\Question\Questions;
use App\Models\RegisterUsers\UserCoach;
use App\Models\RegisterUsers\UserCoachTags;
use App\Models\RegisterUsers\UserInfo;
use App\Models\RegisterUsers\UserRelations;
use App\Services\RegisterUsers\UserCoachService;
use App\Services\RegisterUsers\UsersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserCoachController extends Controller
{
    /**
     * @api               {post} /api/coach/join 加入教练
     * @apiGroup          用户操作
     * @apiName           加入教练
     *
     * @apiParam {String} glory
     * @apiParam {string} img
     * @apiParam {String} real_name
     * @apiParam {String} job
     * @apiParam {String} desc
     * @apiParam {String} intro
     * @apiParam {List} courses  课程
     * @apiParam {List} services 服务项目
     *
     * @apiParam {List} tag_ids  教练擅长频道
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
    public function join(Request $request): array
    {
        DB::beginTransaction();
        $uid = Auth::id();
        $exists = UserCoach::query()->where('user_id', $uid)->first();
        if ($exists) {
            if ($exists->status == Common::STATUS_DISABLE) {
                return ['code' => ErrorConstant::DATA_ERR, 'response' => '此账户已被禁止申请教练'];
            } else {
                return ['code' => ErrorConstant::DATA_ERR, 'response' => '已经申请过了'];
            }
        }
        try {
            $params = ArrayParse::checkParamsArray(['glory', 'real_name', 'job', 'desc', 'intro',
                'courses', 'services','img'], $request->input());
            $params['user_id'] = $uid;
            $params['courses'] = is_array($params['courses']) ? implode(",", $params['courses']) : $params['courses'];
            $params['services'] = is_array($params['services']) ? implode(",", $params['services']) : $params['services'];
            $params['status'] = 3; // 待审核
            $ucId = UserCoach::query()->insertGetId($params);

            $tagParams = ArrayParse::arrayCopy(['tag_ids'], $request->input());
            if (is_array($tagParams['tag_ids'])) {
                $tagParams['tag_ids'] = array_values($tagParams['tag_ids']);
                foreach ($tagParams['tag_ids'] as $tagId) {
                    UserCoachTags::query()->insert([
                        'tag_id' => $tagId,
                        'coach_id' => $ucId
                    ]);
                }
            }

            DB::commit();
            return ['id' => $ucId];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }
    /**
     * @api               {get} /api/coach/tag/{tag_id} 按频道取教练列表
     * @apiGroup          内容获取
     * @apiName           按频道取教练列表
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response
     * [
     *     {
     *                  "join_time":"2020-04-20 12:12:12",
     *                  "tags"  :   [
     *                          {"id":"1","name":"教练标签"},//...
     *                  ],
     *                    "intro":"dsadasdsa",
     *                    "desc":"dasfghfdsfgdfgfhdg",
     *                    "user":{
     *                          "user_id":"111",
     *                          "nickname":"dsadsad",
     *                          "avatar":"erfgh"
     *                      },
     *                    "followed":"0" //0未关注  1已关注
     *
     *     }, //......
     * ]
     *
     */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function tag(Request $request): array
    {
        $tagId = $request->route('tag_id');
        $coaches = UserCoachTags::query()->where('tag_id', $tagId)->get()->toArray();
        if (empty($coaches)) {
            return ['coaches' => []];
        }

        $coachIds = array_column($coaches, 'coach_id');
        $coaches = UserCoach::query()->whereIn('id', $coachIds)->where('status', Common::STATUS_NORMAL)->get()->toArray();
        if (empty($coaches)) {
            return ['coaches' => []];
        }

        $userIds = array_column($coaches, 'user_id');
        $coachTags = UserCoachTags::query()->whereIn('coach_id', $coachIds)->get()->toArray();
        $tagsIds = array_column($coachTags, 'tag_id');
        $tags = Tags::query()->whereIn('id', $tagsIds)->get()->toArray();
        $tags = array_column($tags, null, 'id');
        $userInfo = UserInfo::query()->whereIn('user_id', $userIds)->get()->toArray();
        $userInfo = array_column($userInfo, null, 'user_id');
        $relations = UserRelations::followRelation(Auth::id(), $userIds);
        foreach ($coaches as &$coach) {
            $coach['tags'] = [];
            foreach ($coachTags as $coachTag) {
                if ($coachTag['coach_id'] == $coach['id']) {
                    if (isset($tags[$coachTag['tag_id']])) {
                        $coach['tags'][] = $tags[$coachTag['tag_id']];
                    }
                }
            }
            $coach['user'] = $userInfo[$coach['user_id']];
            $coach['followed'] = $relations[$coach['user_id']];
            unset($coach);
        }
        return [
            'coaches' => $coaches
        ];
    }

    /**
     * @api               {get} /api/recommend/coach 首页推荐教练
     * @apiGroup          首页数据
     * @apiName           首页推荐教练
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response
     * [
     *     {
     *                  "id":1,
     *                  "user_id":"22",
     *                  "join_time":"2020-04-20 12:12:12",
     *
     *                    "intro":"dsadasdsa",
     *                    "desc":"dasfghfdsfgdfgfhdg",
     *                    "user":{
     *                          "user_id":"111",
     *                          "nickname":"dsadsad",
     *                          "avatar":"erfgh"
     *                      },
     *                    "followed":"0" //0未关注  1已关注
     *
     *     }, //......
     * ]
     *
     */
    /**
     * @return array
     */
    public function recommend(): array
    {

        $coaches = UserCoach::query()->where('status', Common::STATUS_NORMAL)->inRandomOrder()->limit(10)->get()->toArray();

        if (empty($coaches)) {
            return ['code' => ErrorConstant::DATA_ERR, 'response' => '暂时未有教练'];
        }

        $userIds = array_column($coaches, 'user_id');
        $infos = UserInfo::query()->whereIn('user_id', $userIds)->get(['user_id', 'avatar', 'nickname'])->toArray();
        $infos = array_column($infos, null, 'user_id');
        //$coaches = UserInfo::getUserInfoWithList($coaches);

        try {
            $id = Auth::id();
        } catch (\Exception $exception) {
            $id = 0;
        }
        if ($id > 0) {
            $relations = UserRelations::followRelation($id, array_keys($infos));
            foreach ($coaches as &$coach) {
                $coach['user'] = $infos[$coach['user_id']];
                $coach['followed'] = $relations[$coach['user_id']];
                unset($coach);
            }
        } else {
            foreach ($coaches as &$coach) {
                $coach['user'] = $infos[$coach['user_id']];
                $coach['followed'] = 0;
            }
        }

        return [
            'coaches' => $coaches
        ];
    }

    /**
     * @api               {get} /api/coach/info/{uid} 获取教练信息
     * @apiGroup          内容获取
     * @apiName           获取教练基本信息
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *              "info":{
     *                  "id": "1",
     *                  "nickname": "休息休息",
     *                  "avatar": "https://xxxxxx/",
     *              },
     *              "coach": {
     *                  "join_time":"2020-04-20 12:12:12",
     *                  "tags"  :   [
     *                          {"id":"1","name":"教练标签"},//...
     *                              ]
     *
     *              }
     *          }
     *          ,//...
     */
    public function info(Request $request)
    {
        $uid = $request->route('uid');
        $user = new UsersService();
        $info = $user->getOne($uid);
        if (empty($info)) {
            return ['code' => ErrorConstant::DATA_ERR, 'response' => '用户信息出错'];
        }

        $coach = new UserCoachService();
        $coachInfo = $coach->getOne($uid);

        return [
            'info' => $info,
            'coach' => $coachInfo
        ];
    }


    /**
     * @api               {get} /api/coach/answer/{uid}/{typ} 获取教练回答
     * @apiGroup          内容获取
     * @apiName           获取教练基本信息
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "questions": [
     *          {
     *              "question_info"：{
     *                      "title":""
     *                  },
     *              "reply":"",
     *              "user_id":"",
     *              "answer_id":""
     *              "answer_avatar":"",
     *              "answer_nickname":""
     *
     *          }
     *          ,//...
     *      ],
     *   }
     */
    public function answers(Request $request)
    {
        $uid = $request->route('uid');
        $typ = $request->route('typ');

        $coach = new UsersService();
        $coachInfo = $coach->getOne($uid);

        $answers = [];
        if ($typ != 0) {
            $relations = QuestionRelation::query()
                ->where('answer_id', $uid)
                ->where('typ', $typ)
                ->get()->toArray();
            if (!empty($relations)) {
                $answers = QuestionReply::query()
                    ->where('user_id', $uid)
                    ->where('status', Common::STATUS_NORMAL)
                    ->whereIn('id', array_column($relations, 'answer_id'))
                    ->orderBy('id', 'desc')
                    ->get()->toArray();
            }
        } else {
            $answers = QuestionReply::query()
                ->where('user_id', $uid)
                ->where('status', Common::STATUS_NORMAL)
                ->orderBy('id', 'desc')
                ->get()->toArray();
        }

        foreach ($answers as &$answer) {
            $answer['reply'] = $answer['content'];
            $answer['question_info'] = Questions::query()->find($answer['question_id'], ['title', 'question_id']);
            $answer['answer_avatar'] = $coachInfo['avatar'];
            $answer['answer_nickname'] = $coachInfo['nickname'];
            unset($answer);
        }

        return [
            'answers' => $answers
        ];
    }


    /**
     * @api               {get} /api/coach/content/{uid}/{typ} 获取教练发布内容
     * @apiGroup          内容获取
     * @apiName           获取教练基本信息
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "contents": [
     *          {
     *              "title":"",
     *              "content":"",
     *              "user_id":"",
     *              "user_avatar":"",
     *              "user_nickname":"",
     *              "create_time":"",
     *
     *          }
     *          ,//...
     *      ],
     *   }
     */
    public function contents(Request $request): array
    {
        $uid = $request->route('uid');
        $typ = $request->route('typ');

        $coach = new UsersService();
        $coachInfo = $coach->getOne($uid);


        if ($typ != 0) {
            $contents = Content::query()
                ->where('user_id', $uid)
                ->where('status', Common::STATUS_NORMAL)
                ->where('typ', $typ)
                ->orderBy('id', 'desc')
                ->get()->toArray();
        } else {
            $contents = Content::query()
                ->where('user_id', $uid)
                ->where('status', Common::STATUS_NORMAL)
                ->orderBy('id', 'desc')
                ->get()->toArray();
        }

        foreach ($contents as &$content) {
            $content['reply'] = $content['content'];
            $content['user_avatar'] = $coachInfo['avatar'];
            $content['user_nickname'] = $coachInfo['nickname'];
            unset($content);
        }

        return [
            'contents' => $contents
        ];
    }
}