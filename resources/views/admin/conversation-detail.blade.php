@extends('admin.layout')

@section('title', 'Detalle de conversación')
@section('page-title', 'Detalle de conversación')

@push('styles')
<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--blue);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 24px;
        transition: color 0.18s;
    }
    .back-link:hover { color: var(--pink); }

    .detail-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .detail-grid { grid-template-columns: 1fr; }
    }

    .card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--shadow);
    }

    .card-title {
        font-family: 'Playfair Display', serif;
        font-size: 16px;
        color: var(--blue);
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--gray-2);
    }

    /* INFO DE SESIÓN */
    .info-row {
        display: flex;
        flex-direction: column;
        gap: 3px;
        margin-bottom: 14px;
    }

    .info-label {
        font-size: 11px;
        font-weight: 500;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 13.5px;
        color: var(--text);
        word-break: break-all;
    }

    .info-value.mono {
        font-family: monospace;
        font-size: 12px;
        color: var(--text-soft);
    }

    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }
    .badge-blue { background: #e8edf8; color: var(--blue); }
    .badge-pink { background: #fce8f3; color: var(--pink); }

    /* CHAT */
    .chat-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-height: 600px;
        overflow-y: auto;
        padding: 4px;
        scrollbar-width: thin;
        scrollbar-color: var(--gray-2) transparent;
    }

    .chat-container::-webkit-scrollbar { width: 4px; }
    .chat-container::-webkit-scrollbar-thumb { background: var(--gray-2); border-radius: 4px; }

    .msg-wrap {
        display: flex;
        flex-direction: column;
    }

    .msg-wrap.user { align-items: flex-end; }
    .msg-wrap.bot  { align-items: flex-start; }

    .msg-bubble {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 14px;
        font-size: 13.5px;
        line-height: 1.5;
    }

    .msg-wrap.user .msg-bubble {
        background: var(--blue);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .msg-wrap.bot .msg-bubble {
        background: var(--gray);
        color: var(--text);
        border-bottom-left-radius: 4px;
    }

    .msg-meta {
        font-size: 11px;
        color: var(--text-soft);
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .msg-intent {
        background: var(--gray-2);
        color: var(--blue);
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 500;
    }

    .msg-intent.ai { background: #fce8f3; color: var(--pink); }

    .empty-state {
        text-align: center;
        color: var(--text-soft);
        font-size: 14px;
        padding: 32px 0;
    }
</style>
@endpush

@section('content')

<a href="{{ route('admin.conversations') }}" class="back-link">
    ← Volver a conversaciones
</a>

<div class="detail-grid">

    {{-- INFO DE SESIÓN --}}
    <div class="card">
        <h3 class="card-title">Información de sesión</h3>

        <div class="info-row">
            <span class="info-label">ID</span>
            <span class="info-value">{{ $userChat->id }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Session ID</span>
            <span class="info-value mono">{{ $userChat->session_id }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">IP</span>
            <span class="info-value">{{ $userChat->ip ?? '—' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Último producto</span>
            <span class="info-value">{{ $userChat->last_product ?? '—' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Última intención</span>
            <span class="info-value">
                @if($userChat->last_intent)
                    <span class="badge {{ $userChat->last_intent === 'ai_fallback' ? 'badge-pink' : 'badge-blue' }}">
                        {{ $userChat->last_intent }}
                    </span>
                @else
                    —
                @endif
            </span>
        </div>

        <div class="info-row">
            <span class="info-label">Total mensajes</span>
            <span class="info-value">{{ $userChat->chats->count() }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Inicio</span>
            <span class="info-value">{{ $userChat->created_at->format('d/m/Y H:i') }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Última actividad</span>
            <span class="info-value">{{ $userChat->updated_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    {{-- HILO DE MENSAJES --}}
    <div class="card">
        <h3 class="card-title">Hilo de conversación</h3>

        @if($userChat->chats->isEmpty())
            <p class="empty-state">Sin mensajes registrados.</p>
        @else
            <div class="chat-container">
                @foreach($userChat->chats as $chat)

                    {{-- Mensaje del usuario --}}
                    <div class="msg-wrap user">
                        <div class="msg-bubble">{{ $chat->message }}</div>
                        <div class="msg-meta">{{ $chat->created_at->format('H:i') }}</div>
                    </div>

                    {{-- Respuesta del bot --}}
                    <div class="msg-wrap bot">
                        <div class="msg-bubble">{{ $chat->reply }}</div>
                        <div class="msg-meta">
                            {{ $chat->created_at->format('H:i') }}
                            @if($chat->intent)
                                <span class="msg-intent {{ $chat->intent === 'ai_fallback' ? 'ai' : '' }}">
                                    {{ $chat->intent }}
                                </span>
                            @endif
                        </div>
                    </div>

                @endforeach
            </div>
        @endif
    </div>

</div>

@endsection