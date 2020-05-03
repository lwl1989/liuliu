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
use App\Library\Constant\Common;
use App\Models\Content\Content;
use App\Models\Question\QuestionRelation;
use App\Models\Question\QuestionReply;
use App\Models\Question\Questions;
use App\Services\RegisterUsers\UserCoachService;
use App\Services\RegisterUsers\UsersService;
use Illuminate\Http\Request;

class UserCoachController extends Controller
{
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
            $answer['reply']    = $answer['content'];
            $answer['question_info']    = Questions::query()->find($answer['question_id'], ['title','question_id']);
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
    public function contents(Request $request) : array
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
            $content['reply']    = $content['content'];
            $content['user_avatar'] = $coachInfo['avatar'];
            $content['user_nickname'] = $coachInfo['nickname'];
            unset($content);
        }

        return [
            'contents' => $contents
        ];
    }
}