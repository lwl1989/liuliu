<?php
namespace App\Models\RegisterUsers;

use App\Models\Model;

class UserOpLog extends Model
{
    public $table = 'user_op_log';
    public $timestamps = false;
}