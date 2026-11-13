
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>School kiezen | Evaluatieplatform</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f2f5f7;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #142b45;
            padding: 24px;
        }

        .container {
            width: 100%;
            max-width: 560px;
            background: white;
            padding: 45px;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(20,43,69,.10);
        }

        h1 {
            font-size: 28px;
            margin: 0 0 10px;
        }

        .description {
            color: #718096;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .school-button {
            display: block;
            width: 100%;
            padding: 20px;
            margin-bottom: 12px;
            text-align: left;
            background: #f8fafb;
            border: 1px solid #dce4ea;
            border-radius: 10px;
            cursor: pointer;
            color: #142b45;
            font-size: 16px;
            font-weight: 600;
        }

        .school-button:hover {
            border-color: #59aaa5;
            background: #eff8f7;
        }

        .school-code {
            display: block;
            margin-top: 5px;
            color: #718096;
            font-size: 13px;
            font-weight: 400;
        }

        .logout {
            margin-top: 25px;
            text-align: center;
        }

        .logout button {
            border: 0;
            background: transparent;
            color: #718096;
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>Kies je school</h1>

    <p class="description">
        Welkom, {{ auth()->user()->first_name }}.
        Selecteer de school waarvoor je wilt werken.
    </p>

    @foreach ($schools as $school)
        <form method="POST" action="{{ route('schools.select.store') }}">
            @csrf

            <input type="hidden"
                   name="school_id"
                   value="{{ $school->id }}">

            <button type="submit" class="school-button">
                {{ $school->name }}

                <span class="school-code">
                    {{ $school->code }}
                </span>
            </button>
        </form>
    @endforeach

    <form method="POST" action="{{ route('logout') }}" class="logout">
        @csrf
        <button type="submit">Afmelden</button>
    </form>

</div>

</body>
</html>
