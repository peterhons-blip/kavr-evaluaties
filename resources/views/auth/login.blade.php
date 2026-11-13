
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Evaluatieplatform | Kunstacademie Voeren-Riemst</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy: #142b45;
            --teal: #59aaa5;
            --gold: #e5a65b;
            --pink: #c97f9a;
            --muted: #718096;
            --background: #f2f5f7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--background);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--navy);
        }

        /* Hoofdcontainer */
        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 960px;
            min-height: 530px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(20, 43, 69, .10);
        }

        /* Linkerkant */
        .branding {
            background: var(--navy);
            color: white;
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 35px;
        }

        .brand-name {
            letter-spacing: normal;
        }

        .platform-title {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: -1.2px;
            line-height: 1.3;
            color: #ffffff;
        }


        .academy-subtitle {
            margin-top: 8px;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            font-size: 15px;
            font-weight: 400;
            letter-spacing: 0.1px;
            line-height: 1.6;
            color: #9cd5d1;
        }


        .branding h1 {
            font-size: 32px;
            line-height: 1.25;
            margin: 0 0 20px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }


        .branding p {
            color: #c9d7e5;
            line-height: 1.7;
            font-size: 16px;
        }

        .branding .disciplines {
            font-size: 17px;
            margin-bottom: 25px;
        }

        .color-bars {
            display: flex;
            gap: 8px;
        }

        .color-bars span {
            height: 7px;
            flex: 1;
            border-radius: 10px;
        }

        .color-bars span:nth-child(1) {
            background: var(--gold);
        }

        .color-bars span:nth-child(2) {
            background: var(--teal);
        }

        .color-bars span:nth-child(3) {
            background: var(--pink);
        }

        /* Rechterkant */
        .login-form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 65px 55px;
        }

        .login-form h2 {
            margin: 0 0 12px;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .description {
            color: var(--muted);
            margin-bottom: 35px;
            line-height: 1.6;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #d7dfe7;
            border-radius: 9px;
            font-size: 15px;
            margin-bottom: 22px;
            outline: none;
            background: #f9fafb;
            color: var(--navy);
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(89, 170, 165, .12);
        }

        input::placeholder {
            color: #8a98a7;
        }

        button {
            width: 100%;
            background: var(--navy);
            color: white;
            border: none;
            border-radius: 9px;
            padding: 15px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: background .2s, transform .2s;
        }

        button:hover {
            background: #254668;
        }

        button:active {
            transform: scale(.99);
        }

        button:focus-visible {
            outline: 3px solid var(--teal);
            outline-offset: 3px;
        }

        /* Foutmeldingen */
        .error {
            padding: 12px 15px;
            color: #9c2e35;
            background: #fff1f2;
            border: 1px solid #f5c2c7;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            color: #9aa6b2;
            font-size: 12px;
            margin-top: 35px;
        }

        /* Tablets */
        @media (max-width: 850px) {
            .branding {
                padding: 35px;
            }

            .login-form {
                padding: 45px 35px;
            }

            .branding h1 {
                font-size: 36px;
            }
        }

        /* Smartphones */
        @media (max-width: 700px) {
            body {
                padding: 14px;
            }

            .login-container {
                grid-template-columns: 1fr;
            }

            .branding {
                padding: 30px;
                gap: 25px;
            }

            .platform-title {
                font-size: 29px;
            }

            .branding h1 {
                font-size: 26px;
            }

            .academy-subtitle {
                font-size: 14px;
            }

            .login-form {
                padding: 35px 30px;
            }

            .login-form h2 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<div class="login-container">

    <!-- LINKERKANT: HUISSTIJL -->
    <section class="branding">

        <!-- Naam platform -->
        <div class="brand-name">
            <div class="platform-title">
                Evaluatieplatform BLA
            </div>

            <div class="academy-subtitle">
                Kunstacademie Voeren-Riemst
            </div>
        </div>

        <!-- Slogan -->
        <div>
            <h1>Ruimte voor talent.</h1>

            <p class="disciplines">
                Beeld. Muziek. Theater.
            </p>

            <p>
                Eén plek voor alle evaluaties,
                leerdoelen en leerlingengroei.
            </p>
        </div>

        <!-- Accentkleuren -->
        <div class="color-bars" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>

    </section>

    <!-- RECHTERKANT: AANMELDEN -->
    <section class="login-form">

        <h2>Welkom terug</h2>

        <p class="description">
            Meld je aan om verder te gaan.
        </p>

        <!-- Foutmelding -->
        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Loginformulier -->
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <label for="email">E-mailadres</label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Vul uw e-mailadres in"
                autocomplete="username"
                required
                autofocus
            >

            <label for="password">Wachtwoord</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Uw wachtwoord"
                autocomplete="current-password"
                required
            >

            <button type="submit">
                Aanmelden
            </button>
        </form>

        <div class="footer">
            Kunstacademie Voeren-Riemst
            &copy; {{ date('Y') }}
        </div>

    </section>

</div>

</body>
</html>
