
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Dashboard | Artelynx</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('artelynx-favicon.svg') }}">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f2f5f7;
            color: #142b45;
        }

        header {
            background: #142b45;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
        }

        main {
            max-width: 1100px;
            margin: 50px auto;
            padding: 0 25px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 35px;
            box-shadow: 0 8px 30px rgba(0,0,0,.05);
        }

        .subtitle {
            color: #718096;
            line-height: 1.7;
        }

        button {
            background: #e5a65b;
            color: #142b45;
            border: 0;
            padding: 11px 18px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            header {
                padding: 20px;
            }

            main {
                margin: 25px auto;
            }
        }
    </style>
</head>
<body>


<header>
    <div class="logo">@if (file_exists(public_path('images/artelynx-logo.webp')))
                <img src="{{ asset('images/artelynx-logo.webp') }}" alt="Artelynx" style="display:block;width:185px;max-width:100%;height:auto;background:white;padding:6px 12px;border-radius:9px">
            @else
                <span>Artelynx</span>
            @endif</div>

    <div style="display:flex; gap:15px; align-items:center;">

        <a href="{{ route('schools.select') }}"
           style="color:white; text-decoration:none;">
            Wissel omgeving
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Afmelden</button>
        </form>

    </div>
</header>


<main>
    <div class="card">
        <h1>Welkom, {{ Auth::user()->name }}!</h1>

        <p class="subtitle">
            Je bent succesvol aangemeld bij het
            evaluatieplatform van Kunstacademie Voeren-Riemst.
        </p>

        <p>
            Binnenkort vind je hier jouw leerlingen,
            lesgroepen en evaluaties.
        </p>
    </div>
</main>

</body>
</html>
