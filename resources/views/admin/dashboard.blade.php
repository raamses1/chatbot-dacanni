@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .metric-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
        overflow: hidden;
        animation: fadeUp 0.4s ease both;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: var(--blue);
    }

    .metric-card.pink::after { background: var(--pink); }
    .metric-card.green::after { background: #4ade80; }
    .metric-card.orange::after { background: #f97316; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .metric-card:nth-child(2) { animation-delay: 0.1s; }
    .metric-card:nth-child(3) { animation-delay: 0.2s; }
    .metric-card:nth-child(4) { animation-delay: 0.3s; }

    .metric-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .metric-value {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        color: var(--blue);
        line-height: 1;
    }

    .metric-card.pink .metric-value  { color: var(--pink); }
    .metric-card.green .metric-value { color: #16a34a; }
    .metric-card.orange .metric-value { color: #ea580c; }

    .metric-sub {
        font-size: 12px;
        color: var(--text-soft);
    }

    /* SECCIÓN INFERIOR */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    @media (max-width: 900px) {
        .bottom-grid { grid-template-columns: 1fr; }
    }

    .card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--shadow);
    }

    .card-title {
        font-family: 'Playfair Display', serif;
        font-size: 17px;
        color: var(--blue);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--gray-2);
    }

    /* BARRAS DE INTENCIONES */
    .intent-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .intent-name {
        width: 110px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text);
        flex-shrink: 0;
        text-transform: capitalize;
    }

    .intent-bar-wrap {
        flex: 1;
        background: var(--gray-2);
        border-radius: 6px;
        height: 8px;
        overflow: hidden;
    }

    .intent-bar {
        height: 100%;
        background: var(--blue);
        border-radius: 6px;
        transition: width 0.6s ease;
    }

    .intent-count {
        font-size: 12px;
        color: var(--text-soft);
        width: 30px;
        text-align: right;
        flex-shrink: 0;
    }

    /* SESIONES RECIENTES */
    .session-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--gray-2);
        font-size: 13px;
    }

    .session-row:last-child { border-bottom: none; }

    .session-id {
        font-family: monospace;
        font-size: 11px;
        color: var(--text-soft);
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .session-intent {
        background: var(--gray);
        color: var(--blue);
        font-size: 11px;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .session-link {
        color: var(--pink);
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
    }

    .session-link:hover { text-decoration: underline; }

    .empty-state {
        text-align: center;
        color: var(--text-soft);
        font-size: 14px;
        padding: 32px 0;
    }
</style>
@endpush

@section('content')

{{-- MÉTRICAS --}}
<div class="metrics-grid">
    <div class="metric-card">
        <p class="metric-label">Total sesiones</p>
        <p class="metric-value">{{ $totalSessions }}</p>
        <p class="metric-sub">Usuarios únicos</p>
    </div>

    <div class="metric-card pink">
        <p class="metric-label">Total mensajes</p>
        <p class="metric-value">{{ $totalMessages }}</p>
        <p class="metric-sub">Desde el inicio</p>
    </div>

    <div class="metric-card green">
        <p class="metric-label">Mensajes hoy</p>
        <p class="metric-value">{{ $todayMessages }}</p>
        <p class="metric-sub">{{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="metric-card orange">
        <p class="metric-label">Fallback a IA</p>
        <p class="metric-value">{{ $fallbackCount }}</p>
        <p class="metric-sub">Preguntas no reconocidas</p>
    </div>
    <div class="metric-card green">
    <p class="metric-label">Respuestas 👍</p>
    <p class="metric-value">{{ $ratingPositive }}</p>
    <p class="metric-sub">Calificaciones positivas</p>
</div>

<div class="metric-card pink">
    <p class="metric-label">Respuestas 👎</p>
    <p class="metric-value">{{ $ratingNegative }}</p>
    <p class="metric-sub">Calificaciones negativas</p>
</div>
</div>

{{-- INFERIOR --}}
<div class="bottom-grid">

    {{-- INTENCIONES --}}
    <div class="card">
        <h3 class="card-title">Intenciones más detectadas</h3>

        @if($intentStats->isEmpty())
            <p class="empty-state">Sin datos aún</p>
        @else
            @php $maxTotal = $intentStats->first()->total; @endphp
            @foreach($intentStats as $stat)
                <div class="intent-row">
                    <span class="intent-name">{{ $stat->intent }}</span>
                    <div class="intent-bar-wrap">
                        <div class="intent-bar" style="width: {{ ($stat->total / $maxTotal) * 100 }}%"></div>
                    </div>
                    <span class="intent-count">{{ $stat->total }}</span>
                </div>
            @endforeach
        @endif
    </div>

    {{-- SESIONES RECIENTES --}}
    <div class="card">
        <h3 class="card-title">Sesiones recientes</h3>

        @php
            $recentSessions = \App\Models\UserChat::withCount('chats')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get();
        @endphp

        @if($recentSessions->isEmpty())
            <p class="empty-state">Sin sesiones aún</p>
        @else
            @foreach($recentSessions as $session)
                <div class="session-row">
                    <span class="session-id">{{ $session->session_id }}</span>
                    <span class="session-intent">{{ $session->last_intent ?? 'sin intent' }}</span>
                    <a href="{{ route('admin.conversation.detail', $session->id) }}" class="session-link">Ver →</a>
                </div>
            @endforeach
        @endif
    </div>

</div>

@endsection