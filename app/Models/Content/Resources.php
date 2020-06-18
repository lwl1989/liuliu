<?php


namespace App\Models\Content;


use App\Models\Model;

class Resources extends Model
{
    public $table = 'content_resources';

    protected $fillable = ['content_id'];
    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}