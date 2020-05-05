<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


class UserRelations extends Model
{
    public $table = 'user_relation';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}