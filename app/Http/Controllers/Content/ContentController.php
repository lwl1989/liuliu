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
use App\Models\Content\ContentTags;
use App\Models\RegisterUsers\UserOpLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{

    public function release(Request $request): array
    {
        DB::beginTransaction();
        $uid = Auth::id();
        try {
            $params = ArrayParse::checkParamsArray(['category_id', 'title', 'typ', 'content', 'cover'], $request->input());
            $params['user_id'] = $uid;
            $cid = Content::query()->insertGetId($params);

            $tagParams = ArrayParse::checkParamsArray(['tag_ids'], $request->input());
            if (!is_array($tagParams['tag_ids'])) {
                throw new \Exception('ids为空', ErrorConstant::PARAMS_ERROR);
            }
            $tagParams['tag_ids'] = array_values($tagParams['tag_ids']);
            foreach ($tagParams['tag_ids'] as $tagId) {
                ContentTags::query()->insert([
                    'content_id'    =>  $cid,
                    'tag_id'        =>  $tagId
                ]);
            }

            $typ = Common::USER_OP_RELEASE;
            UserOpLog::incrementOrCreate($uid, $typ);

            DB::commit();
            return ['id' => $cid];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => $e->getMessage()];
        }
    }
}