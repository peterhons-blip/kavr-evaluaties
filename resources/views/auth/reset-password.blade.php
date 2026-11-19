<!doctype html>
<html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nieuw wachtwoord | Artelynx</title><link rel="icon" type="image/svg+xml" href="{{ asset('artelynx-favicon.svg') }}">
<style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;background:#f0f4f7;font-family:'Segoe UI',sans-serif;color:#142b45}.card{width:100%;max-width:460px;background:#fff;padding:38px;border-radius:18px;box-shadow:0 18px 55px #142b4516}h1{margin:12px 0}p{color:#718096;font-size:14px;line-height:1.7}label{display:block;margin:17px 0 7px;font-weight:700;font-size:13px}input{width:100%;padding:13px;border:1px solid #d5dfe7;border-radius:9px;font:inherit}.btn{width:100%;background:#142b45;color:#fff;border:0;border-radius:9px;padding:13px;margin-top:20px;font-weight:700;cursor:pointer}.error{padding:15px;background:#fff1f2;color:#9f1239;border-radius:9px}</style>
</head><body><main class="card"><strong style="color:#168f90;font-size:21px;">Artelynx</strong><h1>Nieuw wachtwoord</h1>
<p>Kies een uniek wachtwoord van minstens 12 tekens.</p>
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('password.update') }}">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<label for="email">E-mailadres</label><input id="email" type="email" name="email" value="{{ old('email',$email) }}" autocomplete="email" required>
<label for="password">Nieuw wachtwoord</label><input id="password" type="password" name="password" minlength="12" autocomplete="new-password" required>
<label for="password_confirmation">Wachtwoord bevestigen</label><input id="password_confirmation" type="password" name="password_confirmation" minlength="12" autocomplete="new-password" required>
<button class="btn" type="submit">Wachtwoord opslaan</button></form></main></body></html>