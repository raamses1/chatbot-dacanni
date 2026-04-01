<?php

namespace App\Http\Controllers;

use App\Models\UserChat;
use App\Models\Chat;
use Illuminate\Http\Request;
use App\Models\Setting;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalSessions    = UserChat::count();
        $totalMessages    = Chat::count();
        $todayMessages    = Chat::whereDate('created_at', today())->count();
        $fallbackCount    = Chat::where('intent', 'ai_fallback')->count();
        $ratingPositive = Chat::where('rating', 1)->count();
        $ratingNegative = Chat::where('rating', 0)->count();

        $intentStats = Chat::whereNotNull('intent')
            ->where('intent', '!=', 'ai_fallback')
            ->selectRaw('intent, COUNT(*) as total')
            ->groupBy('intent')
            ->orderByDesc('total')
            ->get();

        $topIntent = $intentStats->first();

        $maintenanceMode = Setting::get('maintenance_mode', '0') === '1';

return view('admin.dashboard', compact(
    'totalSessions',
    'totalMessages',
    'todayMessages',
    'fallbackCount',
    'intentStats',
    'topIntent',
    'ratingPositive',
    'ratingNegative',
    'maintenanceMode'
));
    }

    public function conversations(Request $request)
    {
       $query = UserChat::withCount('chats')->orderByDesc('updated_at');

    // Filtro por intención
    if ($request->filled('intent')) {
        $query->where('last_intent', $request->intent);
    }

    // Filtro por fecha
    if ($request->filled('date')) {
        $query->whereDate('updated_at', $request->date);
    }

    $conversations = $query->paginate(15)->withQueryString();

    $intents = ['saludo', 'precio', 'stock', 'envio', 'pago', 'horario', 'ai_fallback'];

    return view('admin.conversations', compact('conversations', 'intents'));
    }

    public function conversationDetail($id)
    {
        $userChat = UserChat::with('chats')->findOrFail($id);
        return view('admin.conversation-detail', compact('userChat'));
    }
    public function toggleMaintenance(Request $request)
{
    $current = \App\Models\Setting::get('maintenance_mode', '0');
    $new = $current === '1' ? '0' : '1';
    \App\Models\Setting::set('maintenance_mode', $new);

    return back()->with('success', $new === '1' ? 'Chatbot en mantenimiento.' : 'Chatbot activado.');
}
}