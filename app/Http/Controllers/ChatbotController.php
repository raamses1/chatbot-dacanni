<?php

namespace App\Http\Controllers;

use App\Models\UserChat;
use App\Models\Chat;
use Illuminate\Support\Str;
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

    public function testWoo(WooCommerceService $woo)
    {
        return $woo->getProducts();
    }

    /**
     * Maneja mensajes normales del chatbot.
     * POST /api/v1/chat
     */
    public function handle(Request $request)
    {
        // Verificar modo mantenimiento
    if (\App\Models\Setting::get('maintenance_mode', '0') === '1') {
        return response()->json([
            'reply'    => 'El chatbot está temporalmente en mantenimiento 🔧 Vuelve pronto.',
            'intent'   => 'maintenance',
            'score'    => 0,
            'products' => [],
            'chat_id'  => null,
            'session'  => $request->input('session'),
        ]);
    }
    
        $rawMessage = $request->input('message');

        if (!$rawMessage) {
            return response()->json([
                'error' => 'Mensaje vacío'
            ], 400);
        }

        $sessionId = $request->input('session');

        if (!$sessionId) {
            $sessionId = Str::random(40);
        }

        $userChat = UserChat::firstOrCreate(
            ['session_id' => $sessionId],
            ['ip' => $request->ip()]
        );

        $result = $this->chatbotService->processMessage($rawMessage, $userChat);

        $chat = Chat::create([
            'user_chat_id' => $userChat->id,
            'message'      => $rawMessage,
            'reply'        => $result['reply'],
            'intent'       => $result['intent'],
        ]);

        return response()->json([
            'reply'    => $result['reply'],
            'intent'   => $result['intent'],
            'score'    => $result['score'],
            'products' => $result['products'] ?? [],
            'chat_id'  => $chat->id,
            'session'  => $sessionId,
        ]);
    }

    /**
     * Maneja la selección de un producto desde los botones del frontend.
     * POST /api/v1/select
     */
    public function select(Request $request)
    {
        $sessionId = $request->input('session');
        $productId = $request->input('product_id');

        if (!$sessionId || !$productId) {
            return response()->json([
                'error' => 'Faltan datos: session y product_id son requeridos'
            ], 400);
        }

        $userChat = UserChat::where('session_id', $sessionId)->first();

        if (!$userChat) {
            return response()->json([
                'error' => 'Sesión no encontrada'
            ], 404);
        }

        $result = $this->chatbotService->processSelection((int) $productId, $userChat);

        $chat = Chat::create([
            'user_chat_id' => $userChat->id,
            'message'      => 'Selección: product_id ' . $productId,
            'reply'        => $result['reply'],
            'intent'       => $result['intent'],
        ]);

        return response()->json([
            'reply'    => $result['reply'],
            'intent'   => $result['intent'],
            'score'    => $result['score'],
            'products' => $result['products'] ?? [],
            'chat_id'  => $chat->id,
            'session'  => $sessionId,
        ]);
    }
    
public function rate(Request $request)
{
    $chatId  = $request->input('chat_id');
    $rating  = $request->input('rating'); // 1 o 0

    if (is_null($chatId) || is_null($rating)) {
        return response()->json(['error' => 'Faltan datos'], 400);
    }

    $chat = Chat::find($chatId);

    if (!$chat) {
        return response()->json(['error' => 'Mensaje no encontrado'], 404);
    }

    $chat->rating = (int) $rating;
    $chat->save();

    return response()->json(['success' => true]);
}
public function history(Request $request)
{
    $sessionId = $request->input('session');

    if (!$sessionId) {
        return response()->json(['chats' => []]);
    }

    $userChat = UserChat::where('session_id', $sessionId)->first();

    if (!$userChat) {
        return response()->json(['chats' => []]);
    }

    $chats = Chat::where('user_chat_id', $userChat->id)
        ->orderBy('created_at', 'asc')
        ->take(20)
        ->get(['id', 'message', 'reply', 'intent', 'rating']);

    return response()->json(['chats' => $chats]);
}
public function stats()
{
    $totalSessions  = UserChat::count();
    $totalMessages  = Chat::count();
    $todayMessages  = Chat::whereDate('created_at', today())->count();
    $fallbackCount  = Chat::where('intent', 'ai_fallback')->count();
    $ratingPositive = Chat::where('rating', 1)->count();
    $ratingNegative = Chat::where('rating', 0)->count();

    $intentStats = Chat::whereNotNull('intent')
        ->where('intent', '!=', 'ai_fallback')
        ->selectRaw('intent, COUNT(*) as total')
        ->groupBy('intent')
        ->orderByDesc('total')
        ->get();

    return response()->json([
        'sessions'        => $totalSessions,
        'messages'        => $totalMessages,
        'messages_today'  => $todayMessages,
        'ai_fallbacks'    => $fallbackCount,
        'rating_positive' => $ratingPositive,
        'rating_negative' => $ratingNegative,
        'top_intents'     => $intentStats,
    ]);
}
}