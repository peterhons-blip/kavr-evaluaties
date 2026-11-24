@extends('layouts.school-students')
@section('title','Schoolinstellingen')
@section('page-title','Schoolinstellingen')
@section('content')
<div class="page-header"><h1>Schoolinstellingen</h1><p>{{ $school->name }} · Pas alleen de eigen academie aan.</p></div>
@if(session('success'))<div class="card" style="color:#26734e;margin-bottom:15px">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" style="color:#a33b3b;margin-bottom:15px">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card" style="max-width:900px">
<form method="POST" action="{{ route('school.settings.update') }}">@csrf @method('PUT')
<div class="ss-grid">
<label>Slogan<input name="slogan" maxlength="255" value="{{ old('slogan', $school->settings?->slogan) }}"></label>
<label>Ondertitel<input name="subtitle" maxlength="255" value="{{ old('subtitle', $school->settings?->subtitle) }}"></label>
<label class="ss-wide">Beschrijving op de loginpagina<textarea name="login_description" rows="4" maxlength="2000">{{ old('login_description', $school->settings?->login_description) }}</textarea></label>
@foreach(['primary_color' => ['Primaire kleur','#142B45'], 'secondary_color' => ['Secundaire kleur','#59AAA5'], 'accent_color' => ['Accentkleur','#E5A65B']] as $key => $info)
<label>{{ $info[0] }}<input name="{{ $key }}" value="{{ old($key, $school->settings?->{$key} ?? $info[1]) }}" pattern="#[0-9a-fA-F]{6}" required></label>
@endforeach
<label>Taal<select name="language">@foreach(['nl'=>'Nederlands','fr'=>'Frans','en'=>'Engels','de'=>'Duits'] as $key => $name)<option value="{{ $key }}" @selected(old('language',$school->settings?->language ?? 'nl')===$key)>{{ $name }}</option>@endforeach</select></label>
<label>Tijdzone<select name="timezone">@foreach(['Europe/Brussels','Europe/Amsterdam','Europe/Paris','Europe/Berlin','UTC'] as $timezone)<option value="{{ $timezone }}" @selected(old('timezone',$school->settings?->timezone ?? 'Europe/Brussels')===$timezone)>{{ $timezone }}</option>@endforeach</select></label>
</div>
<p style="color:#718096;font-size:12px">Schoollogo en platformbrede instellingen blijven beschikbaar via Platformbeheer. Wijzigingen hier gelden uitsluitend voor {{ $school->name }}.</p>
<div style="display:flex;gap:10px;justify-content:flex-end"><a class="btn btn-secondary" href="{{ route('dashboard') }}">Annuleren</a><button class="btn" type="submit">Opslaan</button></div>
</form></div>
@endsection
@push('styles')<style>.ss-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-bottom:22px}.ss-grid label{display:grid;gap:7px;font-size:12px;font-weight:700}.ss-wide{grid-column:1/-1}.ss-grid input,.ss-grid textarea,.ss-grid select{border:1px solid #d4e0e7;border-radius:8px;padding:12px;background:#fff;font:inherit}@media(max-width:700px){.ss-grid{grid-template-columns:1fr}.ss-wide{grid-column:auto}}</style>@endpush
