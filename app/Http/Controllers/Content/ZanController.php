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
use App\Models\Content\ContentCounts;
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
    public function zan(Request $request): array
    {
        $cid = $request->post('cid', 0);
        $typ = $request->post('typ', 1);
        if (!$cid) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
        }
        DB::beginTransaction();
        $uid = Auth::id();
        try {
            DB::commit();
            $opTyp = Common::USER_OP_ZAN;
            ContentCounts::incrementOrCreate($cid, $opTyp);
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'op_typ_id' => $cid,
                'typ' => $opTyp
            ]);
            UserZan::query()->insert([
                'user_id'   =>  $uid,
                'obj_id'    =>  $cid,
                'typ'       =>  $typ
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
            UserZan::query()->where('id', $exists->id)->delete();
            return ['id' => $cid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }
}