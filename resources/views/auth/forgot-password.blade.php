<!doctype html>
<html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Wachtwoord herstellen | Artelynx</title><link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=3">
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;background:#f0f4f7;font-family:'Segoe UI',sans-serif;color:#142b45}.card{width:100%;max-width:460px;background:#fff;padding:38px;border-radius:18px;box-shadow:0 18px 55px #142b4516}h1{font-size:27px;margin:12px 0}p{color:#718096;font-size:14px;line-height:1.7}label{display:block;font-weight:700;margin:24px 0 9px;font-size:13px}input{width:100%;padding:13px;border:1px solid #d5dfe7;border-radius:9px;font:inherit}.btn{width:100%;background:#142b45;color:#fff;border:0;border-radius:9px;padding:13px;margin-top:20px;font-weight:700;cursor:pointer}.link{display:block;margin-top:20px;color:#287f79;text-decoration:none}.alert{padding:15px;background:#edf8f3;color:#246b52;border-radius:9px;margin:15px 0}.error{padding:15px;background:#fff1f2;color:#9f1239;border-radius:9px}
</style></head><body><main class="card">
<strong style="color:#168f90;font-size:21px;">Artelynx</strong>
<h1>Wachtwoord instellen of herstellen</h1>
<p>Vul je e-mailadres in. Als je een Artelynx-account hebt, ontvang je een beveiligde link waarmee je een wachtwoord kunt instellen. De link is 24 uur geldig en kan maar één keer worden gebruikt.</p>
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf
<label for="email">E-mailadres</label><input id="email" type="email" name="email" required autocomplete="email" value="{{ old('email') }}">
<button type="submit" class="btn">Verstuur instellink</button>
</form><a class="link" href="{{ route('login') }}">Terug naar aanmelden</a></main></body></html>