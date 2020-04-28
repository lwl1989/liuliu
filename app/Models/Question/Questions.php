<?php

namespace App\Models\Question;

use App\Models\Model;


class Questions extends Model
{
    public $table = 'question';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


}