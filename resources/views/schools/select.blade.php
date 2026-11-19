
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Omgeving kiezen | Artelynx</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <style>
        :root {
            --navy: #142b45;
            --teal: #59aaa5;
            --muted: #718096;
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
            background: #f2f5f7;
            color: var(--navy);
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .container {
            width: 100%;
            max-width: 620px;
            padding: 42px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(20,43,69,.10);
        }

        .eyebrow {
            color: #408984;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 30px;
            letter-spacing: -.6px;
        }

        .description {
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .section-label {
            margin: 25px 0 12px;
            color: #718096;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .option {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
            padding: 18px;
            border: 1px solid #dce4ea;
            border-radius: 11px;
            background: #f8fafb;
            color: var(--navy);
            text-align: left;
            text-decoration: none;
            cursor: pointer;
            margin-bottom: 12px;
            font: inherit;
            transition: .2s;
        }

        .option:hover {
            border-color: var(--teal);
            background: #eff8f7;
        }

        .option-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            border-radius: 10px;
            background: #e7eff2;
            font-size: 24px;
        }

        .option-text {
            flex: 1;
            min-width: 0;
        }

        .option-title {
            display: block;
            font-size: 16px;
            font-weight: 700;
        }

        .option-description {
            display: block;
            margin-top: 5px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .arrow {
            font-size: 23px;
            color: #9aa6b2;
        }

        .logout {
            margin-top: 30px;
            text-align: center;
        }

        .logout button {
            border: none;
            background: transparent;
            color: var(--muted);
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }

            h1 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="eyebrow">
        @if (file_exists(public_path('images/artelynx-logo.webp')))
            <img src="{{ asset('images/artelynx-logo.webp') }}" alt="Artelynx" style="display:block;width:210px;max-width:100%;height:auto;margin-bottom:10px;">
        @else
            Artelynx
        @endif
    </div>

    <h1>
        Welkom, {{ auth()->user()->first_name }}!
    </h1>

    <p class="description">
        Kies de omgeving waarin je wilt werken.
    </p>

    @if ($isPlatformAdmin)

        <div class="section-label">Platform</div>

        <a href="{{ route('platform.dashboard') }}"
           class="option">

            <span class="option-icon">⚙️</span>

            <span class="option-text">
                <span class="option-title">
                    Platformbeheer
                </span>

                <span class="option-description">
                    Scholen, gebruikers en
                    platforminstellingen beheren
                </span>
            </span>

            <span class="arrow">&rsaquo;</span>
        </a>

    @endif

    @if ($schools->isNotEmpty())

        <div class="section-label">Mijn scholen</div>

        @foreach ($schools as $school)

            <form method="POST"
                  action="{{ route('schools.select.store') }}">
                @csrf

                <input type="hidden"
                       name="school_id"
                       value="{{ $school->id }}">

                <button type="submit" class="option">

                    <span class="option-icon">🏫</span>

                    <span class="option-text">

                        <span class="option-title">
                            {{ $school->name }}
                        </span>

                        <span class="option-description">
                            {{ ucfirst(str_replace(
                                '_',
                                ' ',
                                $school->pivot->role
                            )) }}
                        </span>

                    </span>

                    <span class="arrow">&rsaquo;</span>

                </button>
            </form>

        @endforeach

    @endif

    <form method="POST"
          action="{{ route('logout') }}"
          class="logout">
        @csrf

        <button type="submit">Afmelden</button>
    </form>

</div>

</body>
</html>
