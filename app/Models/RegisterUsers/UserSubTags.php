<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


class UserSubTags extends Model
{
    public $table = 'user_sub_tags';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


}