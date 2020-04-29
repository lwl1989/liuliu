<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/28
 * Time: 18:16
 */

namespace App\Models\Content;


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

}