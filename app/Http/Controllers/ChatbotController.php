<?php

namespace App\Http\Controllers;
use App\Models\UserChat;
use App\Models\Chat;
use Illuminate\Support\Facades\Session;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $rawMessage = $request->input('message');

        if(!$rawMessage){
            return response()->json([
                'error'=>'Mensaje vacio'
            ], 400);
        }

        //sesion
        $sessionId = Session::getId();
        $ip = $request->ip();

        $userChat = UserChat::firstOrCreate(
            ['session_id' => $sessionId],
            ['ip' => $ip]
        );

        //normalizar texto
        $message = $this->normalizeText($rawMessage);

        //intentos
        $intents = [
            'saludo' => [
            'keywords' => ['hola','buenas','hey','que tal'],
            'response' => '¡Hola! 👋 ¿En qué puedo ayudarte hoy?'
        ],
        'envio' => [
            'keywords' => ['envio','envios','mandan','entrega','paqueteria'],
            'response' => 'Hacemos envíos a todo México 📦 ¿Deseas saber costos o tiempos?'
        ],
        'precio' => [
            'keywords' => ['precio','cuesta','costo','vale'],
            'response' => 'Nuestros precios varían según el producto. ¿Cuál te interesa?'
        ]
        ];

        $words = explode(' ', $message);

        $scores = [];

        foreach($intents as $intent => $data){
            $scores[$intent] = 0;

            foreach($data['keywords'] as $keyword){
                foreach($words as $word){
                    if($word === $keyword){
                        $scores[$intent]++;
                    }
                }
            }
        }

        
        //mejor intento
        $bestIntent = null;
        $maxScore = 0;

        foreach($scores as $intent => $score){
            if($score > $maxScore){
                $maxScore = $score;
                $bestIntent = $intent;
            }
        }
        if($maxScore === 0){
            $reply = 'Lo siento no entendi, formul tu pregunta';
        }else{
            $reply = $intents[$bestIntent]['response'];
        }
        //guardar el chat
        $chat = Chat::create([
            'user_chat_id' => $userChat->id,
            'message' => $rawMessage,
            'reply' => $reply,
            'intent' => $bestIntent
        ]);

        return response()->json([
            'reply' => $reply,
            'intent' => $bestIntent,
            'score' => $score,
            'chat_id' => $chat->id,
            'session' => $sessionId
        ]);
    
    }

    private function normalizeText($text)
    {
        $text = strtolower($text);
        $text = trim($text);

        $text = str_replace(
            ['á','é','í','ó','ú','ñ'],
            ['a','e','i','o','u','n'],
            $text
        );

        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        $text = preg_replace('/(.)\1{2,}/', '$1', $text);

        return $text;
    }
}
