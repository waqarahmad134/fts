<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id', 'sender_id', 'receiver_id','file_reject', 'file_note', 'receive_date'
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}

