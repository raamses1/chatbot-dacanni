<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dacanni — Acceso</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f4f5f8;
        }

        /* ── LADO IZQUIERDO ── */
        .left {
            width: 45%;
            background: #1B3A8C;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .left::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(232,25,125,0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }

        .left::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
            bottom: -80px;
            left: -80px;
        }

        .left-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: white;
            line-height: 1;
            margin-bottom: 8px;
        }

        .brand-tagline {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 48px;
        }

        .brand-divider {
            width: 40px;
            height: 3px;
            background: #E8197D;
            margin: 0 auto 48px;
            border-radius: 2px;
        }

        .brand-desc {
            font-size: 14px;
            color: rgba(255,255,255,0.6);
            line-height: 1.7;
            max-width: 280px;
        }

        /* ── LADO DERECHO ── */
        .right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #1B3A8C;
            margin-bottom: 6px;
        }

        .login-sub {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 36px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e8eaf0;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: #1a1a2e;
            background: #f9fafb;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }

        input:focus {
            border-color: #1B3A8C;
            background: white;
        }

        .error-msg {
            background: #fce8f3;
            border: 1px solid #E8197D;
            color: #E8197D;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: #E8197D;
            color: white;
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: #ff3d96;
            transform: translateY(-1px);
        }

        .login-footer {
            text-align: center;
            margin-top: 32px;
            font-size: 12px;
            color: #9ca3af;
        }

        @media (max-width: 768px) {
            .left { display: none; }
            .right { padding: 32px 24px; }
        }
    </style>
</head>
<body>

    <!-- LADO IZQUIERDO -->
    <div class="left">
        <div class="left-content">
            <h1 class="brand-name">Dacanni</h1>
            <p class="brand-tagline">Artesanalmente Bello</p>
            <div class="brand-divider"></div>
            <p class="brand-desc">
                Sistema de gestión del chatbot. Accede para ver conversaciones, métricas y estadísticas en tiempo real.
            </p>
        </div>
    </div>

    <!-- LADO DERECHO -->
    <div class="right">
        <div class="login-box">
            <h2 class="login-title">Bienvenido</h2>
            <p class="login-sub">Ingresa tus credenciales para continuar</p>

            @if($errors->has('credentials'))
                <div class="error-msg">
                    {{ $errors->first('credentials') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        placeholder="Tu usuario"
                        autocomplete="off"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Tu contraseña"
                    >
                </div>

                <button type="submit" class="btn-login">Iniciar sesión</button>
            </form>

            <p class="login-footer">Dacanni® Panel Admin v1.0</p>
        </div>
    </div>

</body>
</html>