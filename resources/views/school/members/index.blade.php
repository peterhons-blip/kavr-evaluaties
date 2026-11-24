@extends('layouts.school')
@section('title','Personeelsleden')
@section('page-title','Personeelsleden')
@section('content')
<div class="page-header">
    <h1>Personeelsleden</h1>
    <p>Gebruikers en schoolrollen van {{ $school->name }}. Wijzigingen gelden uitsluitend voor deze academie.</p>
</div>
@if(session('success'))<div class="sm-alert sm-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sm-alert sm-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

<div class="sm-summary">
    <div class="sm-stat"><span>Personeelsleden</span><strong>{{ $counts['total'] }}</strong></div>
    <div class="sm-stat"><span>Actieve schoolkoppelingen</span><strong>{{ $counts['active'] }}</strong></div>
    <div class="sm-stat"><span>Beschikbare rollen</span><strong>{{ $roles->count() }}</strong></div>
</div>

<div class="card sm-panel">
    <div class="sm-head">
        <div><h2>Personeelsledenoverzicht</h2><p>Zoek en filter op gegevens binnen deze school.</p></div>
        @if($canManage)<a class="btn btn-secondary" href="#school-add-member">+ Personeelslid koppelen</a>@endif
    </div>
    <form method="GET" action="{{ route('school.members.index') }}" class="sm-filter">
        <label class="sm-wide">Zoeken<input name="q" type="search" value="{{ $search }}" placeholder="Naam of e-mailadres"></label>
        <label>Schoolrol<select name="role"><option value="">Alle rollen</option>@foreach($roles as $role)<option value="{{ $role->slug }}" @selected($roleFilter===$role->slug)>{{ $role->name }}</option>@endforeach</select></label>
        <label>Status<select name="status"><option value="all" @selected($status==='all')>Alle statussen</option><option value="active" @selected($status==='active')>Actief</option><option value="inactive" @selected($status==='inactive')>Inactief</option></select></label>
        <button type="submit" class="btn">Filteren</button>
        <a href="{{ route('school.members.index') }}" class="btn btn-secondary">Wissen</a>
    </form>
    <div class="sm-scroll">
      <table class="sm-table">
        <thead><tr><th>Personeelslid</th><th>Rol binnen school</th><th>Schoolstatus</th><th>Accountstatus</th>@if($canManage)<th>Acties</th>@endif</tr></thead>
        <tbody>
        @forelse($members as $member)
          <tr>
            <td><div class="sm-person"><span class="sm-avatar">{{ mb_strtoupper(mb_substr($member->first_name ?: $member->name ?: 'G',0,1)) }}</span><div><strong>{{ $member->full_name ?: $member->name }}</strong><small>{{ $member->email }}</small></div></div></td>
            @if($canManage)
              <td colspan="2">
                <form class="sm-inline" method="POST" action="{{ route('school.members.update',$member) }}" id="sm-edit-{{ $member->id }}">@csrf @method('PUT')
                  <select name="role" aria-label="Schoolrol van {{ $member->full_name ?: $member->name }}">@foreach($roles as $role)<option value="{{ $role->slug }}" @selected($member->pivot->role===$role->slug)>{{ $role->name }}</option>@endforeach</select>
                  <select name="is_active" aria-label="Schoolkoppeling {{ $member->full_name ?: $member->name }}"><option value="1" @selected($member->pivot->is_active)>Actief</option><option value="0" @selected(!$member->pivot->is_active)>Inactief</option></select>
                </form>
              </td>
            @else
              <td>{{ $roles->firstWhere('slug',$member->pivot->role)?->name ?? $member->pivot->role }}</td>
              <td><span class="sm-pill {{ $member->pivot->is_active?'sm-on':'sm-off' }}">{{ $member->pivot->is_active?'Actief':'Inactief' }}</span></td>
            @endif
            <td><span class="sm-pill {{ $member->is_active?'sm-on':'sm-off' }}">{{ $member->is_active?'Actief':'Globaal inactief' }}</span></td>
            @if($canManage)<td><button type="submit" class="btn btn-secondary" form="sm-edit-{{ $member->id }}">Opslaan</button></td>@endif
          </tr>
        @empty<tr><td colspan="{{ $canManage ? 5 : 4 }}" class="sm-empty">Geen personeelsleden gevonden.</td></tr>@endforelse
        </tbody>
      </table>
    </div>
    <div class="sm-pages">{{ $members->links('vendor.pagination.artelynx') }}</div>
