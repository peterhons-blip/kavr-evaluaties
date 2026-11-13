
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Platformbeheer') | Evaluatieplatform</title>

    <style>
        :root {
            --navy: #142b45;
            --navy-light: #223e5b;
            --teal: #59aaa5;
            --gold: #e5a65b;
            --background: #f2f5f7;
            --text: #142b45;
            --muted: #718096;
            --border: #e1e8ee;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        /* Linkernavigatie */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 260px;
            background: var(--navy);
            color: white;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 30px 24px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }

        .sidebar-brand h2 {
            margin: 0;
            font-size: 23px;
            font-weight: 700;
            letter-spacing: -.5px;
        }

        .sidebar-brand p {
            margin: 8px 0 0;
            color: #9cd5d1;
            font-size: 13px;
        }

        .sidebar-menu {
            padding: 25px 12px;
            flex: 1;
        }

        .menu-label {
            margin: 10px 14px 14px;
            color: #809bb4;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.3px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px 15px;
            margin-bottom: 5px;
            color: #d5e1ec;
            text-decoration: none;
            border-radius: 9px;
            font-size: 14px;
            transition: background .2s, color .2s;
        }

        .nav-item:hover {
            background: var(--navy-light);
            color: white;
        }

        .nav-item.active {
            background: rgba(89,170,165,.18);
            color: #a5ebe3;
            font-weight: 600;
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            font-size: 18px;
        }

        .sidebar-footer {
            padding: 22px;
            border-top: 1px solid rgba(255,255,255,.1);
        }

        .sidebar-footer small {
            color: #809bb4;
        }

        /* Hoofdinhoud */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
        }

        .topbar {
            height: 76px;
            padding: 0 35px;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 700;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 14px;
            font-weight: 700;
        }

        .user-role {
            color: var(--muted);
            font-size: 12px;
            margin-top: 3px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5f3f1;
            color: #286e69;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .content {
            padding: 35px;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Herbruikbare kaarten */
        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 13px;
            padding: 28px;
            box-shadow: 0 5px 20px rgba(20,43,69,.03);
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-header h1 {
            font-size: 27px;
            margin: 0 0 8px;
        }

        .page-header p {
            color: var(--muted);
            margin: 0;
        }

        /* Knoppen */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 17px;
            border: none;
            border-radius: 8px;
            background: var(--navy);
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .btn:hover {
            background: var(--navy-light);
        }

        .btn-secondary {
            background: #e9f3f2;
            color: #286e69;
        }

        .btn-secondary:hover {
            background: #d7ebe8;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .sidebar {
                width: 75px;
            }

            .sidebar-brand {
                padding: 25px 12px;
                text-align: center;
            }

            .sidebar-brand h2 {
                font-size: 14px;
            }

            .sidebar-brand p,
            .menu-label,
            .nav-text,
            .sidebar-footer {
                display: none;
            }

            .sidebar-menu {
                padding: 20px 8px;
            }

            .nav-item {
                justify-content: center;
                padding: 14px 5px;
            }

            .main-wrapper {
                margin-left: 75px;
            }

            .topbar {
                padding: 0 20px;
            }

            .content {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<!-- LINKERNAVIGATIE -->
<aside class="sidebar">

    <div class="sidebar-brand">
        <h2>Evaluatieplatform</h2>
        <p>Platformbeheer</p>
    </div>

    <nav class="sidebar-menu" aria-label="Platformnavigatie">

        <div class="menu-label">ALGEMEEN</div>

        <a href="{{ route('platform.dashboard') }}"
           class="nav-item {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}">
            <span class="nav-icon">▦</span>
            <span class="nav-text">Dashboard</span>
        </a>

        <a href="{{ route('platform.schools.index') }}"
        class="nav-item {{ request()->routeIs('platform.schools.*') ? 'active' : '' }}">
            <span class="nav-icon">🏫</span>
            <span class="nav-text">Scholen</span>
        </a>

        <a href="{{ route('platform.dashboard') }}"
           class="nav-item">
            <span class="nav-icon">♙</span>
            <span class="nav-text">Gebruikers</span>
        </a>

        <div class="menu-label">BEHEER</div>

        <a href="{{ route('platform.dashboard') }}"
           class="nav-item">
            <span class="nav-icon">⚙</span>
            <span class="nav-text">Platforminstellingen</span>
        </a>

    </nav>

    <div class="sidebar-footer">
        <small>Evaluatieplatform &copy; {{ date('Y') }}</small>
    </div>

</aside>

<!-- HOOFDINHOUD -->
<div class="main-wrapper">

    <header class="topbar">

        <div class="topbar-title">
            @yield('page-title', 'Platformbeheer')
        </div>

        <div class="topbar-right">

            <a href="{{ route('schools.select') }}"
               class="btn btn-secondary">
                Wissel omgeving
            </a>

            <div class="user-info">
                <div class="user-name">
                    {{ auth()->user()->full_name }}
                </div>
                <div class="user-role">
                    Platformadministrator
                </div>
            </div>

            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn">
                    Afmelden
                </button>
            </form>

        </div>

    </header>

    <main class="content">
        @yield('content')
    </main>

</div>

</body>
</html>