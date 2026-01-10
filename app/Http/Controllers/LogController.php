<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    /**
     * Display the Laravel logs
     */
    public function index()
    {
        // Check if user is admin
        if (auth()->user()->role->name !== 'Admin') {
            abort(403, 'Unauthorized access');
        }

        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logPath)) {
            $content = File::get($logPath);
            
            // Parse the log file - each entry typically starts with [timestamp]
            $logEntries = preg_split('/\n(?=\[)/', $content);
            
            // Reverse to show newest first
            $logEntries = array_reverse($logEntries);
            
            // Limit to last 500 entries to avoid memory issues
            $logEntries = array_slice($logEntries, 0, 500);
            
            foreach ($logEntries as $entry) {
                if (!empty(trim($entry))) {
                    $logs[] = [
                        'content' => $entry,
                        'level' => $this->getLogLevel($entry)
                    ];
                }
            }
        }

        $fileSize = File::exists($logPath) ? File::size($logPath) : 0;
        $fileSizeFormatted = $this->formatBytes($fileSize);

        return view('logs.index', compact('logs', 'fileSizeFormatted'));
    }

    /**
     * Clear all logs
     */
    public function clear()
    {
        // Check if user is admin
        if (auth()->user()->role->name !== 'Admin') {
            return redirect()->back()->with('toast_error', 'Unauthorized access');
        }

        $logPath = storage_path('logs/laravel.log');

        if (File::exists($logPath)) {
            File::put($logPath, '');
            return redirect()->route('logs.index')->with('toast_success', 'Logs cleared successfully');
        }

        return redirect()->route('logs.index')->with('toast_error', 'Log file not found');
    }

    /**
     * Get log level from entry
     */
    private function getLogLevel($entry)
    {
        if (preg_match('/\.(ERROR|CRITICAL|ALERT|EMERGENCY)/', $entry)) {
            return 'error';
        } elseif (preg_match('/\.WARNING/', $entry)) {
            return 'warning';
        } elseif (preg_match('/\.INFO/', $entry)) {
            return 'info';
        } elseif (preg_match('/\.DEBUG/', $entry)) {
            return 'debug';
        }
        return 'info';
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
