<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


class UserZan extends Model
{
    //1文章  2评论 3 问题 4 回答 5 场景意见
    const UserZanContent = 1;
    const UserZanComment = 2;
    const UserZanQuestion = 3;
    const UserZanAnswer = 4;
    const UserZanScene = 5;
    public $table = 'user_zan';


    public $timestamps = false;
}