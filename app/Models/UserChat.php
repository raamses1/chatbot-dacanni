<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
   protected $table = 'users_chat';

    protected $fillable = [
        'session_id',
        'ip'
    ];

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}