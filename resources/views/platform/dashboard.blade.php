
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Platformbeheer | Evaluatieplatform</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f2f5f7;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #142b45;
        }

        header {
            background: #142b45;
            color: white;
            padding: 22px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        main {
            max-width: 1150px;
            margin: 45px auto;
            padding: 0 25px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,.05);
        }

        .subtitle {
            color: #718096;
        }

        .tag {
            color: #59aaa5;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 1px;
        }

        button {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            cursor: pointer;
            background: #e5a65b;
            color: #142b45;
            font-weight: 600;
        }
    </style>
</head>

<body>

<header>
    <strong>Evaluatieplatform – Platformbeheer</strong>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Afmelden</button>
    </form>
</header>

<main>
    <div class="card">
        <div class="tag">SUPERADMIN</div>

        <h1>
            Welkom, {{ auth()->user()->first_name }}!
        </h1>

        <p class="subtitle">
            Vanuit deze omgeving beheer je straks alle
            aangesloten scholen, vestigingsplaatsen
            en platforminstellingen.
        </p>

        <p>
            Je bent aangemeld als platformadministrator.
        </p>
    </div>
</main>

</body>
</html>
