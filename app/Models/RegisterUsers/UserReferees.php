<?php

namespace App\Models\RegisterUsers;


use App\Models\Model;

class UserReferees extends Model
{
    protected $table = 'user_referees';
    const CREATED_AT =  'create_time';
    const UPDATED_AT = 'update_time';
}