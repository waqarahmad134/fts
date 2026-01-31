<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_no', 'subject', 'puc_proposal', 'handover_note', 'file_image',
        'file_attachment', 'status', 'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movements()
    {
        return $this->hasMany(FileMovement::class);
    }

    /**
     * Get the current holder (sender) of the file
     * Returns the user who currently has the file
     */
    public function getCurrentHolder()
    {
        $latestMovement = $this->movements()->orderBy('created_at', 'desc')->first();
        
        if ($latestMovement) {
            // If rejected, file is with the original sender
            if ($latestMovement->file_reject) {
                return User::find($latestMovement->sender_id);
            }
            // Otherwise, file is with the receiver
            return User::find($latestMovement->receiver_id);
        }
        
        // No movements yet, file is with creator
        return $this->creator;
    }

    /**
     * Get the role name of current file holder
     */
    public function getCurrentHolderRole()
    {
        $holder = $this->getCurrentHolder();
        return $holder ? $holder->role->name : 'Unknown';
    }
}
