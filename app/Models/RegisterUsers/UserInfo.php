<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


/**
 * Class UserInfo
 * @package App\Models\RegisterUsers
 * @author  author  李文龙 <liwenlong@inke.cn>
 */
class UserInfo extends Model
{
    public $table = 'user_info';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


}