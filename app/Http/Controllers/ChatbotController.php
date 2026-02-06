<?php

namespace App\Http\Controllers;
use App\Models\UserChat;
use App\Models\Chat;
use Illuminate\Support\Facades\Session;
use App\Services\ChatbotService;
use App\Services\WooCommerceService;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
     protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    public function testWoo(WooCommerceService $woo){
        return $woo->getProducts();
    }

    public function handle(Request $request)
    {
        $rawMessage = $request->input('message');

        if (!$rawMessage) {
            return response()->json([
                'error' => 'Mensaje vacío'
            ], 400);
        }

        // sesión
        $sessionId = Session::getId();
        $ip = $request->ip();

        $userChat = UserChat::firstOrCreate(
            ['session_id' => $sessionId],
            ['ip' => $ip]
        );

        // procesar con Service
        $result = $this->chatbotService->processMessage($rawMessage, $userChat);

        // guardar chat
        $chat = Chat::create([
            'user_chat_id' => $userChat->id,
            'message' => $rawMessage,
            'reply' => $result['reply'],
            'intent' => $result['intent']
        ]);

        return response()->json([
            'reply' => $result['reply'],
            'intent' => $result['intent'],
            'score' => $result['score'],
            'chat_id' => $chat->id,
            'session' => $sessionId
        ]);
    }
}