<?php


namespace App\Http\Controllers\Content;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\Constant\Common;
use App\Models\Content\ContentComment;
use App\Models\Content\ContentCounts;
use App\Models\RegisterUsers\UserCounts;
use App\Models\RegisterUsers\UserOpLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Comment extends Controller
{
    /**
     * @api               {post} /api/content/comment 评论文章
     * @apiGroup          内容操作
     * @apiName           评论文章
     *
     * @apiParam {String} content
     * @apiParam {String} cid  文章id
     * @apiParam {String} pid  上一层评论id，第一层为0
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     */
    /**
     * @param Request $request
     * @return array
     */
    public function comment(Request $request): array
    {
        $cid = $request->post('cid', 0);
        $pid = $request->post('pid', 0);
        if (!$cid) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
        }
        $content = $request->post('content', '');
        DB::beginTransaction();
        $uid = Auth::id();
        try {
            DB::commit();
            ContentComment::query()->insertGetId([
                'user_id' => $uid,
                'content_id' => $cid,
                'parent_id' => $pid,
                'content' => $content,
            ]);

            $typ = Common::USER_OP_COMMENT;
            ContentCounts::incrementOrCreate($cid, $typ);
            UserCounts::incrementOrCreate($uid, $typ);
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'op_typ_id' => $cid,
                'typ' => $typ
            ]);
            return ['id' => $cid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }

}