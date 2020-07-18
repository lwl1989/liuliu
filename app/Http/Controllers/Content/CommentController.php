<?php


namespace App\Http\Controllers\Content;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\Constant\Common;
use App\Models\Content\Content;
use App\Models\Content\ContentComment;
use App\Models\Content\ContentCounts;
use App\Models\RegisterUsers\UserCounts;
use App\Models\RegisterUsers\UserInfo;
use App\Models\RegisterUsers\UserNotice;
use App\Models\RegisterUsers\UserOpLog;
use App\Models\RegisterUsers\Users;
use App\Models\RegisterUsers\UserZan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
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
        $contentValue = $request->post('content', '');
        $content = Content::query()->where('id', $cid)->first();
        if (!$content) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
        }
        $uid = Auth::id();

        DB::beginTransaction();
        try {
            DB::commit();
            ContentComment::query()->insertGetId([
                'user_id' => $uid,
                'content_id' => $cid,
                'parent_id' => $pid,
                'content' => $contentValue,
            ]);

            $typ = Common::USER_OP_COMMENT;
            ContentCounts::incrementOrCreate($cid, $typ);
            ContentCounts::incrementOrCreate($cid, Common::USER_OP_BE_COMMENT);
            UserCounts::incrementOrCreate($uid, $typ);
            UserOpLog::query()->insert([
                'user_id' => $uid,
                'op_typ_id' => $cid,
                'typ' => $typ
            ]);

            UserNotice::query()->insert([
                'user_id'   =>  $content->user_id,
                'obj_id'    =>  $cid,
                'op_user_id'=>  $uid,
                'op_type'   =>  Common::USER_OP_BE_COMMENT,
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
     * @api               {get} /api/content/comments 获取评论列表
     *
     * @apiParam {String} id 内容cid
     * @apiGroup          内容获取
     * @apiName           获取评论列表
     *
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      'comments':{
     *              "id": "1",
     *              "content": "真人秀",
     *              "user":{//用户信息},
     *              "zan":"1",
     *              "zanCount":"1",
     *              "replies":[//comment对象 和本级内容一致]
     *       },

     *   }
     */
    /**
     * @param Request $request
     * @return array
     */
    public function all(Request $request): array
    {
        $id = $request->get('id');
        if ($id < 1) {
            return ['code' => ErrorConstant::PARAMS_ERROR, 'response' => 'id错误'];
        }

        $comments = ContentComment::query()->where('content_id', $id)->where('status', Common::STATUS_NORMAL)->get()->toArray();
        //->where('parent_id', 0)
        if (!empty($comments)) {
            $comments = [];
            $commentIds = array_column($comments, 'id');
            $zanCount = UserZan::query()->where('typ', 2)
                ->where('obj_id', $commentIds)
                ->groupBy('obj_id')
                ->selectRaw('count(*) as count, obj_id')->get()->toArray();
            $comments = UserInfo::getUserInfoWithList($comments);

            foreach ($comments as &$comment) {
                $comment['zanCount'] = 0;
                foreach ($zanCount as $item) {
                    if ($item['obj_id'] == $comment['id']) {
                        $comment['zanCount'] = $item['count'];
                    }
                }
                if(is_numeric($comment['create_time'])) {
                    $comment['create_time'] = date('Y-m-d H:i:s', $comment['create_time']);
                    $comment['update_time'] = date('Y-m-d H:i:s', $comment['update_time']);
                }
                unset($comment);
            }


            $uid = Auth::id();
            if ($uid > 0) {
                $exists = UserZan::query()->where('typ', 2)->where('obj_id', $commentIds)->where('user_id', $uid)->get(['id', 'user_id', 'obj_id'])->toArray();
                foreach ($comments as &$comment) {
                    $comment['zan'] = 0;

                    foreach ($exists as $exist) {
                        if ($exist['obj_id'] == $comment['id']) {
                            $comment['zan'] = 1;
                            break;
                        }
                    }
                    unset($comment);
                }
            }

            $results = [];
            $findIds = [];
            foreach ($comments as $comment) {
                $comment['replies'] = [];
                if($comment['parent_id'] == 0) {
                    $results[] = $comment;
                    $findIds[] = $comment['id'];
                }
            }

            if(count($findIds) < count($comments)) {
                foreach ($comments as $comment) {
                    if ($comment['parent_id'] != 0) {
                        foreach ($results as &$result) {
                            if ($comment['parent_id'] == $result['id']) {
                                $result['replies'][] = $comment;
                                $findIds[] = $comment['id'];
                                unset($result);
                                break;
                            }
                            unset($result);
                        }
                    }
                }
            }


            if(count($findIds) < count($comments)) {
                foreach ($comments as $comment) {
                    if ($comment['parent_id'] != 0) {
                        foreach ($results as &$result) {
                            if(isset($result['replies']) and count($result['replies']) > 0) {
                                foreach ($result['replies'] as &$reply) {
                                    if ($comment['parent_id'] == $reply['id']) {
                                        $result['replies'][] = $comment;
                                        $findIds[] = $comment['id'];
                                        unset($result);
                                        break;
                                    }
                                    unset($reply);
                                }
                            }
                            unset($result);
                        }
                    }
                }
            }

        }
        return [
            'comments' => $comments
        ];
    }

}