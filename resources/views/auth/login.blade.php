
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Artelynx | Aanmelden</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('artelynx-favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>
        :root {
            --primary: {{ $school->settings?->primary_color ?? '#142b45' }};
            --secondary: {{ $school->settings?->secondary_color ?? '#59aaa5' }};
            --accent: {{ $school->settings?->accent_color ?? '#e5a65b' }};

            --text: #142b45;
            --muted: #718096;
            --background: #f1f5f8;
            --border: #d5e0e9;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 20px;
            background: var(--background);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ================================
           HOOFDCONTAINER
        ================================= */

        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 1000px;
            min-height: 550px;
            overflow: hidden;
            background: #ffffff;
            border-radius: 22px;
            box-shadow: 0 22px 65px rgba(20, 43, 69, .11);
        }

        /* ================================
           LINKERPANEEL - ARTELYNX
        ================================= */

        .login-panel {
            display: flex;
            flex-direction: column;
            padding: 45px 55px;
            background: #ffffff;
        }

        .platform-brand {
            display: flex;
            align-items: center;
            height: 70px;
            margin-bottom: 25px;
        }

        .platform-brand img {
            display: block;
            width: 185px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }

        .login-content {
            width: 100%;
            max-width: 380px;
        }

        .login-content h1 {
            margin: 0 0 12px;
            color: var(--text);
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.25;
        }

        .login-description {
            margin: 0 0 33px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
        }

        /* ================================
           FORMULIER
        ================================= */

        .form-group {
            margin-bottom: 23px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: var(--text);
            font-size: 13px;
            font-weight: 700;
        }

        .form-control {
            width: 100%;
            height: 49px;
            padding: 0 15px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #f8fafc;
            color: var(--text);
            font: inherit;
            font-size: 13px;
            transition:
                border-color .2s,
                box-shadow .2s,
                background .2s;
        }

        .form-control::placeholder {
            color: #93a4b8;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(89, 170, 165, .13);
        }

        .form-control.is-invalid {
            border-color: #d96c78;
        }

        .field-error {
            margin-top: 7px;
            color: #b84252;
            font-size: 12px;
        }

        .login-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 49px;
            margin-top: 5px;
            border: none;
            border-radius: 9px;
            background: var(--primary);
            color: #ffffff;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition:
                opacity .2s,
                transform .15s,
                box-shadow .2s;
        }

        .login-button:hover {
            opacity: .94;
            box-shadow: 0 7px 18px rgba(20, 43, 69, .15);
        }

        .login-button:active {
            transform: translateY(1px);
        }

        .login-button:focus-visible {
            outline: 3px solid var(--secondary);
            outline-offset: 3px;
        }

        /* ================================
           MELDINGEN
        ================================= */

        .alert {
            margin-bottom: 23px;
            padding: 14px 16px;
            border-radius: 9px;
            font-size: 12px;
            line-height: 1.7;
        }

        .alert-error {
            border: 1px solid #f2c7cc;
            background: #fff3f4;
            color: #a73d4b;
        }

        .alert-success {
            border: 1px solid #b7dfcd;
            background: #effaf4;
            color: #28704f;
        }

        .alert ul {
            margin: 7px 0 0;
            padding-left: 18px;
        }

        /* ================================
           RECHTERPANEEL - SCHOOL
        ================================= */

        .branding-panel {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            padding: 52px 52px 46px;
            background: var(--primary);
            color: #ffffff;
        }

        .school-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 9px;
            min-width: 0;
        }

        /* ================================
           SCHOOLLOGO - DEFINITIEVE CSS
        ================================= */

        .artelynx-school-logo {
            display: flex;
            align-items: center;
            justify-content: flex-start;

            width: fit-content;
            max-width: 100%;
            margin-bottom: 20px;
            padding: 8px 12px;

            background: #ffffff;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .artelynx-school-logo > img {
            display: block;
            width: auto;
            height: auto;
            max-width: 180px;
            max-height: 65px;
            object-fit: contain;
        }

        /* ================================
           SCHOOLNAAM
        ================================= */

        .school-label {
            color: var(--secondary);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            line-height: 1.6;
        }

        .school-name {
            margin: 2px 0 0;
            color: #ffffff;
            font-size: 27px;
            font-weight: 700;
            letter-spacing: -.7px;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .school-decoration {
            width: 45px;
            height: 3px;
            margin-top: 8px;
            background: var(--secondary);
            border-radius: 3px;
        }

        /* ================================
           SLOGAN EN BESCHRIJVING
        ================================= */

        .school-content {
            margin: 36px 0 42px;
        }

        .school-content h2 {
            margin: 0 0 20px;
            color: #ffffff;
            font-size: 35px;
            font-weight: 800;
            letter-spacing: -1.2px;
            line-height: 1.25;
        }

        .school-disciplines {
            margin: 0 0 28px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.7;
        }

        .school-description {
            max-width: 350px;
            margin: 0;
            color: #dce7f1;
            font-size: 14px;
            line-height: 1.9;
        }

        /* ================================
           ACCENTBALKEN
        ================================= */

        .color-bars {
            display: flex;
            gap: 9px;
            width: 100%;
            max-width: 370px;
        }

        .color-bars span {
            flex: 1;
            height: 6px;
            border-radius: 10px;
        }

        .color-bars span:nth-child(1) {
            background: var(--accent);
        }

        .color-bars span:nth-child(2) {
            background: var(--secondary);
        }

        .color-bars span:nth-child(3) {
            background: #c97f9a;
        }

        /* ================================
           TABLET
        ================================= */

        @media (max-width: 850px) {
            .login-panel {
                padding: 42px 35px;
            }

            .branding-panel {
                padding: 45px 35px 40px;
            }

            .school-name {
                font-size: 24px;
            }

            .school-content h2 {
                font-size: 29px;
            }
        }

        /* ================================
           SMARTPHONE
        ================================= */

        @media (max-width: 650px) {
            body {
                align-items: flex-start;
                padding: 18px 12px;
            }

            .login-container {
                grid-template-columns: 1fr;
                min-height: 0;
                border-radius: 17px;
            }

            .login-panel {
                padding: 28px 27px 38px;
            }

            .platform-brand {
                height: auto;
                margin-bottom: 28px;
            }

            .platform-brand img {
                width: 175px;
            }

            .login-content {
                max-width: none;
            }

            .login-content h1 {
                font-size: 26px;
            }

            .login-description {
                margin-bottom: 27px;
            }

            .branding-panel {
                padding: 33px 27px;
            }

            .school-label {
                font-size: 10px;
            }

            .school-name {
                font-size: 23px;
            }

            .school-content {
                margin: 30px 0;
            }

            .school-content h2 {
                font-size: 27px;
                margin-bottom: 15px;
            }

            .school-disciplines {
                margin-bottom: 18px;
            }

            .school-description {
                font-size: 13px;
            }

            .color-bars span {
                height: 5px;
            }

            .artelynx-school-logo > img {
                max-width: 160px;
                max-height: 60px;
            }
        }
    </style>
</head>

<body>

<div class="login-container">

    <!-- =====================================
         LINKERKANT: ARTELYNX + LOGIN
    ====================================== -->

    <section class="login-panel">

        <!-- Officieel Artelynx-logo -->
        <div class="platform-brand">
            @if (file_exists(public_path('images/artelynx-logo.webp')))
                <img src="{{ asset('images/artelynx-logo.webp') }}" alt="Artelynx" width="185">
            @else
                <strong style="font-size:30px;color:#142b45;">Artelynx</strong>
            @endif
        </div>

        <!-- Aanmeldformulier -->
        <div class="login-content">

            <h1>Welkom terug</h1>

            <p class="login-description">
                Meld je aan om verder te gaan.
            </p>

            <!-- Succesmelding -->
            @if (session('status'))
                <div class="alert alert-success" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Foutmeldingen -->
            @if ($errors->any())
                <div class="alert alert-error" role="alert">

                    <strong>Aanmelden is niet gelukt.</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            @endif

            <!-- Formulier -->
            <form method="POST"
                  action="{{ route('login.submit') }}">

                @csrf

                <div class="form-group">

                    <label for="email">
                        E-mailadres
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="Vul uw e-mailadres in"
                        autocomplete="username"
                        maxlength="255"
                        required
                        autofocus>

                </div>

                <div class="form-group">

                    <label for="password">
                        Wachtwoord
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Uw wachtwoord"
                        autocomplete="current-password"
                        required>

                </div>

                <button type="submit" class="login-button">
                    Aanmelden
                </button>

            </form>

        </div>

    </section>

    <!-- =====================================
         RECHTERKANT: SCHOOL
    ====================================== -->

    <section class="branding-panel">

        <!-- Schoolidentiteit -->
        <div class="school-header">

            @if ($school->settings?->logo_path)
                <div class="artelynx-school-logo">
                    <img
                        src="{{ asset('storage/' . $school->settings->logo_path) }}"
                        alt="Logo van {{ $school->name }}">
                </div>
            @endif

            <div class="school-label">
                Aangesloten school
            </div>

            <h2 class="school-name">
                {{ $school->name }}
            </h2>

            <div class="school-decoration"></div>

        </div>

        <!-- Slogan en beschrijving -->
        <div class="school-content">

            <h2>
                {{ $school->settings?->slogan
                    ?? 'Ruimte voor talent.' }}
            </h2>

            @if ($school->settings?->subtitle)
                <p class="school-disciplines">
                    {{ $school->settings->subtitle }}
                </p>
            @endif

            <p class="school-description">
                {{ $school->settings?->login_description
                    ?? 'Welkom bij Artelynx.' }}
            </p>

        </div>

        <!-- Schoolspecificieke accentkleuren -->
        <div class="color-bars" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>

    </section>

</div>

</body>
</html>
