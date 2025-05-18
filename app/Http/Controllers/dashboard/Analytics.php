<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Carbon;


class Analytics extends Controller
{
  public function index()
  {
    $totalFiles = File::count();
    $pendingFiles = File::where('status', 'pending')->count();
    $closedFiles = File::where('status', 'closed')->count();
    $reopenedFiles = File::where('status', 'reopened')->count();

    // Today's files
    $todayFilesCount = File::whereDate('created_at', Carbon::today())->count();

    return view('content.dashboard.dashboards-analytics', compact(
        'totalFiles',
        'pendingFiles',
        'closedFiles',
        'reopenedFiles',
        'todayFilesCount'
    ));
  }
}
