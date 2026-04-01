<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') — Dacanni</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue:       #1B3A8C;
            --blue-dark:  #122870;
            --blue-light: #2248b0;
            --pink:       #E8197D;
            --pink-light: #ff3d96;
            --white:      #FFFFFF;
            --gray:       #f4f5f8;
            --gray-2:     #e8eaf0;
            --gray-3:     #c8ccd8;
            --text:       #1a1a2e;
            --text-soft:  #6b7280;
            --shadow:     0 4px 24px rgba(27,58,140,0.10);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--gray);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 240px;
            background: var(--blue);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: white;
            line-height: 1.2;
        }

        .sidebar-logo span {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            font-weight: 300;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            padding: 20px 12px;
            flex: 1;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 500;
            color: rgba(255,255,255,0.35);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 0 8px;
            margin-bottom: 8px;
            margin-top: 16px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            transition: background 0.18s, color 0.18s;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }

        .nav-item.active {
            background: var(--pink);
            color: white;
        }

        .nav-item svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-footer p {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
        }

        /* ── CONTENIDO ── */
        .main {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--gray-2);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: var(--blue);
        }

        .topbar-badge {
            background: var(--pink);
            color: white;
            font-size: 11px;
            font-weight: 500;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .content {
            padding: 32px;
            flex: 1;
        }
    </style>
    @stack('styles')
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <h1>Dacanni</h1>
        <span>Panel Admin</span>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-label">General</p>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <p class="nav-label">Chatbot</p>

        <a href="{{ route('admin.conversations') }}"
           class="nav-item {{ request()->routeIs('admin.conversations*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            Conversaciones
        </a>
    </nav>

    <div class="sidebar-footer">
    <a href="{{ route('home') }}" style="display:block; color:rgba(255,255,255,0.6); text-decoration:none; font-size:13px; margin-bottom:10px; transition:color 0.2s;" onmouseover="this.style.color='#E8197D'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">
        ← Volver a la tienda
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="background:none; border:1px solid rgba(255,255,255,0.2); color:rgba(255,255,255,0.5); padding:7px 14px; border-radius:8px; cursor:pointer; font-size:12px; width:100%; font-family:'DM Sans',sans-serif; transition:all 0.2s;" onmouseover="this.style.borderColor='#E8197D';this.style.color='#E8197D'" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)';this.style.color='rgba(255,255,255,0.5)'">
            Cerrar sesión
        </button>
    </form>
    <p style="margin-top:10px;">Dacanni® Chatbot v1.0</p>
</div>
</aside>

<main class="main">
    <div class="topbar">
        <h2 class="topbar-title">@yield('page-title', 'Dashboard')</h2>
        <span class="topbar-badge">Admin</span>
    </div>

    <div class="content">
        @yield('content')
    </div>
</main>

@stack('scripts')
</body>
</html>