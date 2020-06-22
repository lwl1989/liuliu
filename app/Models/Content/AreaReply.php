<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/6/22
 * Time: 21:45
 */

namespace App\Models\Content;


use App\Models\Model;

class AreaReply extends Model
{

    public $table = 'area_reply';

    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}