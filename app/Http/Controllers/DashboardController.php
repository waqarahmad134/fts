<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\Charge;
use App\Models\User;
use App\Models\Challan;
use App\Models\Fir;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('dashboard.index');
    }
}
