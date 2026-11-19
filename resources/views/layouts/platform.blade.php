
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Platformbeheer') | Artelynx</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=3">

    <link rel="preconnect"
          href="https://fonts.googleapis.com">
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>
        :root {
            --navy: #142b45;
            --navy-light: #223e5b;
            --teal: #59aaa5;
            --gold: #e5a65b;
            --pink: #c97f9a;

            --background: #f0f4f7;
            --text: #142b45;
            --muted: #718096;
            --border: #e1e8ee;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--background);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
        }

        /* ========================================
           LINKERNAVIGATIE
        ======================================== */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            display: flex;
            flex-direction: column;
            background: var(--navy);
            border-right: 1px solid var(--navy-light);
            z-index: 100;
        }

        /* Wit gedeelte met Artelynx-logo */

        .sidebar-brand {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 128px;
            padding: 25px 24px;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-brand a {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            max-width: 100%;
            text-decoration: none;
        }

        .brand-logo {
            display: block;
            width: 190px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }

        .brand-subtitle {
            margin-top: 10px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .3px;
        }

        /* Menu */

        .sidebar-menu {
            flex: 1;
            padding: 25px 12px;
            overflow-y: auto;
        }

        .menu-label {
            margin: 15px 14px 12px;
            color: #839eb5;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.2px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 13px;
            width: 100%;
            padding: 13px 15px;
            margin-bottom: 5px;
            color: #d5e1ec;
            text-decoration: none;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            transition:
                background .2s ease,
                color .2s ease;
        }

        .nav-item:hover {
            background: var(--navy-light);
            color: #ffffff;
        }

        .nav-item.active {
            background: rgba(89, 170, 165, .22);
            color: #a5ebe3;
        }

        .nav-item.active .nav-icon {
            color: #a5ebe3;
        }

        .nav-item.disabled {
            color: #718ca4;
            cursor: default;
            opacity: .7;
        }

        .nav-item.disabled:hover {
            background: transparent;
            color: #718ca4;
        }

        .nav-icon {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 22px;
            flex-shrink: 0;
            font-size: 18px;
        }

        .nav-text {
            min-width: 0;
        }

        /* Footer linkernavigatie */

        .sidebar-footer {
            padding: 20px 25px;
            border-top: 1px solid rgba(255, 255, 255, .1);
            color: #839eb5;
            font-size: 11px;
        }

        /* ========================================
           HOOFDGEDEELTE
        ======================================== */

        .main-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 250px;
        }

        /* ========================================
           BOVENBALK
        ======================================== */

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            min-height: 76px;
            padding: 15px 35px;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
        }

        .topbar-heading {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .topbar-title {
            color: var(--navy);
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -.4px;
        }

        .topbar-subtitle {
            color: var(--muted);
            font-size: 11px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            color: var(--navy);
            font-size: 13px;
            font-weight: 700;
        }

        .user-role {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #e5f3f1;
            color: #286e69;
            font-weight: 800;
        }

        /* ========================================
           PAGINA-INHOUD
        ======================================== */

        .content {
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 35px;
            flex: 1;
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-header h1 {
            margin: 0 0 9px;
            color: var(--navy);
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -.8px;
        }

        .page-header p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.8;
        }

        /* ========================================
           KAARTEN
        ======================================== */

        .card {
            padding: 28px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 13px;
            box-shadow: 0 5px 20px rgba(20, 43, 69, .035);
        }

        .card h2 {
            color: var(--navy);
        }

        /* ========================================
           KNOPPEN
        ======================================== */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 17px;
            background: var(--navy);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            transition:
                background .2s ease,
                transform .15s ease;
        }

        .btn:hover {
            background: var(--navy-light);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn:focus-visible,
        .nav-item:focus-visible {
            outline: 3px solid var(--teal);
            outline-offset: 3px;
        }

        .btn-secondary {
            background: #e9f3f2;
            color: #286e69;
        }

        .btn-secondary:hover {
            background: #d7ebe8;
        }

        /* ========================================
           FOOTER HOOFDGEDEELTE
        ======================================== */

        .main-footer {
            padding: 20px 35px;
            border-top: 1px solid var(--border);
            color: #98a6b7;
            font-size: 11px;
            text-align: center;
        }

        /* ========================================
           TABLET
        ======================================== */

        @media (max-width: 1000px) {

            .sidebar {
                width: 78px;
            }

            .sidebar-brand {
                min-height: 100px;
                align-items: center;
                padding: 20px 10px;
            }

            .brand-logo {
                width: 58px;
            }

            .brand-subtitle,
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
                margin-left: 78px;
            }

            .topbar {
                padding: 15px 20px;
            }

            .content {
                padding: 25px;
            }
        }

        /* ========================================
           SMARTPHONE
        ======================================== */

        @media (max-width: 650px) {

            .sidebar {
                position: static;
                width: 100%;
                flex-direction: row;
                align-items: center;
                border-right: none;
                border-bottom: 1px solid var(--navy-light);
            }

            .sidebar-brand {
                min-height: 0;
                padding: 12px 15px;
                border-bottom: none;
                flex-shrink: 0;
            }

            .brand-logo {
                width: 105px;
            }

            .sidebar-menu {
                display: flex;
                justify-content: flex-end;
                gap: 2px;
                padding: 8px;
                min-width: 0;
            }

            .nav-item {
                width: auto;
                margin: 0;
                padding: 10px;
            }

            .nav-icon {
                font-size: 17px;
            }

            .main-wrapper {
                min-height: calc(100vh - 70px);
                margin-left: 0;
            }

            .topbar {
                flex-wrap: wrap;
                align-items: flex-start;
                padding: 17px 20px;
            }

            .topbar-right {
                flex-wrap: wrap;
                gap: 10px;
            }

            .user-info,
            .avatar {
                display: none;
            }

            .content {
                padding: 22px 15px;
            }

            .page-header h1 {
                font-size: 23px;
            }

            .card {
                padding: 20px;
            }

            .main-footer {
                padding: 18px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

<!-- ======================================
     LINKERNAVIGATIE
====================================== -->

<aside class="sidebar">

    <!-- WIT LOGOGEDEELTE -->
    <div class="sidebar-brand">

        <a href="{{ route('platform.dashboard') }}"
           aria-label="Artelynx – Platformbeheer">

            @if (file_exists(public_path('images/artelynx-logo.webp')))
                <img src="{{ asset('images/artelynx-logo.webp') }}" alt="Artelynx" class="brand-logo">
            @else
                <strong style="font-size:24px;color:#142b45;">Artelynx</strong>
            @endif

        </a>

    </div>

    <!-- DONKERBLAUW MENU -->
    <nav class="sidebar-menu"
         aria-label="Platformnavigatie">

        <div class="menu-label">ALGEMEEN</div>

        <!-- Dashboard -->
        <a href="{{ route('platform.dashboard') }}"
           class="nav-item {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}"
           @if (request()->routeIs('platform.dashboard'))
               aria-current="page"
           @endif>

            <span class="nav-icon">▦</span>
            <span class="nav-text">Dashboard</span>

        </a>

        <!-- Scholen -->
        <a href="{{ route('platform.schools.index') }}"
           class="nav-item {{ request()->routeIs('platform.schools.*') ? 'active' : '' }}"
           @if (request()->routeIs('platform.schools.*'))
               aria-current="page"
           @endif>

            <span class="nav-icon">🏫</span>
            <span class="nav-text">Scholen</span>

        </a>

        <!-- Gebruikersbeheer -->
        <a href="{{ route('platform.users.index') }}"
           class="nav-item {{ request()->routeIs('platform.users.*') ? 'active' : '' }}">
            <span class="nav-icon">♙</span>
            <span class="nav-text">Personeelsleden</span>
        </a>

        <div class="menu-label">BEHEER</div>

        <a href="{{ route('platform.audit.index') }}"
           class="nav-item {{ request()->routeIs('platform.audit.*') ? 'active' : '' }}">
            <span class="nav-icon">☷</span>
            <span class="nav-text">Beheerhistoriek</span>
        </a>

        <!-- Platforminstellingen: later -->
        <span class="nav-item disabled"
              title="Binnenkort beschikbaar">

            <span class="nav-icon">⚙</span>
            <span class="nav-text">Platforminstellingen</span>

        </span>

    </nav>

    <!-- SIDEBAR FOOTER -->
    <div class="sidebar-footer">
        Artelynx &copy; {{ date('Y') }}
    </div>

</aside>

<!-- ======================================
     HOOFDGEDEELTE
====================================== -->

<div class="main-wrapper">

    <!-- BOVENBALK -->
    <header class="topbar">

        <div class="topbar-heading">

            <div class="topbar-title">
                @yield('page-title', 'Platformbeheer')
            </div>

            <div class="topbar-subtitle">
                Artelynx / Platformbeheer
            </div>

        </div>

        <div class="topbar-right">

            <!-- OMGEVING WISSELEN -->
            <a href="{{ route('schools.select') }}"
               class="btn btn-secondary">
                Wissel omgeving
            </a>

            <!-- GEBRUIKER -->
            <div class="user-info">

                <div class="user-name">
                    {{ auth()->user()->full_name }}
                </div>

                <div class="user-role">
                    Platformadministrator
                </div>

            </div>

            <!-- AVATAR -->
            <div class="avatar">
                {{ strtoupper(mb_substr(
                    auth()->user()->first_name ?? 'U',
                    0,
                    1
                )) }}
            </div>

            <!-- AFMELDEN -->
            <form method="POST"
                  action="{{ route('logout') }}">

                @csrf

                <button type="submit" class="btn">
                    Afmelden
                </button>

            </form>

        </div>

    </header>

    <!-- PAGINA-INHOUD -->
    <main class="content">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="main-footer">
        Artelynx · Elk talent in ontwikkeling.
    </footer>

</div>
@stack('scripts')
</body>
</html>
