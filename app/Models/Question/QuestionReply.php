<?php

namespace App\Models\Question;

use App\Models\Model;


class QuestionReply extends Model
{
    public $table = 'question_reply';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


}