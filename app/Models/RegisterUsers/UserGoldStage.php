<?php

namespace App\Models\RegisterUsers;



use App\Models\Model;

class UserGoldStage extends Model
{
    protected $table = 'user_gold_stage';

    protected $needLog = false;

    protected $hidden = ['update_time'];
}