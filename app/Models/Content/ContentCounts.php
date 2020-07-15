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

    public static function incrementOrCreate($cid, int $typ, int $amount = 1)
    {
        $exists = ContentCounts::query()->where('content_id', $cid)->where('typ', $typ)->first(['id']);
       // var_dump($exists, $cid, $typ);
        if (!empty($exists)) {
            ContentCounts::query()->where('content_id', $cid)->where('typ', $typ)->increment('counts', $amount);
        } else {
            ContentCounts::query()->insert([
                'content_id' => $cid,
                'counts' => $amount,
                'typ' => $typ
            ]);
        }
    }

    public static function getContentsCounts(array $list, array $types = [Common::USER_OP_BE_ZAN,Common::USER_OP_BE_COMMENT]) : array
    {
        if (empty($list)) {
            return $list;
        }

        $listIds = array_column($list, 'id');

        $ccs = ContentCounts::query()->whereIn('content_id', $listIds)->whereIn('typ', $types)->get()->toArray();
       // var_dump($ccs);
        foreach ($list as &$item) {
            $item['counts'] = [];
           foreach ($types as $type) {
               $item['counts'][$type] = 0;
           }

           foreach ($ccs as $cc) {
               if($item['id'] == $cc['content_id']) {
                   $item['counts'][$cc['typ']] = $cc['counts'];
               }
           }
        }

        return $list;
    }




}