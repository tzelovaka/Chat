<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';
    protected $guarded = false;
    
    public function user(){
        return $this->belongsToMany(User::class, 'chat_user', 'chat_id', 'user_id');
    }

    function messages(){
        return $this->hasMany(Message::class, 'chat_id', 'id');
    }
}
