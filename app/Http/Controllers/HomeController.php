<?php

namespace App\Http\Controllers;

use App\Models\CountryCode;
use App\Services\Department\DepartmentService;

class HomeController extends Controller
{
    public function adminBusinessUnitList()
    {
        return DepartmentService::getAdminBusinessUnitWithRole();
    }

    public function allBusinessUnitList()
    {
        return DepartmentService::getAllBusinessUnitWithRole();
    }

    public function countryCode()
    {
        return CountryCode::query()
            ->orderBy('en_name')
            ->get(['id', 'zh_name', 'en_name', 'code'])
            ->toArray();
    }

    public function turnCountryCode($id)
    {
        return CountryCode::query()
            ->find($id, ['code'])
            ->toArray();
    }
}
