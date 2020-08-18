<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/5/5
 * Time: 16:57
 */

namespace App\Http\Controllers\Content;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\Constant\Common;
use App\Models\Content\Content;
use App\Models\Content\ContentComment;
use App\Models\Content\ContentCounts;
use App\Models\Content\Scene;
use App\Models\Content\SceneReply;
use App\Models\Question\QuestionReply;
use App\Models\Question\Questions;
use App\Models\RegisterUsers\UserCounts;
use App\Models\RegisterUsers\UserNotice;
use App\Models\RegisterUsers\UserOpLog;
use App\Models\RegisterUsers\UserZan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ZanController extends Controller
{
    /**
     * @api               {post} /api/content/zan 点赞
     * @apiGroup          内容操作
     * @apiName           点赞
     *
     * @apiParam {String} cid  文章id
     * @apiParam {String} typ  类型  1文章  2评论 3 问题 4 回答 5 场景意见
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
    public function zan(Request $request): array
    {
        $cid = $request->post('cid', 0);
        $typ = $request->post('typ', 1);
        if (!$cid) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
        }

        switch ($typ) {
            case UserZan::UserZanContent:
                $content = Content::query()->where('id', $cid)->first();
                if (!$content) {
                    return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
                }
                break;
            case UserZan::UserZanAnswer:
                $content = QuestionReply::query()->where('id', $cid)->first();
                if (!$content) {
                    return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
                }
                break;
            case UserZan::UserZanComment:
                $content = ContentComment::query()->where('id', $cid)->first();
                if (!$content) {
                    return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
                }
                break;
            case UserZan::UserZanQuestion:
                $content = Questions::query()->where('id', $cid)->first();
                if (!$content) {
                    return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
                }
                break;
            case UserZan::UserZanScene:
                $content = SceneReply::query()->where('id', $cid)->first();
                break;
            default:
                $content = false;
        }

        if (!$content) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
        }

        DB::beginTransaction();
        $uid = Auth::id();
        $exists = UserZan::query()->where('user_id', $uid)->where('typ', $typ)->where('obj_id', $cid)->first();
        if (!empty($exists)) {
            return ['code' => ErrorConstant::DATA_ERR, 'response' => '你已经点赞过了'];
        }
        try {
            DB::commit();
            $opTyp = Common::USER_OP_ZAN;
            UserZan::query()->insert([
                'user_id' => $uid,
                'obj_id' => $cid,
                'typ' => $typ
            ]);
            ContentCounts::incrementOrCreate($cid, $opTyp);
            ContentCounts::incrementOrCreate($cid, Common::USER_OP_BE_ZAN);
            //UserCounts::incrementOrCreate($uid, $opTyp);
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'op_typ_id' => $cid,
                'typ' => $opTyp
            ]);
            UserNotice::query()->insert([
                'user_id'   =>  $content->user_id,
                'obj_id'    =>  $cid,
                'op_user_id'=>  $uid,
                'op_type'   =>  Common::USER_OP_BE_ZAN,
                'typ'       =>  Common::CONTENT_CONTENT,
                'status'    =>  0 //未读
            ]);

            return ['id' => $cid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }

    /**
     * @api               {post} /api/content/unzan 取消点赞
     * @apiGroup          内容操作
     * @apiName           取消点赞
     *
     * @apiParam {String} cid  文章id
     * @apiParam {String} typ  类型  1文章  2评论 3 问题 4回答
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
    public function unZan(Request $request): array
    {
        $cid = $request->post('cid', 0);
        $typ = $request->post('typ', 1);
        if (!$cid) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
        }
        $uid = Auth::id();
        $exists = UserZan::query()->where('user_id', $uid)->where('obj_id', $cid)
            ->where('typ', $typ)->first();
        if (!$exists) {
            return [];
        }
        DB::beginTransaction();
        try {
            DB::commit();
            $opTyp = Common::USER_OP_ZAN;
            ContentCounts::incrementOrCreate($cid, $opTyp, -1);
            UserCounts::incrementOrCreate($uid, $opTyp, -1);
            UserZan::query()->where('id', $exists->id)->delete();
            return ['id' => $cid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }
}