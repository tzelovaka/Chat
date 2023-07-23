<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = 'messages';
    protected $guarded = false;

    function getTimeAttribute() {
        return $this->created_at->diffForHumans();
    }
    function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function getIsOwnerAttribute() {
        return (int) $this->user_id === (int) auth()->id();
    }
}
