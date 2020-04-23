<?php

namespace App\Models\RegisterUsers;

use Illuminate\Database\Eloquent\Model;

class GroupUserRelation extends Model
{
	public $table = 'group_user_relation';

	public $timestamps = false;//停用created_at等
}