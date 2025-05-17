<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Analytics extends Controller
{
  public function index()
  {
    $menuData = [json_decode(file_get_contents(resource_path('menu/verticalMenu.json')))] ;
    return view('content.dashboard.dashboards-analytics', compact('menuData'));
  }
}
