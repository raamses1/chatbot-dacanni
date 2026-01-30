<?php

namespace App\Services;

class ChatbotService
{
     protected array $products = [
        'blusa',
        'guayabera',
        'vestido',
        'alebrije',
        'top',
        'corset',
        'pantalon'
    ];

    protected array $intents = [

        'saludo' => [
            'keywords' => ['hola','buenas','hey','que','tal'],
            'response' => '¡Hola! 👋 ¿En qué puedo ayudarte hoy?'
        ],

        'envio' => [
            'keywords' => ['envio','envios','mandan','entrega','paqueteria'],
            'response' => 'Hacemos envíos a todo México 📦'
        ],

        'precio' => [
            'keywords' => ['precio','cuesta','costo','vale'],
            'response' => '¿De qué producto quieres saber el precio?'
        ],

    ];

    public function processMessage(string $rawMessage, $userChat)
    {
        // normalizar texto
        $message = $this->normalizeText($rawMessage);

        $words = explode(' ', $message);

        // detectar producto
        $detectedProduct = $this->detectProduct($words);

        if ($detectedProduct) {
            $userChat->last_product = $detectedProduct;
        }

        // detectar intent
        [$intent, $score] = $this->detectIntent($words);

        $userChat->last_intent = $intent;
        $userChat->save();

        // generar respuesta
        $reply = $this->buildReply($intent, $userChat);

        return [
            'reply' => $reply,
            'intent' => $intent,
            'score' => $score
        ];
    }

    //detectar producto
    private function detectProduct(array $words): ?string
    {
        foreach ($this->products as $product) {
            if (in_array($product, $words)) {
                return $product;
            }
        }

        return null;
    }


    //detectar intencion
    private function detectIntent(array $words): array
    {
        $scores = [];

        foreach ($this->intents as $intent => $data) {

            $scores[$intent] = 0;

            foreach ($data['keywords'] as $keyword) {

                if (in_array($keyword, $words)) {
                    $scores[$intent]++;
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

        return [$bestIntent, $maxScore];
    }


    //imprimir la respuesta final
    private function buildReply(?string $intent, $userChat): string
    {
        // precio con producto
        if ($intent === 'precio' && $userChat->last_product) {

            return 'El precio del ' .
                $userChat->last_product .
                ' es de $5000 MXN 💰';

        }

        // envio con producto
        if ($intent === 'envio' && $userChat->last_product) {

            return 'El envío del ' .
                $userChat->last_product .
                ' tarda de 2 a 4 días 📦';

        }

        // respuesta normal
        if ($intent && isset($this->intents[$intent])) {

            return $this->intents[$intent]['response'];

        }

        return 'Lo siento, no entendí tu pregunta 😕';
    }


    //funcion para normalizar texto
    private function normalizeText(string $text): string
    {
        $text = strtolower(trim($text));

        $text = str_replace(
            ['á','é','í','ó','ú','ñ'],
            ['a','e','i','o','u','n'],
            $text
        );

        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        return $text;
    }
    }

