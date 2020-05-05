<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/28
 * Time: 18:16
 */

namespace App\Models\Content;


use App\Library\Constant\Common;
use App\Models\Model;

class ContentCounts extends Model
{
    public $table = 'content_counts';
    public $timestamps = false;

    public static function incrementOrCreate($cid, int $typ)
    {
        $exists = ContentCounts::query()->where('content_id', $cid)->where('typ', $typ)->first('id');
        if (!empty($exists)) {
            ContentCounts::query()->insert([
                'content_id' => $cid,
                'counts' => 1,
                'typ' => $typ
            ]);
        } else {
            ContentCounts::query()->where('content_id', $cid)->where('typ', $typ)->increment('counts');
        }
    }

    public static function getContentsCounts(array $list, array $types = [Common::USER_OP_BE_ZAN,Common::USER_OP_BE_COMMENT]) : array
    {
        if (empty($list)) {
            return $list;
        }

        $listIds = array_column($list, 'id');
        $ccs = ContentCounts::query()->whereIn('content_id', $listIds)->whereIn('typ', $types)->get()->toArray();
        foreach ($list as &$item) {
           foreach ($types as $type) {
               $item[$type] = 0;
           }

           foreach ($ccs as $cc) {
               if($item['id'] == $cc['content_id']) {
                   $item[$cc['typ']] = $cc['counts'];
               }
           }
        }

        return $list;
    }

}