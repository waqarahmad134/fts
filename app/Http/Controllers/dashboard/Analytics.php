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

    // Stats for the logged-in user's files only (created_by)
    $totalFiles = File::where('created_by', $user->id)->count();
    $pendingFiles = File::where('created_by', $user->id)->where('status', 'pending')->count();
    $closedFiles = File::where('created_by', $user->id)->where('status', 'closed')->count();
    $reopenedFiles = File::where('created_by', $user->id)->where('status', 'reopened')->count();

    // Today's files created by the logged-in user
    $todayFilesCount = File::where('created_by', $user->id)
      ->whereDate('created_at', Carbon::today())
      ->count();

    // Count pending files assigned to current user (files waiting for them to scan/receive)
    $pendingFilesForUser = \App\Models\FileMovement::where('receiver_id', $user->id)
      ->where('file_reject', false)
      ->whereHas('file', function($query) {
        $query->where('status', '!=', 'closed');
      })
      ->count();

    return view('content.dashboard.dashboards-analytics', compact(
        'totalFiles',
        'pendingFiles',
        'closedFiles',
        'reopenedFiles',
        'todayFilesCount',
        'pendingFilesForUser',
        'user'
    ));
  }
}
