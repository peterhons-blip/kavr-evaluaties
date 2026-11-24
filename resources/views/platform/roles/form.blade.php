@extends('layouts.platform')
@section('title', $role->exists ? 'Schoolrol bewerken' : 'Schoolrol toevoegen')
@section('page-title','Schoolrollen en rechten')
@section('content')
<div class="page-header"><h1>{{ $role->exists ? 'Rol en rechten bewerken' : 'Nieuwe schoolrol' }}</h1><p>{{ $school->name }} — elke wijziging geldt uitsluitend voor deze school.</p></div>
@if($errors->any())<div class="rf-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ $role->exists ? route('platform.schools.roles.update',[$school,$role]) : route('platform.schools.roles.store',$school) }}">
@csrf @if($role->exists) @method('PUT') @endif
<div class="card rf-card">
    <div class="rf-grid">
        <label>Naam *<input required maxlength="100" name="name" value="{{ old('name',$role->name) }}"></label>
        <label>Technische rolcode *<input required maxlength="30" name="slug" value="{{ old('slug',$role->slug) }}" @readonly($role->is_system)></label>
        <label class="rf-full">Beschrijving<textarea name="description" maxlength="1000" rows="2">{{ old('description',$role->description) }}</textarea></label>
    </div>
    <h2>Toegestane functies</h2>
    <p class="rf-explain">Een vinkje geeft de rol toegang tot die functie. Bij leerlinggegevens blijven aanvullende controles op toegewezen lessen en groepen noodzakelijk.</p>
    @php $selected = old('permissions', $role->permissions ?? []); @endphp
    @foreach(\App\Support\SchoolRoleCatalog::CATEGORIES as $category => $modules)
        <h3 style="margin-top:22px;background:#eef4f7;border-radius:8px;padding:12px">{{ $category }}</h3>
        @foreach($modules as $module => $permissions)
            <h4>{{ $module }}</h4>
            <div class="rf-perms">
                @foreach($permissions as $key)
                <label class="rf-perm">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key,$selected,true))>
                    <span><strong>{{ \App\Support\SchoolRoleCatalog::PERMISSIONS[$key] }}</strong><small>{{ $key }}</small></span>
                </label>
                @endforeach
            </div>
        @endforeach
    @endforeach

</div>
<div class="rf-actions"><a class="btn btn-secondary" href="{{ route('platform.schools.roles.index',$school) }}">Annuleren</a><button class="btn" type="submit">Rol opslaan</button></div>
</form>
@endsection
@push('styles')
<style>
.rf-card{max-width:1050px}.rf-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.rf-grid label{font-size:12px;font-weight:700;display:flex;flex-direction:column;gap:8px}.rf-grid input,.rf-grid textarea{padding:12px;border:1px solid #d5dfe7;border-radius:8px;font:inherit}.rf-full{grid-column:1/-1}.rf-card h2{margin-top:32px;border-bottom:1px solid #e1e8ee;padding-bottom:14px}.rf-explain{font-size:12px;color:#718096}.rf-perms{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:20px}.rf-perm{display:flex;gap:12px;align-items:center;padding:14px;background:#f8fafc;border-radius:9px;border:1px solid #e1e8ee}.rf-perm span{display:flex;flex-direction:column;gap:5px}.rf-perm small{color:#718096}.rf-actions{display:flex;justify-content:flex-end;gap:10px;max-width:1050px;margin:24px 0}.rf-error{padding:16px;background:#fff1f2;color:#9f1239;margin-bottom:20px}@media(max-width:650px){.rf-grid,.rf-perms{grid-template-columns:1fr}.rf-full{grid-column:auto}}
</style>
@endpush
