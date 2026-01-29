<?php

namespace App\Http\Controllers;
use App\Models\UserChat;
use App\Models\Chat;
use Illuminate\Support\Facades\Session;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected $products = [
        'blusa',
        'guayabera',
        'vestido',
        'alebrije',
        'top',
        'corset',
        'pantalon'
    ]; 

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

        $detectedProduct = null;

        foreach($this->products as $product){
            if(in_array($product, $words)){
                $detectedProduct = $product;
                break;
            }
        }

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

        $userChat->last_intent = $bestIntent;

         if($detectedProduct){
            $userChat->last_product = $detectedProduct;
        }

        //topic basico
        if($bestIntent === 'precio'){
            $userChat->last_topic = 'producto';
        }

        if($bestIntent === 'envio'){
            $userChat->last_topic = 'envio';
        } 

        $userChat->save();

        if($bestIntent === 'precio' && $userChat->last_product){
            $reply = 'El precio del'.$userChat->last_product.' es de $5000';
        }
        elseif($bestIntent === 'envio' && $userChat->last_product){
            $reply = 'El envio del'.$userChat->last_product.' tarda de 2 a 4 dias';
        }
        elseif($maxScore > 0){
            //respuesta normal
            $reply = $intents[$bestIntent]['response'];
        }
        else{
            $reply ='Lo siento, no entendi tu pregunta';
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
            'score' => $maxScore,
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