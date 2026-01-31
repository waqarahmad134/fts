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

    /**
     * Handle QR code scan - create file movement with receiver's note
     * File transfer requires a note from the receiver
     */
    public function scan(Request $request, $fileId)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Please login to scan QR code'], 401);
            }
            return redirect('/login')->with('toast_error', 'Please login to scan QR code');
        }

        try {
            $file = File::with('movements')->findOrFail($fileId);
            $currentUser = Auth::user();

            // Check if file is closed
            if ($file->status === 'closed') {
                $message = 'Cannot receive a closed file';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['error' => $message], 400);
                }
                return redirect('/files')->with('toast_error', $message);
            }

            // Get the latest movement to determine the current holder (sender)
            // If no movements exist, the file creator is the current holder
            $latestMovement = $file->movements()->orderBy('created_at', 'desc')->first();
            $senderId = null;
            
            if ($latestMovement) {
                // If there's a movement, the current holder is the receiver of the latest movement
                // But check if the latest movement was rejected - if so, sender should be the original sender
                if ($latestMovement->file_reject) {
                    // If rejected, the file goes back to the sender
                    $senderId = $latestMovement->sender_id;
                } else {
                    // Otherwise, current holder is the receiver
                    $senderId = $latestMovement->receiver_id;
                }
            } else {
                // No movements yet, creator is the current holder
                $senderId = $file->created_by;
            }

            // Prevent sending to yourself
            if ($senderId == $currentUser->id) {
                $message = 'You already have this file. You cannot receive it from yourself.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['error' => $message], 403);
                }
                return redirect('/files')->with('toast_error', $message);
            }

            // Check if file is already assigned to current user (avoid duplicates)
            // Only check non-rejected movements
            $existingMovement = FileMovement::where('file_id', $fileId)
                ->where('receiver_id', $currentUser->id)
                ->where('file_reject', false)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($existingMovement) {
                $message = 'File is already assigned to you';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => true, 'message' => $message], 200);
                }
                return redirect('/files')->with('toast_info', $message);
            }

            // Validate sender exists
            if (!$senderId || !User::find($senderId)) {
                \Log::error('Invalid sender ID for file transfer', ['sender_id' => $senderId, 'file_id' => $fileId]);
                $message = 'Unable to determine file sender. Please contact administrator.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['error' => $message], 500);
                }
                return redirect('/files')->with('toast_error', $message);
            }

            // HIERARCHY VALIDATION: Ensure step-by-step transfer (no role skipping)
            $sender = User::with('role')->find($senderId);
            $receiverRole = $currentUser->role;
            $senderRole = $sender->role;
            
            // Skip hierarchy check for Admin
            if (strtolower($receiverRole->name) !== 'admin') {
                // Calculate the level difference
                $levelDifference = abs($receiverRole->level - $senderRole->level);
                
                // Allow transfer only to adjacent levels (difference of 100)
                // Or same level (for lateral transfers)
                if ($levelDifference > 100) {
                    // Determine the correct next role in hierarchy
                    $direction = $receiverRole->level > $senderRole->level ? 'up' : 'down';
                    
                    if ($direction === 'up') {
                        // File should go to next higher role
                        $nextRole = Role::where('level', '>', $senderRole->level)
                                       ->where('level', '<', $receiverRole->level)
                                       ->orderBy('level')
                                       ->first();
                    } else {
                        // File should go to next lower role
                        $nextRole = Role::where('level', '<', $senderRole->level)
                                       ->where('level', '>', $receiverRole->level)
                                       ->orderByDesc('level')
                                       ->first();
                    }
                    
                    $message = "❌ Cannot Skip Hierarchy Levels!\n\n";
                    $message .= "This file is currently with: {$senderRole->name}\n";
                    $message .= "You are trying to receive as: {$receiverRole->name}\n\n";
                    
                    if ($nextRole) {
                        $message .= "✅ The file must first go to: {$nextRole->name}\n\n";
                        $message .= "Please ask the {$senderRole->name} to send this file to {$nextRole->name} first, then it can be forwarded to you.";
                    } else {
                        $message .= "Files must move step-by-step through the hierarchy. You cannot skip intermediate roles.";
                    }
                    
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'error' => $message,
                            'current_holder_role' => $senderRole->name,
                            'your_role' => $receiverRole->name,
                            'next_role_required' => $nextRole ? $nextRole->name : null
                        ], 403);
                    }
                    return redirect('/files')->with('toast_error', $message);
                }
            }

            // Check if note is provided
            $fileNote = $request->input('file_note');
            
            if (!$fileNote || trim($fileNote) === '') {
                // Note is required - return file info for note input
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'require_note' => true,
                        'file_id' => $fileId,
                        'file_no' => $file->file_no,
                        'subject' => $file->subject,
                        'sender_id' => $senderId,
                        'message' => 'Please provide a note to receive this file'
                    ], 200);
                }
                return redirect('/files')->with('toast_error', 'Note is required to receive the file');
            }

            // Create file movement with receiver's note
            FileMovement::create([
                'file_id'      => $fileId,
                'sender_id'    => $senderId,
                'receiver_id'  => $currentUser->id,
                'file_note'    => $fileNote,
                'receive_date' => now(),
                'file_reject'  => false,
            ]);

            $message = 'File received successfully with your note';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $message], 200);
            }
            return redirect('/files')->with('toast_success', $message);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = 'File not found';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => $message], 404);
            }
            return redirect('/files')->with('toast_error', $message);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('QR Code scan database error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $message = 'Database error occurred. Please try again or contact administrator.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => $message, 'debug' => config('app.debug') ? $e->getMessage() : null], 500);
            }
            return redirect('/files')->with('toast_error', $message);
        } catch (\Exception $e) {
            \Log::error('QR Code scan failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $message = 'Failed to receive file: ' . ($e->getMessage() ?? 'Unknown error occurred');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => config('app.debug') ? $message : 'Failed to receive file. Please try again.'], 500);
            }
            return redirect('/files')->with('toast_error', config('app.debug') ? $message : 'Failed to receive file. Please try again.');
        }
    }

    /**
     * Show QR scanner page
     */
    public function scanner()
    {
        return view('file_movements.scanner');
    }
}

