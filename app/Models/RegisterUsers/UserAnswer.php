<?php

namespace App\Models\RegisterUsers;


use App\Models\Model;

class UserAnswer extends Model
{
    protected $table = 'user_question_answer';

    protected $resultFormat = [
        'content'    =>  'jsonDecode'
    ];
}