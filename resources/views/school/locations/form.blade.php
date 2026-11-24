@extends('layouts.school-students')
@section('title', $location->exists ? 'Vestiging bewerken' : 'Vestiging toevoegen')
@section('page-title','Vestigingsplaatsen')
@section('content')
<div class="page-header"><h1>{{ $location->exists ? 'Vestigingsplaats bewerken' : 'Vestigingsplaats toevoegen' }}</h1><p>{{ $school->name }}</p></div>
@if($errors->any())<div class="card" style="color:#a33b3b;margin-bottom:16px">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card" style="max-width:920px"><form method="POST" action="{{ $location->exists ? route('school.locations.update',$location) : route('school.locations.store') }}">@csrf @if($location->exists)@method('PUT')@endif
<div class="lf-grid">
@foreach(['name'=>'Naam','location_number'=>'Vestigingsnummer','street'=>'Straat en huisnummer','postal_code'=>'Postcode','city'=>'Gemeente','email'=>'E-mailadres','phone'=>'Telefoonnummer'] as $key=>$label)
<label>{{ $label }}<input type="{{ $key==='email' ? 'email' : 'text' }}" name="{{ $key }}" value="{{ old($key,$location->{$key}) }}" @if($key==='name')required @endif maxlength="255"></label>
@endforeach
<label>Hoofdvestiging<select name="is_main_location"><option value="0" @selected(!old('is_main_location',$location->is_main_location))>Nee</option><option value="1" @selected(old('is_main_location',$location->is_main_location))>Ja</option></select></label>
<label>Status<select name="is_active"><option value="1" @selected(old('is_active',$location->exists ? $location->is_active : 1))>Actief</option><option value="0" @selected(!old('is_active',$location->exists ? $location->is_active : 1))>Inactief</option></select></label>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px"><a class="btn btn-secondary" href="{{ route('school.locations.index') }}">Annuleren</a><button class="btn" type="submit">Opslaan</button></div>
</form></div>
@endsection
@push('styles')<style>.lf-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.lf-grid label{display:grid;gap:7px;font-size:12px;font-weight:700}.lf-grid input,.lf-grid select{padding:11px;border:1px solid #d4dfe8;border-radius:8px;font:inherit}@media(max-width:650px){.lf-grid{grid-template-columns:1fr}}</style>@endpush
