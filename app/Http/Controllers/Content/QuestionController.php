<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/5/3
 * Time: 21:28
 */

namespace App\Http\Controllers\Content;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\ArrayParse;
use App\Library\Constant\Common;
use App\Models\Question\QuestionAppoint;
use App\Models\Question\QuestionReply;
use App\Models\Question\Questions;
use App\Models\RegisterUsers\UserCounts;
use App\Models\RegisterUsers\UserInfo;
use App\Models\RegisterUsers\UserOpLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{

    /**
     * @api               {get} /api/questions 问答列表
     * @apiGroup          内容获取
     * @apiName           问答列表
     *
     * @apiParam {String} page
     * @apiParam {String} limit
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
    /**
     * @param Request $request
     *
     * @return array
     */
    public function timeLine(Request $request): array
    {
        $limit = $request->get('limit', 15);
        $page = $request->get('page', 1);
        $questions = Questions::query()->where('typ',1)
            ->limit($limit)->offset(($page - 1) * $limit)->get();

        if (!empty($questions)) {
            $userIds = array_column($questions, 'user_id');
            $users = UserInfo::query()->whereIn('user_id', $userIds)->get(['user_id', 'avatar', 'nickname'])->toArray();
            $users = array_column($users, null, 'user_id');
            foreach ($questions as &$question) {
                $question['user_info'] = $users[$question['user_id']];
                unset($question);
            }
        }

        return [
            'questions' => $questions
        ];
    }

    /**
     * @api               {get} /api/question/info/{id} 问答详情页
     * @apiGroup          内容获取
     * @apiName           问答详情页
     *
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *          {
     *              "title"："",
     *              "content":"",
     *              "user_id":"",
     *              "user_info":{
     *                   "nickname":"",
     *                    "avatar":""
     *              },
     *              "answers": [{
     *                    "reply":"",
     *                    "answer_avatar":"",
     *                    "answer_nickname":"",
     *                    "create_time":"",
     *              },//...],
     *              "answer_avatar":"",
     *              "answer_nickname":""
     *
     *          }
     */
    /**
     * @param Request $request
     *
     * @return array
     */
    public function info(Request $request): array
    {
        $id = $request->route('id', 1);
        $question = Questions::query()->find($id);
        if (!$question) {
            return ['code' => ErrorConstant::DATA_ERR, 'response' => '数据不存在'];
        }

        $question = $question->toArray();
        $uid = $question['user_id'];

        //        $question['releaser'] = (new UsersService())->getOne($uid);
        $answers = QuestionReply::query()->where('question_id', $id)->get()->toArray();

        $userIds = array_column($answers, 'user_id');
        array_push($userIds, $uid);
        $users = UserInfo::query()->whereIn('user_id', $userIds)->get(['user_id', 'avatar', 'nickname'])->toArray();
        $users = array_column($users, null, 'user_id');
        $question['user_info'] = $users[$question['user_id']];
        foreach ($answers as &$answer) {
            $answer['reply'] = $answer['content'];
            $answer['answer_avatar'] = $users[$answer['user_id']]['avatar'];
            $answer['answer_nickname'] = $users[$answer['user_id']]['nickname'];
            unset($answer);
        }

        return $question;
    }


    /**
     * @api               {post} /api/question/release 发布问题
     * @apiGroup          内容操作
     * @apiName           发布问题
     *
     * @apiParam {String} title
     * @apiParam {String} typ
     * @apiParam {String} content
     * @apiParam {String} cover 封面图
     * @apiParam {List} tag_ids  标签（支持多选？接口支持）
     * @apiParam {String} answer_id   typ 为2 必填
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
        //title,content
        DB::beginTransaction();
        $uid = Auth::id();
        try {
            $params = ArrayParse::checkParamsArray(['title', 'typ', 'content'], $request->input());
            $params['user_id'] = $uid;
            $qid = Questions::query()->insertGetId($params);

            //todo: 指定提问
            if ($params['typ'] == 2) {
                $answerUserId = $request->input('answer_id');
                QuestionAppoint::query()->insert([
                    'question_id' => $qid,
                    'user_id' => $uid,
                    'answer_id' => $answerUserId,
                ]);
                $typ = Common::USER_OP_BE_QUESTION;
                UserCounts::incrementOrCreate($uid, $typ);
            }

            $typ = Common::USER_OP_QUESTION;
            UserCounts::incrementOrCreate($uid, $typ);
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'op_typ_id' => $qid,
                'typ' => $typ
            ]);
            DB::commit();
            return ['id' => $qid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }
}