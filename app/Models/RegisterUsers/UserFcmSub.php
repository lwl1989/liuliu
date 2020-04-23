<?php

namespace App\Models\RegisterUsers;


use App\Models\Model;

class UserFcmSub extends Model
{
    protected $table = 'user_fcm';

    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';
}