</div>

@if($canManage)
<div class="card sm-panel" id="school-add-member">
    <div class="sm-head"><div><h2>Personeelslid toevoegen of koppelen</h2><p>Een bestaand account blijft hetzelfde; alleen de koppeling met {{ $school->name }} wordt toegevoegd.</p></div></div>
    <form method="POST" action="{{ route('school.members.store') }}" class="sm-add">@csrf
        <label>Voornaam <small>(bij nieuw account)</small><input name="first_name" value="{{ old('first_name') }}" maxlength="100" placeholder="Voornaam"></label>
        <label>Familienaam <small>(bij nieuw account)</small><input name="last_name" value="{{ old('last_name') }}" maxlength="100" placeholder="Familienaam"></label>
        <label>E-mailadres<input name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="naam@academie.be"></label>
        <label>Schoolrol<select name="role" required><option value="">Kies een rol</option>@foreach($roles as $role)<option value="{{ $role->slug }}" @selected(old('role')===$role->slug)>{{ $role->name }}</option>@endforeach</select></label>
        <button type="submit" class="btn">Account koppelen</button>
    </form>
    <p class="sm-help">Voor een nieuw account zijn voor- en familienaam verplicht. Er wordt een wachtwoordinstellink verzonden. De globale accountstatus en rechten van andere academies worden hier niet gewijzigd.</p>
</div>
@endif
@endsection
@push('styles')
<style>
.sm-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}
.sm-stat{border:1px solid #e1e8ee;background:#fff;border-radius:12px;padding:19px 21px}
.sm-stat span{font-size:11px;color:#718096;display:block}.sm-stat strong{display:block;font-size:27px;margin-top:5px}
.sm-panel{margin-bottom:22px}.sm-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px;margin-bottom:20px}
.sm-head h2{font-size:18px;margin:0 0 5px}.sm-head p,.sm-help{color:#718096;font-size:12px;line-height:1.7;margin:0}
.sm-filter{display:flex;align-items:end;gap:12px;flex-wrap:wrap;background:#f6fafb;border-radius:10px;padding:16px;margin-bottom:20px;border:1px solid #e6edf0}
.sm-filter label,.sm-add label{display:flex;flex-direction:column;gap:6px;font-size:11px;font-weight:700;color:#50677c;flex:1;min-width:145px}
.sm-filter .sm-wide{flex:2;min-width:210px}.sm-filter input,.sm-filter select,.sm-add input,.sm-add select,.sm-inline select{height:40px;border:1px solid #d1dfe6;border-radius:8px;padding:0 10px;background:#fff;color:#142b45;font:inherit;font-size:12px}
.sm-scroll{overflow-x:auto}.sm-table{border-collapse:collapse;width:100%}.sm-table td,.sm-table th{text-align:left;padding:14px 10px;border-bottom:1px solid #e7edf2;font-size:12px;vertical-align:middle}
.sm-table th{font-size:10px;color:#718096;text-transform:uppercase;white-space:nowrap}.sm-person{display:flex;align-items:center;gap:10px}.sm-person strong{display:block}.sm-person small{display:block;color:#718096;font-size:11px;margin-top:4px}.sm-avatar{display:grid;place-items:center;width:36px;height:36px;flex:0 0 36px;background:#e4f4f1;color:#2b7773;border-radius:50%;font-weight:800}
.sm-inline{display:flex;gap:8px;flex-wrap:wrap}.sm-inline select{min-width:115px}.sm-pill{display:inline-block;border-radius:16px;padding:5px 9px;font-size:10px;font-weight:700;white-space:nowrap}.sm-on{color:#267450;background:#e8f7ef}.sm-off{color:#a64949;background:#fff0f0}.sm-empty{text-align:center;color:#718096;padding:28px!important}.sm-pages{margin-top:16px}
.sm-add{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;align-items:end}.sm-add label{min-width:0}.sm-add button{justify-self:start}.sm-add small{font-weight:400}.sm-help{margin-top:16px}.sm-alert{border-radius:9px;padding:15px;margin-bottom:16px}.sm-success{background:#eaf7ef;color:#267450}.sm-error{background:#fff0f0;color:#a64949}.sm-error p{margin:4px 0}
@media(max-width:850px){.sm-summary{grid-template-columns:1fr}.sm-add{grid-template-columns:1fr}}
</style>
@endpush
