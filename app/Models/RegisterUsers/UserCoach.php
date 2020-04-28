<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


class UserCoach extends Model
{
    public $table = 'user_coach';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


}