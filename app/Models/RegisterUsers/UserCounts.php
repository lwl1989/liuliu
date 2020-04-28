<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


/**
 * Class UserCounts
 * @package App\Models\RegisterUsers
 * @author  author  李文龙 <liwenlong@inke.cn>
 */
class UserCounts extends Model
{
    public $table = 'user_counts';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


}