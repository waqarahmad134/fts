<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\FileMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileMovementController extends Controller
{
    public function index()
    {
        $files = File::with('movements')->get();
        $users = User::with('role')->get();

        return view('file_movements.index', compact('files', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file_id'     => 'required|exists:files,id',
            'receiver_id' => 'required|exists:users,id',
            'file_note'   => 'nullable|string',
            'file_reject' => 'nullable|boolean',
        ]);

        FileMovement::create([
            'file_id'      => $validated['file_id'],
            'sender_id'    => Auth::id(),
            'receiver_id'  => $validated['receiver_id'],
            'file_note'    => $validated['file_note'],
            'receive_date' => now(),
            'file_reject'  => $validated['file_reject'] ?? false,
        ]);

        $message = ($validated['file_reject'] ?? false) ? 'File returned successfully' : 'File sent successfully';
        return redirect()->back()->with('toast_success', $message);
    }


    public function show($id)
    {
        $file = File::with('movements.sender', 'movements.receiver')->findOrFail($id);
        return view('file_movements.show', compact('file'));
    }
}

