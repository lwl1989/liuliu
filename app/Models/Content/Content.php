<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/28
 * Time: 18:16
 */

namespace App\Models\Content;


use App\Models\Model;

class Content extends Model
{
    public $table = 'content';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

}