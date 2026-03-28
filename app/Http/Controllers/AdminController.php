<?php

namespace App\Http\Controllers;

use App\Models\UserChat;
use App\Models\Chat;
use Illuminate\Http\Request;

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

        return view('admin.dashboard', compact(
            'totalSessions',
            'totalMessages',
            'todayMessages',
            'fallbackCount',
            'intentStats',
            'topIntent',
            'ratingPositive',
            'ratingNegative'
        ));
    }

    public function conversations(Request $request)
    {
        $conversations = UserChat::withCount('chats')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('admin.conversations', compact('conversations'));
    }

    public function conversationDetail($id)
    {
        $userChat = UserChat::with('chats')->findOrFail($id);
        return view('admin.conversation-detail', compact('userChat'));
    }
}