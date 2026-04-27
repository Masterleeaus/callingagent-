<?php

namespace Modules\TitanGo\Http\Controllers;

use App\Http\Controllers\Controller;

class UpgradeController extends Controller
{
    public function index()
    {
        return view('titango::upgrade');
    }
}
