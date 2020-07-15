<?php

namespace App\Models\RegisterUsers;

use App\Models\Model;


class UserFavorites extends Model
{
    //1文章  2评论 3 问题 4 回答 5 场景意见
    const UserFavoritesContent = 1;
    const UserFavoritesComment = 2;
    const UserFavoritesQuestion = 3;
    const UserFavoritesAnswer = 4;
    const UserFavoritesScene = 5;
    public $table = 'user_favorites';


    public $timestamps = false;
}