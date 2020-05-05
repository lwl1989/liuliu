<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/28
 * Time: 18:16
 */

namespace App\Models\Content;


use App\Models\Model;

class ContentTags extends Model
{
    public $table = 'content_relation';
    public $timestamps = false;
}