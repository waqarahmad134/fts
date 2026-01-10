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
    $user = auth()->user();
    
    $totalFiles = File::count();
    $pendingFiles = File::where('status', 'pending')->count();
    $closedFiles = File::where('status', 'closed')->count();
    $reopenedFiles = File::where('status', 'reopened')->count();

    // Today's files created by the logged-in user
    $todayFilesCount = File::where('created_by', $user->id)
      ->whereDate('created_at', Carbon::today())
      ->count();

    return view('content.dashboard.dashboards-analytics', compact(
        'totalFiles',
        'pendingFiles',
        'closedFiles',
        'reopenedFiles',
        'todayFilesCount',
        'user'
    ));
  }
}
