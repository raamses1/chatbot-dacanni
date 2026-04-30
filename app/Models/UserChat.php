<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    protected $table = 'users_chat';

    protected $fillable = [
        'session_id',
        'ip',
        'last_intent',
        'last_topic',
        'last_product',
        'last_category',
        'last_message',
        'last_question',
        'suggested_products',
        'awaiting_selection',
        'conversation_state',
        'last_product_link',
    ];

    protected $casts = [
        'awaiting_selection' => 'boolean',
    ];

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}