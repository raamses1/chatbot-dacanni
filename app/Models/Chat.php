<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
   protected $fillable = [
        'user_chat_id',
        'message',
        'reply',
        'intent',
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(UserChat::class, 'user_chat_id');
    }
}
