<!doctype html>
<html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mijn profiel | Artelynx</title><link rel="icon" href="{{ asset('favicon.ico') }}">
<style>
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#f0f4f7;color:#142b45;margin:0;font-size:14px}
header{background:white;border-bottom:1px solid #dde7ed;padding:18px 28px;display:flex;justify-content:space-between;align-items:center}
header a{color:#286e69;text-decoration:none;font-weight:700}
main{max-width:930px;margin:32px auto;padding:0 18px}
.card{background:white;border:1px solid #dfe8ee;border-radius:13px;padding:28px;margin:19px 0}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
label{display:grid;gap:8px;font-weight:700;font-size:12px}
input{border:1px solid #cfdde5;border-radius:9px;padding:12px;font:inherit;min-width:0}
button{background:#142b45;border:0;border-radius:8px;color:white;padding:12px 18px;cursor:pointer;font-weight:700}
.note{color:#64748b;font-size:12px;line-height:1.8}
.alert{border-radius:9px;padding:15px;margin:15px 0;background:#eaf8ef;color:#166534}
.error{background:#fff1f2;color:#9f1239}
@media(max-width:680px){.grid{grid-template-columns:1fr}}
</style></head><body>
<header><strong>Artelynx · Mijn profiel</strong><a href="{{ url()->previous() === url()->current() ? route('schools.select') : url()->previous() }}">Terug naar Artelynx</a></header>
<main>
<h1>Mijn profiel</h1><p class="note">Je kunt deze gegevens altijd zelf wijzigen, ongeacht je schoolrol. Je schoolrechten en platformrechten kun je hier niet aanpassen.</p>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error"><strong>Controleer je gegevens:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card">
<h2>Persoonlijke gegevens</h2>
<form method="POST" action="{{ route('profile.update') }}">@csrf @method('PUT')
<div class="grid">
<label>Voornaam<input name="first_name" maxlength="100" required value="{{ old('first_name',$user->first_name) }}"></label>
<label>Achternaam<input name="last_name" maxlength="100" required value="{{ old('last_name',$user->last_name) }}"></label>
<label>E-mailadres<input type="email" name="email" maxlength="255" required value="{{ old('email',$user->email) }}"></label>
<label>Telefoonnummer<input name="phone" maxlength="60" value="{{ old('phone',$user->phone) }}"></label>
</div>
<p class="note">Je naam, e-mailadres en telefoonnummer worden bijgewerkt. Bij wijziging van je e-mailadres wordt de verificatiestatus gewist.</p>
<button type="submit">Gegevens opslaan</button>
</form>
</div>
<div class="card">
<h2>Wachtwoord wijzigen</h2>
<p class="note">Voer je huidige wachtwoord in en kies een nieuw wachtwoord van minstens 8 tekens. Andere actieve sessies worden afgemeld. Je ontvangt een bevestigingsmail.</p>
<form method="POST" action="{{ route('profile.password') }}">@csrf @method('PUT')
<div class="grid">
<label>Huidig wachtwoord<input type="password" name="current_password" autocomplete="current-password" required></label><div></div>
<label>Nieuw wachtwoord<input type="password" name="password" autocomplete="new-password" minlength="8" required></label>
<label>Nieuw wachtwoord bevestigen<input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required></label>
</div>
<p><button type="submit">Wachtwoord wijzigen</button></p>
</form>
</div>
</main></body></html>
