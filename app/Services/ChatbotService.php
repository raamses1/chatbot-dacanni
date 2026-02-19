<?php

namespace App\Services;
use App\Services\WooCommerceService;

class ChatbotService
{
    protected WooCommerceService $woo;

    public function __construct(WooCommerceService $woo){
        $this->woo = $woo;
    }

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
        'pago' => [
            'keywords' => ['pago', 'pagos', 'tarjeta', 'transferencia', 'deposito', 'efectivo', 'cuenta'],
            'response' => 'Aceptamos tarjeta, transferencia, pago en linea (por la pagina), deposito bancario'
        ],
        'horario' => [
            'keywords' => ['horario', 'horarios', 'abren', 'cierran', 'hora'],
            'response' => 'Nuestro horario es de lunes a sabado de 9 am a 8 pm, domingo de 10 am a 7pm, con un horario de comida de 3pm a 4 pm'
        ],
        'stock' => [
            'keywords' => ['disponible', 'disponibilidad', 'existencia', 'stock', 'agotado','hay'],
            'response' => 'Dejame revisar la disponibilidad.'
        ],

    ];

    public function processMessage(string $rawMessage, $userChat)
    {
        // normalizar texto
        $message = $this->normalizeText($rawMessage);
        
        $cleanMessage = $this->removeStopWords($message);

        //guardar el ultimo mensaje
        $userChat->last_message = $cleanMessage;
        $userChat->save();


        $wordsOriginal = explode(' ', $message);
        $wordsClean = explode(' ', $cleanMessage);

        // detectar producto
        $detectedProduct = $this->detectProduct($wordsClean);

        if ($detectedProduct) {
            $userChat->last_product = $detectedProduct;
            $userChat->conversation_state = null; //limpia estado
            $userChat->save();
        }


        if($userChat->conversation_state === 'waiting_product_price'){
            if($detectedProduct){
                $userChat->conversation_state = null;
                $userChat->last_product = $detectedProduct;
                $userChat->save();

                return [
                    'reply' => 'La'. $detectedProduct .'cuesta $5000 mxn',
                    'intent' => 'precio',
                    'score' => 1
                ];
            }
            return [
                'reply' => '¿de que producto quieres saber el precio',
                'intent' => null,
                'score' => 0
            ];
        }

        // detectar intent
        [$intent, $score] = $this->detectIntent($wordsOriginal);

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
            foreach($words as $word){
                if (str_contains($word, $product)) {
                    return $product;
                }
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

                foreach($words as $word){
                    if(str_contains($word, $keyword)){
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

        return [$bestIntent, $maxScore];
    }


    //imprimir la respuesta final
    private function buildReply(?string $intent, $userChat): string
    {
        //precio sin producto
        if($intent === 'precio' &&!$userChat->last_product){
            $userChat->conversation_state = 'waiting_product_price';
            $userChat->save();

            return '¿de que producto quieres saber el precio?';
        }
        // precio con producto
        if ($intent === 'precio' && $userChat->last_product) {
            $product = $this->findProductWoo($userChat->last_message ?? $userChat->last_product);

            if(!$product){
                return 'No logre encontrar el producto';
            }
            return 'El precio del ' . $product['name'] .' es $ ' . $product['price'] .'MXN';
        }

        // envio con producto
        if ($intent === 'envio' && $userChat->last_product) {

            return 'El envío del ' .
                $userChat->last_product .
                ' tarda de 2 a 4 días 📦';

        }

        //detectar stock sin producto
        if($intent === 'stock' && !$userChat->last_product){
            return '¿De que producto quieres saber la disponibilidad?';
        };

        
        // stock con producto
if ($intent === 'stock' && $userChat->last_product) {

$cleanSearch = $this->cleanSearchText($userChat->last_message);

    $product = $this->findProductWoo($cleanSearch);

    if (!$product) {
        return 'No encontré ese producto';
    }

    //peoducto simple
    if ($product['type'] === 'simple') {

        if ($product['stock_status'] === 'instock') {

            if (isset($product['stock_quantity'])) {
                return 'Tenemos ' . $product['stock_quantity'] . ' disponibles del ' . $product['name'];
            }

            return 'El ' . $product['name'] . ' está disponible';
        }

        return 'El ' . $product['name'] . ' está agotado';
    }

    //producto variable
    if ($product['type'] === 'variable') {

        $variations = $this->woo->getVariations($product['id']);

        if (!$variations) {
            return 'No pude verificar las variaciones del producto';
        }

        $totalStock = 0;

        foreach ($variations as $variation) {

            if ($variation['stock_status'] === 'instock') {

                if (isset($variation['stock_quantity'])) {
                    $totalStock += (int) $variation['stock_quantity'];
                }
            }
        }

        if ($totalStock > 0) {
            return 'Tenemos ' . $totalStock . ' disponibles del ' . $product['name'];
        }

        return 'El ' . $product['name'] . ' está agotado';
    }
}


        // respuesta normal
        if ($intent && isset($this->intents[$intent])) {

            return $this->intents[$intent]['response'];

        }

        return 'Lo siento, no entendí tu pregunta 😕';
    }

    //encontrar producto woocomerce
    private function findProductWoo(string $name){
        $products = $this->woo->getProducts($name);
        

        if(empty($products)){
            return null;
        }
        return $this->bestMatch($products, $name); //optimizacion en la busqueda de productos 
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

    private function bestMatch(array $products, string $search)
{
    $search = strtolower($search);

    $best = null;
    $bestScore = 0;

    foreach ($products as $product) {

        if (!isset($product['name'])) {
            continue;
        }

        $name = strtolower($product['name']);

        // 🔥 PRIORIDAD 1: si el nombre contiene exactamente la palabra buscada
        if (str_contains($name, $search)) {
            return $product;
        }

        // 🔥 PRIORIDAD 2: similitud
        similar_text($search, $name, $percent);

        if ($percent > $bestScore) {
            $bestScore = $percent;
            $best = $product;
        }
    }

    return $best;
}

    private function removeStopWords(string $text): string
{
    $stopWords = [
        'cuanto','cuesta','precio','vale','quiero',
        'saber','el','la','los','las','de','un','una',
        'por','favor','me','das'
    ];

    $words = explode(' ', $text);

    $filtered = array_filter($words, function($word) use ($stopWords){
        return !in_array($word, $stopWords);
    });

    return implode(' ', $filtered);
}

private function cleanSearchText($text)
{
    $stopWords = [
        'hay',
        'tienes',
        'tienen',
        'disponible',
        'disponibles',
        'stock',
        'existencia',
        'queda',
        'quedan',
        'precio',
        'cuesta',
        'vale',
        'el',
        'la',
        'los',
        'las',
        'de',
        'del',
        '?'
    ];

    $text = strtolower($text);

    foreach ($stopWords as $word) {
        $text = str_replace($word, '', $text);
    }

    return trim(preg_replace('/\s+/', ' ', $text));
}


    }

