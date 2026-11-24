@extends('layouts.school')
@section('title','Personeelslid bewerken')
@section('page-title','Personeelslid bewerken')
@section('content')
<div class="page-header">
    <h1>Personeelslid bewerken</h1>
    <p>{{ $school->name }} · {{ $member->full_name ?: $member->name }}</p>
</div>
@if(session('success'))<div class="pe-notice pe-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="pe-notice pe-error"><strong>Controleer de gegevens.</strong>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

@if($member->is_platform_admin)
<div class="pe-notice pe-warning">Dit personeelslid is ook platformbeheerder. Persoonsgegevens van dit account kunnen om veiligheidsredenen uitsluitend in Platformbeheer gewijzigd worden. De schoolrol en schoolstatus kunnen wel via het personeelsledenoverzicht worden aangepast.</div>
@endif

<form method="POST" action="{{ route('school.members.details.update', $member) }}" class="pe-container">@csrf @method('PUT')
<div class="card pe-card">
    <h2>Persoons- en contactgegevens</h2>
    <p>Dit zijn de gegevens van het centrale gebruikersaccount. Een medewerker kan ook aan andere academies gekoppeld zijn.</p>
    <div class="pe-grid">
        <label>Voornaam<input name="first_name" value="{{ old('first_name',$member->first_name) }}" required maxlength="100" @disabled($member->is_platform_admin)></label>
        <label>Familienaam<input name="last_name" value="{{ old('last_name',$member->last_name) }}" required maxlength="100" @disabled($member->is_platform_admin)></label>
        <label>E-mailadres<input name="email" type="email" value="{{ old('email',$member->email) }}" required maxlength="255" @disabled($member->is_platform_admin)></label>
        <label>Telefoonnummer<input name="phone" value="{{ old('phone',$member->phone) }}" maxlength="60" @disabled($member->is_platform_admin)></label>
    </div>
    @if($otherSchoolsCount>0)
        <div class="pe-shared"><strong>Gedeeld gebruikersaccount</strong>
            <p>Deze medewerker is aan nog {{ $otherSchoolsCount }} andere {{ $otherSchoolsCount===1 ? 'academie' : 'academies' }} gekoppeld. Aanpassingen aan naam, e-mail en telefoonnummer zijn daar ook zichtbaar. Schoolrol en schoolstatus worden uitsluitend in {{ $school->name }} gewijzigd.</p>
            <label class="pe-check"><input type="checkbox" name="confirm_shared_profile" value="1" @checked(old('confirm_shared_profile'))>
            Ik begrijp dat wijzigingen aan de persoonsgegevens ook bij andere gekoppelde academies zichtbaar worden.</label>
        </div>
    @endif
</div>
<div class="card pe-card">
    <h2>Toegang tot {{ $school->name }}</h2>
    <p>Deze instellingen beïnvloeden uitsluitend de schoolkoppeling van de actieve academie.</p>
    <div class="pe-grid">
        <label>Schoolrol<select name="role" required @disabled($member->is_platform_admin)>
            @foreach($roles as $role)
                <option value="{{ $role->slug }}" @selected(old('role',$member->pivot->role)===$role->slug)>{{ $role->name }}</option>
            @endforeach
        </select></label>
        <label>Schoolstatus<select name="is_active" required @disabled($member->is_platform_admin)>
            <option value="1" @selected((string)old('is_active',(int)$member->pivot->is_active)==='1')>Actief</option>
            <option value="0" @selected((string)old('is_active',(int)$member->pivot->is_active)==='0')>Inactief</option>
        </select></label>
    </div>
    <div class="pe-info">
        <span><strong>Algemene accountstatus:</strong> {{ $member->is_active ? 'Actief' : 'Inactief' }}</span>
        <span><strong>Platformbeheerder:</strong> {{ $member->is_platform_admin ? 'Ja' : 'Nee' }}</span>
        <small>De algemene accountstatus en platformrechten zijn alleen wijzigbaar via Platformbeheer.</small>
    </div>
</div>
<div class="pe-buttons">
    <a class="btn btn-secondary" href="{{ route('school.members.index') }}">Terug naar overzicht</a>
    @unless($member->is_platform_admin)<button class="btn" type="submit">Wijzigingen opslaan</button>@endunless
</div>
</form>

<div class="card pe-card pe-container">
    <h2>Wachtwoord en aanmelding</h2>
    <p>Wachtwoorden worden nooit leesbaar opgeslagen of getoond. Je kunt een veilige instel-/resetlink laten verzenden.</p>
    <div class="pe-password-row">
        <div>
            @if($member->must_change_password)
                <span class="pe-badge pe-wait">Nog in te stellen</span>
            @elseif($member->password_set_at)
                <span class="pe-badge pe-ready">Ingesteld op {{ $member->password_set_at->format('d/m/Y') }}</span>
            @else
                <span class="pe-badge pe-unknown">Status onbekend</span>
            @endif
        </div>
        <form method="POST" action="{{ route('school.members.setup-link',$member) }}" onsubmit="return confirm('Een beveiligde wachtwoordlink versturen naar dit personeelslid?')">
            @csrf
            <button class="btn btn-secondary" type="submit" @disabled(! $member->is_active || ! $member->pivot->is_active)>
                {{ $member->must_change_password ? 'Instellink mailen' : 'Resetlink mailen' }}
            </button>
        </form>
    </div>
</div>
@endsection
@push('styles')
<style>
.pe-container{max-width:1020px}.pe-card{margin-bottom:20px}.pe-card h2{font-size:18px;margin:0 0 8px}.pe-card>p{color:#718096;font-size:12px;line-height:1.7;margin:0 0 23px}
.pe-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.pe-grid label{display:flex;flex-direction:column;gap:7px;font-size:12px;font-weight:700;color:#3e5870}
.pe-grid input,.pe-grid select{height:44px;border:1px solid #d5e1e9;border-radius:9px;padding:0 12px;color:#142b45;background:#fff;font:inherit}
.pe-grid input:disabled,.pe-grid select:disabled{background:#f0f3f5;color:#75859a}
.pe-shared{margin-top:23px;border:1px solid #f0dbad;background:#fff9eb;border-radius:10px;padding:17px;font-size:12px}
.pe-shared p{margin:6px 0 12px;line-height:1.7}.pe-check{display:flex;align-items:start;gap:9px;font-weight:700}
.pe-info{margin-top:23px;display:flex;gap:12px;flex-wrap:wrap;color:#52687d;font-size:12px}.pe-info span{padding:9px 12px;background:#f4f7f9;border:1px solid #e2eaf0;border-radius:8px}.pe-info small{display:block;flex-basis:100%;color:#718096}
.pe-buttons{display:flex;justify-content:flex-end;gap:12px;margin:0 0 22px}
.pe-notice{padding:15px;border-radius:9px;margin-bottom:18px}.pe-success{background:#e8f6ee;color:#267850}.pe-error{background:#fff0f0;color:#a34545}.pe-warning{background:#fff9eb;color:#805c25}
.pe-password-row{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.pe-badge{display:inline-block;border-radius:17px;padding:8px 11px;font-size:11px;font-weight:750}.pe-wait{color:#996326;background:#fff3df}.pe-ready{color:#267850;background:#e8f6ee}.pe-unknown{color:#657689;background:#eff2f6}
@media(max-width:680px){.pe-grid{grid-template-columns:1fr}}
</style>
@endpush
