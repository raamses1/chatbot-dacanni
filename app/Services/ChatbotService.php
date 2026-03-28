<?php

namespace App\Services;

use App\Services\WooCommerceService;
use App\Services\AnthropicService;
use Illuminate\Http\Request;
use App\Models\Chat;

class ChatbotService
{
    protected WooCommerceService $woo;
    protected AnthropicService $anthropic;

    public function __construct(WooCommerceService $woo, AnthropicService $anthropic)
    {
        $this->woo = $woo;
        $this->anthropic = $anthropic;
    }

    protected array $products = [
        'blusa',
        'guayabera',
        'vestido',
        'alebrije',
        'top',
        'corset',
        'pantalon',
    ];

    protected array $intents = [
        'saludo' => [
            'keywords' => ['hola', 'buenas', 'hey', 'que', 'tal'],
            'response' => '¡Hola! 👋 ¿En qué puedo ayudarte hoy?',
        ],
        'envio' => [
            'keywords' => ['envio', 'envios', 'mandan', 'entrega', 'paqueteria'],
            'response' => 'Hacemos envíos a todo México 📦',
        ],
        'precio' => [
            'keywords' => ['precio', 'cuesta', 'costo', 'vale'],
            'response' => '¿De qué producto quieres saber el precio?',
        ],
        'pago' => [
            'keywords' => ['pago', 'pagos', 'tarjeta', 'transferencia', 'deposito', 'efectivo', 'cuenta'],
            'response' => 'Aceptamos tarjeta, transferencia, pago en línea, depósito bancario.',
        ],
        'horario' => [
            'keywords' => ['horario', 'horarios', 'abren', 'cierran', 'hora'],
            'response' => 'Lunes a sábado de 9am a 8pm, domingo de 10am a 7pm. Comida: 3pm a 4pm.',
        ],
        'stock' => [
            'keywords' => ['disponible', 'disponibilidad', 'existencia', 'stock', 'agotado', 'hay'],
            'response' => 'Déjame revisar la disponibilidad.',
        ],
    ];

    // -------------------------------------------------------------------------
    // PROCESO PRINCIPAL
    // -------------------------------------------------------------------------

    public function processMessage(string $rawMessage, $userChat): array
    {
        $message      = $this->normalizeText($rawMessage);
        $cleanMessage = $this->removeStopWords($message);

        $userChat->last_message = $cleanMessage;
        $userChat->save();

        $wordsOriginal = explode(' ', $message);
        $wordsClean    = explode(' ', $cleanMessage);

        $detectedProduct = $this->detectProduct($wordsClean);

        if ($detectedProduct) {
            $userChat->last_product       = $detectedProduct;
            $userChat->conversation_state = null;
            $userChat->save();
        }

        // Estado: esperando producto para precio
        if ($userChat->conversation_state === 'waiting_product_price') {
            if ($detectedProduct) {
                $userChat->conversation_state = null;
                $userChat->last_product       = $detectedProduct;
                $userChat->save();

                return $this->searchAndSuggest($detectedProduct, 'precio', $userChat);
            }

           // Intentar con Groq por si el usuario describió el producto
    $aiReply = $this->anthropic->ask(
        $rawMessage,
        'El usuario está buscando el precio de un producto en la tienda Dacanni. Pídele amablemente que especifique el nombre exacto del producto.'
    );

    // Limpiar estado después de 2 intentos fallidos
    $userChat->conversation_state = null;
    $userChat->save();

    return [
        'reply'    => $aiReply ?? 'No pude identificar el producto. ¿Puedes decirme el nombre exacto?',
        'intent'   => null,
        'score'    => 0,
        'products' => [],
    ];
        }

        [$intent, $score] = $this->detectIntent($wordsOriginal);

// Si el score es muy bajo, no confiar en la intención detectada
$intentasConUmbralBajo = ['envio', 'pago', 'horario', 'saludo', 'precio', 'stock'];

if ($score < 2 && !in_array($intent, $intentasConUmbralBajo)) {
    $intent = null;
}

        $userChat->last_intent = $intent;
        $userChat->save();

        return $this->buildReply($intent, $userChat);
    }

    // -------------------------------------------------------------------------
    // SELECCIÓN DE PRODUCTO (desde botones del frontend)
    // -------------------------------------------------------------------------

    public function processSelection(int $productId, $userChat): array
    {
        if (!$userChat->awaiting_selection) {
            return [
                'reply'    => 'No hay ninguna selección pendiente.',
                'intent'   => null,
                'score'    => 0,
                'products' => [],
            ];
        }

        $suggested = json_decode($userChat->suggested_products, true);

        if (empty($suggested)) {
            return [
                'reply'    => 'No encontré los productos sugeridos. Intenta buscar de nuevo.',
                'intent'   => null,
                'score'    => 0,
                'products' => [],
            ];
        }

        $selected = collect($suggested)->firstWhere('id', $productId);

        if (!$selected) {
            return [
                'reply'    => 'No reconocí esa selección. Por favor elige una de las opciones.',
                'intent'   => null,
                'score'    => 0,
                'products' => [],
            ];
        }

        // Limpiar estado y guardar producto seleccionado
        $userChat->last_product       = $selected['name'];
        $userChat->awaiting_selection = 0;
        $userChat->suggested_products = null;
        $userChat->save();

        return $this->replyFromProduct($selected, $userChat->last_intent, $selected['name']);
    }

