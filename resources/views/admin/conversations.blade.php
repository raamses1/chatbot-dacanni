@extends('admin.layout')

@section('title', 'Conversaciones')
@section('page-title', 'Conversaciones')

@push('styles')
<style>
    .table-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--gray-2);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .table-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 17px;
        color: var(--blue);
    }

    .table-header span {
        font-size: 13px;
        color: var(--text-soft);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        background: var(--gray);
        padding: 12px 24px;
        text-align: left;
        font-size: 11px;
        font-weight: 500;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tbody tr {
        border-bottom: 1px solid var(--gray-2);
        transition: background 0.15s;
    }

    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--gray); }

    tbody td {
        padding: 14px 24px;
        font-size: 13.5px;
        color: var(--text);
        vertical-align: middle;
    }

    .session-code {
        font-family: monospace;
        font-size: 12px;
        color: var(--text-soft);
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }

    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }

    .badge-blue  { background: #e8edf8; color: var(--blue); }
    .badge-pink  { background: #fce8f3; color: var(--pink); }
    .badge-gray  { background: var(--gray-2); color: var(--text-soft); }

    .btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--blue);
        color: white;
        text-decoration: none;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        transition: background 0.18s;
    }

    .btn-detail:hover { background: var(--blue-light); }

    /* PAGINACIÓN */
    .pagination-wrap {
        padding: 20px 24px;
        border-top: 1px solid var(--gray-2);
        display: flex;
        justify-content: flex-end;
    }

    .pagination-wrap .pagination {
        display: flex;
        gap: 4px;
        list-style: none;
    }

    .pagination a,
    .pagination span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        font-size: 13px;
        text-decoration: none;
        color: var(--text);
        background: var(--gray);
        transition: background 0.15s, color 0.15s;
    }

    .pagination a:hover { background: var(--blue); color: white; }
    .pagination .active span { background: var(--blue); color: white; }

    .empty-state {
        text-align: center;
        padding: 48px;
        color: var(--text-soft);
        font-size: 14px;
    }
</style>
@endpush

@section('content')

<div class="table-card">
    <div class="table-header">
        <h3>Todas las conversaciones</h3>
        <span>{{ $conversations->total() }} sesiones en total</span>
    </div>

    {{-- FILTROS --}}
<form method="GET" action="{{ route('admin.conversations') }}" style="padding: 16px 24px; border-bottom: 1px solid var(--gray-2); display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
    <select name="intent" style="padding:8px 12px; border:1.5px solid var(--gray-2); border-radius:10px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text); outline:none; background:var(--gray);">
        <option value="">Todas las intenciones</option>
        @foreach($intents as $intent)
            <option value="{{ $intent }}" {{ request('intent') === $intent ? 'selected' : '' }}>
                {{ ucfirst($intent) }}
            </option>
        @endforeach
    </select>

    <input
        type="date"
        name="date"
        value="{{ request('date') }}"
        style="padding:8px 12px; border:1.5px solid var(--gray-2); border-radius:10px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text); outline:none; background:var(--gray);"
    >

    <button type="submit" style="padding:8px 18px; background:var(--blue); color:white; border:none; border-radius:10px; font-family:'DM Sans',sans-serif; font-size:13px; cursor:pointer;">
        Filtrar
    </button>

    @if(request('intent') || request('date'))
        <a href="{{ route('admin.conversations') }}" style="padding:8px 18px; background:var(--gray-2); color:var(--text); border-radius:10px; font-size:13px; text-decoration:none;">
            Limpiar
        </a>
    @endif
</form>

    @if($conversations->isEmpty())
        <div class="empty-state">No hay conversaciones registradas aún.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Session ID</th>
                    <th>IP</th>
                    <th>Mensajes</th>
                    <th>Última intención</th>
                    <th>Última actividad</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($conversations as $conv)
                    <tr>
                        <td>{{ $conv->id }}</td>
                        <td><span class="session-code">{{ $conv->session_id }}</span></td>
                        <td>{{ $conv->ip ?? '—' }}</td>
                        <td>
                            <span class="badge badge-blue">{{ $conv->chats_count }} msgs</span>
                        </td>
                        <td>
                            @if($conv->last_intent)
                                <span class="badge {{ $conv->last_intent === 'ai_fallback' ? 'badge-pink' : 'badge-blue' }}">
                                    {{ $conv->last_intent }}
                                </span>
                            @else
                                <span class="badge badge-gray">sin intent</span>
                            @endif
                        </td>
                        <td>{{ $conv->updated_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.conversation.detail', $conv->id) }}" class="btn-detail">
                                Ver →
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrap">
            {{ $conversations->links() }}
        </div>
    @endif
</div>

@endsection