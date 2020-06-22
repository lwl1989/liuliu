<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/6/22
 * Time: 21:44
 */

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Models\Content\Area;

class AreaController extends Controller
{
    public function index()
    {
       $areas = Area::query()->where('status',Common::STATUS_NORMAL)->get()->toArray();
    }
}