    // -------------------------------------------------------------------------
    // BÚSQUEDA Y SUGERENCIA DE PRODUCTOS
    // -------------------------------------------------------------------------

    private function searchAndSuggest(string $search, string $intent, $userChat): array
    {
        $products = $this->woo->getProducts($search);

        if ($this->woo->isError($products)) {
            $msg = $products['woo_error'] === 'connection'
                ? 'En este momento no puedo conectarme a la tienda 😕 Intenta más tarde.'
                : 'Hubo un problema consultando la tienda. Intenta de nuevo.';

            return ['reply' => $msg, 'intent' => $intent, 'score' => 0, 'products' => []];
        }

        if (empty($products)) {
            return [
                'reply'    => 'No encontré productos con ese nombre.',
                'intent'   => $intent,
                'score'    => 0,
                'products' => [],
            ];
        }

        // Guardar solo los campos necesarios
        $toSave = collect($products)->map(fn($p) => [
            'id'             => $p['id'],
            'name'           => $p['name'],
            'price'          => $p['price'] ?? null,
            'type'           => $p['type'] ?? 'simple',
            'stock_status'   => $p['stock_status'] ?? null,
            'stock_quantity' => $p['stock_quantity'] ?? null,
        ])->values()->toArray();

        $userChat->suggested_products = json_encode($toSave);
        $userChat->awaiting_selection = 1;
        $userChat->last_intent        = $intent;
        $userChat->save();

        $intentLabel = $intent === 'precio' ? 'saber el precio' : 'revisar la disponibilidad';

        return [
            'reply'    => '¿De cuál de estos quieres ' . $intentLabel . '?',
            'intent'   => $intent,
            'score'    => 1,
            'products' => $toSave, // El frontend usa esto para renderizar los botones
        ];
    }

    // -------------------------------------------------------------------------
    // DETECCIÓN
    // -------------------------------------------------------------------------

    private function detectProduct(array $words): ?string
    {
        foreach ($this->products as $product) {
            foreach ($words as $word) {
                if (str_contains($word, $product)) {
                    return $product;
                }
            }
        }
        return null;
    }

    private function detectIntent(array $words): array
    {
        $scores = [];

        foreach ($this->intents as $intent => $data) {
            $scores[$intent] = 0;
            foreach ($data['keywords'] as $keyword) {
                foreach ($words as $word) {
                    if (str_contains($word, $keyword)) {
                        $scores[$intent]++;
                    }
                }
            }
        }

         // Resolver conflictos: si precio y envio tienen el mismo score, 
    // y envio tiene al menos 1 punto, gana envio
    if (
        isset($scores['precio'], $scores['envio']) &&
        $scores['precio'] === $scores['envio'] &&
        $scores['envio'] >= 1
    ) {
        $scores['precio'] = 0;
    }

        $bestIntent = null;
        $maxScore   = 0;

        foreach ($scores as $intent => $score) {
            if ($score > $maxScore) {
                $maxScore   = $score;
                $bestIntent = $intent;
            }
        }

        return [$bestIntent, $maxScore];
    }

    // -------------------------------------------------------------------------
    // CONSTRUCCIÓN DE RESPUESTA
    // -------------------------------------------------------------------------

    private function buildReply(?string $intent, $userChat): array
    {
        // --- PRECIO ---
        if ($intent === 'precio' && !$userChat->last_product) {
            $userChat->conversation_state = 'waiting_product_price';
            $userChat->save();

            return [
                'reply'    => '¿De qué producto quieres saber el precio?',
                'intent'   => $intent,
                'score'    => 0,
                'products' => [],
            ];
        }

        if ($intent === 'precio' && $userChat->last_product) {
            $search = $this->cleanSearchText($userChat->last_message) ?: $userChat->last_product;
            return $this->searchAndSuggest($search, 'precio', $userChat);
        }

        // --- ENVÍO ---
        if ($intent === 'envio' && $userChat->last_product) {
            return [
                'reply'    => 'El envío del ' . $userChat->last_product . ' tarda de 2 a 4 días 📦',
                'intent'   => $intent,
                'score'    => 1,
                'products' => [],
            ];
        }

        // --- STOCK ---
        if ($intent === 'stock' && !$userChat->last_product) {
            return [
                'reply'    => '¿De qué producto quieres saber la disponibilidad?',
                'intent'   => $intent,
                'score'    => 0,
                'products' => [],
            ];
        }

        if ($intent === 'stock' && $userChat->last_product) {
            $search = $this->cleanSearchText($userChat->last_message) ?: $userChat->last_product;
            return $this->searchAndSuggest($search, 'stock', $userChat);
        }

        // --- RESPUESTA GENÉRICA ---
        if ($intent && isset($this->intents[$intent])) {
            return [
                'reply'    => $this->intents[$intent]['response'],
                'intent'   => $intent,
                'score'    => 1,
                'products' => [],
            ];
        }

        $aiReply = $this->anthropic->ask($userChat->last_message ?? '');

return [
    'reply'    => $aiReply ?? 'Lo siento, no entendí tu pregunta 😕',
    'intent'   => 'ai_fallback',
    'score'    => 0,
    'products' => [],
];
    }

