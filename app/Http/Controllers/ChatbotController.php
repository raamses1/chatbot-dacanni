<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $rawMessage = $request->input('message');

        $message = $this->normalizeText($rawMessage);

        $intents = [
    'saludo' => [
        'keywords' => ['hola','buenas','hey','que tal'],
        'response' => '¡Hola! 👋 ¿En qué puedo ayudarte hoy?'
    ],
    'envio' => [
        'keywords' => ['envio','mandan','entrega','paqueteria'],
        'response' => 'Hacemos envíos a todo México 📦 ¿Deseas saber costos o tiempos?'
    ],
    'precio' => [
        'keywords' => ['precio','cuesta','costo','vale'],
        'response' => 'Nuestros precios varían según el producto. ¿Cuál te interesa?'
    ]
];

$words = explode(' ', $message);

$scores = [];

foreach ($intents as $intent => $data) {
    $scores[$intent] = 0;

    foreach ($data['keywords'] as $keyword) {
        foreach ($words as $word) {
            if ($word === $keyword) {
                $scores[$intent]++;
            }
        }
    }
}

$bestIntent = null;
$maxScore = 0;

foreach ($scores as $intent => $score) {
    if ($score > $maxScore) {
        $maxScore = $score;
        $bestIntent = $intent;
    }
}

if ($maxScore === 0) {
    $reply = 'Lo siento 😅 aún estoy aprendiendo. ¿Puedes reformular tu pregunta?';
} else {
    $reply = $intents[$bestIntent]['response'];
}


        return response()->json([
    'reply' => $reply,
    'intent' => $bestIntent,
    'score' => $maxScore
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
