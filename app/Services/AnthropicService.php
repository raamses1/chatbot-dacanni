<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AnthropicService
{
    protected Client $client;
    protected string $apiKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.groq.com',
            'timeout'  => 15.0,
        ]);

        $this->apiKey = config('services.groq.key');
    }

    public function ask(string $userMessage, string $context = ''): ?string
    {
        try {
            $systemPrompt = "Eres el asistente virtual de Dacanni, una tienda de ropa artesanal oaxaquena. 
Responde siempre en espanol, de forma amable y MUY BREVE, maximo 2 oraciones. 
Solo respondes preguntas relacionadas con la tienda, productos, envios, pagos y moda. 
Si te preguntan algo ajeno a la tienda, redirige amablemente la conversacion en una oracion. 
Nunca inventes precios ni disponibilidad de productos. {$context}";

            $response = $this->client->post('/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'llama-3.1-8b-instant',
                    'max_tokens'  => 150,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userMessage],
                    ],
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['choices'][0]['message']['content'] ?? null;

        } catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'sin respuesta';
            Log::error('[Groq] Error: ' . $e->getMessage());
            Log::error('[Groq] Body: ' . $body);
            return null;
        }
    }
}