    // -------------------------------------------------------------------------
    // RESPUESTA SEGÚN PRODUCTO
    // -------------------------------------------------------------------------

    private function replyFromProduct(array $product, ?string $intent, string $fallbackName): array
    {
        if ($intent === 'precio') {
            $price = $product['price'] ?? 'no disponible';
            return [
                'reply'    => 'El precio del ' . $product['name'] . ' es $' . $price . ' MXN',
                'intent'   => $intent,
                'score'    => 1,
                'products' => [],
            ];
        }

        if ($intent === 'stock') {
            return [
                'reply'    => $this->buildStockReply($product),
                'intent'   => $intent,
                'score'    => 1,
                'products' => [],
            ];
        }

        return [
            'reply'    => 'Producto: ' . $product['name'],
            'intent'   => $intent,
            'score'    => 1,
            'products' => [],
        ];
    }

    private function buildStockReply(array $product): string
{
    $article = $this->getArticle($product['name']);

    if (($product['type'] ?? 'simple') === 'simple') {
        if (($product['stock_status'] ?? '') === 'instock') {
            if (isset($product['stock_quantity'])) {
                return 'Tenemos ' . $product['stock_quantity'] . ' disponibles de ' . $article . ' ' . $product['name'];
            }
            return $article . ' ' . $product['name'] . ' está disponible';
        }
        return $article . ' ' . $product['name'] . ($article === 'La' ? ' está agotada' : ' está agotado');
    }

    if ($product['type'] === 'variable') {
        $variations = $this->woo->getVariations($product['id']);

        if ($this->woo->isError($variations)) {
            return $variations['woo_error'] === 'connection'
                ? 'No pude conectarme para revisar las variaciones 😕 Intenta más tarde.'
                : 'No pude verificar las variaciones del producto.';
        }

        $totalStock = 0;
        foreach ($variations as $variation) {
            if (($variation['stock_status'] ?? '') === 'instock' && isset($variation['stock_quantity'])) {
                $totalStock += (int) $variation['stock_quantity'];
            }
        }

        return $totalStock > 0
            ? 'Tenemos ' . $totalStock . ' disponibles de ' . $article . ' ' . $product['name']
            : $article . ' ' . $product['name'] . ' está agotado';
    }

    return 'No pude determinar la disponibilidad del producto.';
}

private function getArticle(string $name): string
{
    $feminine = ['blusa', 'guayabera', 'playera', 'camisa', 'falda'];
    $nameLower = strtolower($name);

    foreach ($feminine as $word) {
        if (str_contains($nameLower, $word)) {
            return 'La';
        }
    }

    return 'El';
}
    // -------------------------------------------------------------------------
    // PROCESAMIENTO DE TEXTO
    // -------------------------------------------------------------------------

    private function normalizeText(string $text): string
    {
        $text = strtolower(trim($text));
        $text = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $text
        );
        return preg_replace('/[^a-z0-9\s]/', '', $text);
    }

    private function removeStopWords(string $text): string
    {
        $stopWords = [
            'cuanto', 'cuesta', 'precio', 'vale', 'quiero',
            'saber', 'el', 'la', 'los', 'las', 'de', 'un', 'una',
            'por', 'favor', 'me', 'das',
        ];

        $words    = explode(' ', $text);
        $filtered = array_filter($words, fn($w) => !in_array($w, $stopWords));
        return implode(' ', $filtered);
    }

   private function cleanSearchText(string $text): string
{
    $stopWords = [
        'hay', 'tienes', 'tienen', 'disponible', 'disponibles',
        'stock', 'existencia', 'queda', 'quedan', 'precio',
        'cuesta', 'vale', 'el', 'la', 'los', 'las', 'de', 'del',
    ];

    $text = strtolower($text);
    foreach ($stopWords as $word) {
        $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/', '', $text);
    }

    // Eliminar letras sueltas (1 caracter)
    $text = preg_replace('/\b[a-z]\b/', '', $text);

    return trim(preg_replace('/\s+/', ' ', $text));
}

}