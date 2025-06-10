<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class FileController extends Controller
{
    // public function index()
    // {
    //     $currentUser = auth()->user();
    //     $currentRole = $currentUser->role->name;

    //     $roleHierarchy = [
    //         'Junior Clerk',
    //         'Assistant Registrar',
    //         'Deputy Registrar',
    //         'Additional Registrar',
    //         'DG',
    //         'Registrar',
    //         'HCJ'
    //     ];

    //     $currentIndex = array_search($currentRole, $roleHierarchy);

    //     // Determine accessible users for sending files
    //     if (strtolower($currentRole) === 'admin') {
    //         $users = User::whereHas('role', function ($query) {
    //             $query->where('name', '!=', 'admin');
    //         })->with('role')->get();
    //     } else {
    //         $allowedRoles = [];

    //         if ($currentIndex !== false) {
    //             if (isset($roleHierarchy[$currentIndex - 1])) {
    //                 $allowedRoles[] = $roleHierarchy[$currentIndex - 1];
    //             }
    //             if (isset($roleHierarchy[$currentIndex + 1])) {
    //                 $allowedRoles[] = $roleHierarchy[$currentIndex + 1];
    //             }
    //         }

    //         $users = User::whereHas('role', function ($query) use ($allowedRoles) {
    //             $query->whereIn('name', $allowedRoles);
    //         })->with('role')->get();
    //     }

    //     // ✅ Fetch files based on user involvement
    //     $files = File::with(['creator', 'movements.receiver.role'])
    //         ->where(function ($query) use ($currentUser) {
    //             $query->where('created_by', $currentUser->id) // created by user
    //                 ->orWhereHas('movements', function ($q) use ($currentUser) {
    //                     $q->where('receiver_id', $currentUser->id); // assigned to user
    //                 });

    //             // If admin, allow all files
    //             if (strtolower($currentUser->role->name) === 'admin') {
    //                 $query->orWhereRaw('1 = 1');
    //             }
    //         })
    //         ->paginate(10);

    //     return view('files.index', compact('files', 'users'));
    // }

    public function index()
    {
        $currentUser = auth()->user();
        $currentRole = $currentUser->role;      // eager‑loaded relationship
        $currentLevel = $currentRole->level;
    
        /* -----------------------------------------------------------------
         | 1. Build the list of “adjacent” roles                             |
         |    (one step up and one step down in the hierarchy)               |
         ------------------------------------------------------------------*/
        if (strtolower($currentRole->name) === 'admin') {
            // Admin sees everyone except other admins
            $users = User::whereHas('role', fn ($q) => $q->where('name', '!=', 'admin'))
                         ->with('role')
                         ->get();
        } else {
            $prevRole = Role::where('level', '<', $currentLevel)
                            ->orderByDesc('level')
                            ->first();          // immediate lower role
    
            $nextRole = Role::where('level', '>', $currentLevel)
                            ->orderBy('level')
                            ->first();          // immediate higher role
    
            $allowedRoleIds = collect([$prevRole, $nextRole])
                              ->filter()        // remove nulls
                              ->pluck('id');
    
            $users = User::whereIn('role_id', $allowedRoleIds)->with('role')->get();
        }
    
        /* -----------------------------------------------------------------
         | 2. Fetch files:                                                   |
         |    • Created by current user                                      |
         |    • OR assigned to current user                                  |
         |    • Admin sees everything                                        |
         ------------------------------------------------------------------*/
        $filesQuery = File::with(['creator', 'movements.receiver.role'])
            ->where(function ($q) use ($currentUser) {
                $q->where('created_by', $currentUser->id)
                  ->orWhereHas('movements', fn ($m) =>
                        $m->where('receiver_id', $currentUser->id));
            });
    
        if (strtolower($currentRole->name) === 'admin') {
            $filesQuery = File::with(['creator', 'movements.receiver.role']); // reset: full list
        }
    
        $files = $filesQuery->orderByDesc('created_at')->paginate(10);
    
        return view('files.index', compact('files', 'users'));
    }

    public function file_history()
    {
        $currentUser = auth()->user();
        $currentRole = $currentUser->role->name;

        $roleHierarchy = [
            'Junior Clerk',
            'Assistant Registrar',
            'Deputy Registrar',
            'Additional Registrar',
            'DG',
            'Registrar',
            'HCJ'
        ];

        $currentIndex = array_search($currentRole, $roleHierarchy);

        // Determine accessible users for sending files
        if (strtolower($currentRole) === 'admin') {
            $users = User::whereHas('role', function ($query) {
                $query->where('name', '!=', 'admin');
            })->with('role')->get();
        } else {
            $allowedRoles = [];

            if ($currentIndex !== false) {
                if (isset($roleHierarchy[$currentIndex - 1])) {
                    $allowedRoles[] = $roleHierarchy[$currentIndex - 1];
                }
                if (isset($roleHierarchy[$currentIndex + 1])) {
                    $allowedRoles[] = $roleHierarchy[$currentIndex + 1];
                }
            }

            $users = User::whereHas('role', function ($query) use ($allowedRoles) {
                $query->whereIn('name', $allowedRoles);
            })->with('role')->get();
        }

        // ✅ Fetch files based on user involvement
        $files = File::with(['creator', 'movements.receiver.role'])
            ->where('status', 'closed')
            ->where(function ($query) use ($currentUser) {
                $query->where('created_by', $currentUser->id) // created by user
                    ->orWhereHas('movements', function ($q) use ($currentUser) {
                        $q->where('receiver_id', $currentUser->id); // assigned to user
                    });

                // If admin, allow all files
                if (strtolower($currentUser->role->name) === 'admin') {
                    $query->orWhereRaw('1 = 1');
                }
            })
            ->paginate(10);

        return view('files.history', compact('files', 'users'));
    }

    public function create()
    {
        return view('files.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'file_no' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'puc_proposal' => 'required|string',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx',
            'file_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $data = $request->except(['file_attachment', 'file_image']);
        $data['created_by'] = auth()->id(); // Automatically assign the creator

        // Save attachment if present
        if ($request->hasFile('file_attachment')) {
            $file = $request->file('file_attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/attachments'), $fileName);
            $data['file_attachment'] = 'uploads/attachments/' . $fileName;
        }

        // Save image if present
        if ($request->hasFile('file_image')) {
            $image = $request->file('file_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/images'), $imageName);
            $data['file_image'] = 'uploads/images/' . $imageName;
        }

        // dd($data);
        File::create($data);

        return redirect('/files')->with('toast_success', 'File created successfully');
    }

    public function edit($id)
    {
        $file = File::findOrFail($id);
        return view('files.edit', compact('file'));
    }

    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);
        $request->validate([
            'file_no' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'puc_proposal' => 'required|string',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx',
            'file_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'status' => 'required|in:pending,closed,reopened'
        ]);

        $data = $request->except(['file_attachment', 'file_image']);

        if ($request->hasFile('file_attachment')) {
            if ($file->file_attachment && file_exists(public_path($file->file_attachment))) {
                unlink(public_path($file->file_attachment));
            }
            
            $attachment = $request->file('file_attachment');
            $fileName = time() . '_' . $attachment->getClientOriginalName();
            $attachment->move(public_path('uploads/attachments'), $fileName);
            $data['file_attachment'] = 'uploads/attachments/' . $fileName;
        }

        // Save image if present
        if ($request->hasFile('file_image')) {
            // Delete old image if exists
            if ($file->file_image && file_exists(public_path($file->file_image))) {
                unlink(public_path($file->file_image));
            }
            
            $image = $request->file('file_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/images'), $imageName);
            $data['file_image'] = 'uploads/images/' . $imageName;
        }

        $file->update($data);

        return redirect('/files')->with('toast_success', 'File updated successfully');
    }

    public function updateStatus(Request $request)
    {
        $file = File::findOrFail($request->file_id);
        $file->status = $request->status;
        $file->save();
        return redirect()->back()->with('toast_success', 'File status updated successfully');
    }

    public function destroy($id)
    {
        try {
            $file = File::findOrFail($id);
            
            // Delete associated files if they exist
            if ($file->file_attachment && file_exists(public_path($file->file_attachment))) {
                unlink(public_path($file->file_attachment));
            }
            if ($file->file_image && file_exists(public_path($file->file_image))) {
                unlink(public_path($file->file_image));
            }
            
            $file->delete();
            return redirect()->back()->with('toast_success', 'File deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Error deleting file');
        }
    }

    public function show($id)
    {
        $file = File::with(['creator', 'movements.receiver.role'])->findOrFail($id);
        return view('files.show', compact('file'));
    }
}

