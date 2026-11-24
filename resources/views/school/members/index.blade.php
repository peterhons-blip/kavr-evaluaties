@extends('layouts.school')
@section('title','Personeelsleden')
@section('page-title','Personeelsleden')
@section('content')
<div class="wrap">
<div class="header"><div><h1>Personeelsleden — {{ $school->name }}</h1><p class="muted">Beheer uitsluitend personeelslidskoppelingen van deze school.</p></div></div>
@if(session('success'))<div class="success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@if($canManage)
<div class="card"><h2>Personeelslid toevoegen of koppelen</h2>
<form method="POST" action="{{ route('school.members.store') }}" class="inline">@csrf
<input name="first_name" type="text" placeholder="Voornaam (nieuw account)" style="min-width:150px">
<input name="last_name" type="text" placeholder="Achternaam (nieuw account)" style="min-width:150px">
<input name="email" type="email" placeholder="E-mailadres" required style="min-width:220px">
<select name="role" required><option value="">Kies een rol</option>@foreach($roles as $role)<option value="{{ $role->slug }}">{{ $role->name }}</option>@endforeach</select>
<button class="btn" type="submit">Koppelen</button></form>
<p class="muted">Bestaat het e-mailadres al, dan wordt het account gekoppeld en blijven de bestaande persoonsgegevens behouden. Voor een nieuw account vul je ook voornaam en achternaam in. Er wordt automatisch een beveiligde wachtwoordinstellink verstuurd.</p>
</div>
@endif
<div class="card"><h2>Personeelsleden</h2><div style="overflow-x:auto"><table>
<thead><tr><th>Gebruiker</th><th>Rol</th><th>Status</th><th>Actie</th></tr></thead><tbody>
@forelse($members as $member)
<tr><td><strong>{{ $member->full_name ?: $member->name }}</strong><small>{{ $member->email }}</small></td>
<td colspan="3">
@if($canManage)
<form method="POST" action="{{ route('school.members.update',$member) }}" class="inline">@csrf @method('PUT')
<select name="role">@foreach($roles as $role)<option value="{{ $role->slug }}" @selected($member->pivot->role === $role->slug)>{{ $role->name }}</option>@endforeach</select>
<select name="is_active"><option value="1" @selected($member->pivot->is_active)>Actief</option><option value="0" @selected(!$member->pivot->is_active)>Inactief</option></select>
<button class="btn" type="submit">Opslaan</button>
</form>
@else {{ $member->pivot->role }} — {{ $member->pivot->is_active ? 'Actief' : 'Inactief' }}
@endif
</td></tr>
@empty<tr><td colspan="4">Geen personeelsleden gekoppeld.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:18px">{{ $members->links() }}</div></div>
</div>
@endsection
@push('styles')
<style>

body{font-family:system-ui,sans-serif;margin:0;background:#f0f4f7;color:#142b45}.wrap{max-width:1100px;margin:35px auto;padding:0 20px}.header{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap}.card{background:#fff;border:1px solid #e1e8ee;border-radius:13px;padding:24px;margin:20px 0}table{width:100%;border-collapse:collapse}th,td{border-bottom:1px solid #e1e8ee;padding:14px;text-align:left}small{display:block;color:#718096}.btn{background:#142b45;color:white;border:0;padding:11px 15px;border-radius:8px;cursor:pointer;text-decoration:none}input,select{padding:10px;border:1px solid #d5dfe7;border-radius:8px}form.inline{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.success{background:#edf8f3;color:#246b52;padding:14px;border-radius:9px}.error{background:#fff1f2;color:#9f1239;padding:14px;border-radius:9px}.muted{color:#718096}

.wrap{max-width:1120px;margin:0 auto;padding:0} .wrap>.header,.wrap>.head{margin-bottom:20px} .wrap h1{font-size:25px;line-height:1.3;margin:0 0 8px} .wrap>p{color:#718096;font-size:12px} .wrap .card{margin-top:19px} .wrap table{width:100%} .wrap .btn{font-family:inherit;font-weight:700} .wrap .btn.alt{background:#e9f3f2;color:#286e69} .wrap .error{border-radius:9px;margin:12px 0} .wrap .success{border-radius:9px;margin:12px 0}
</style>
@endpush
