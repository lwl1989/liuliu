<?php

namespace App\Models\RegisterUsers;



use App\Models\Model;

class UserGold extends Model
{
    protected $table = 'user_gold_log';

    protected $needLog = false;

    protected $hidden = ['update_time'];
}