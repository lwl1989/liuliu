<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


class UserCoachTags extends Model
{
    public $table = 'user_coach_tags';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}