@extends('layouts.school')
@section('title','Rollen en rechten')
@section('page-title','Rollen en rechten')
@section('content')
<div class="wrap">
<h1>{{ $role->exists ? 'Schoolrol bewerken' : 'Nieuwe schoolrol' }}</h1><p>{{ $school->name }}</p>
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ $role->exists ? route('school.roles.update',$role) : route('school.roles.store') }}">@csrf @if($role->exists) @method('PUT') @endif
<div class="card"><div class="fields">
<label>Naam *<input name="name" maxlength="100" required value="{{ old('name',$role->name) }}"></label>
<label>Rolcode *<input name="slug" maxlength="30" required value="{{ old('slug',$role->slug) }}" @readonly($role->is_system)></label>
<label class="full">Beschrijving<textarea name="description" rows="2" maxlength="1000">{{ old('description',$role->description) }}</textarea></label>
</div><h2>Functierechten</h2><p>Toegewezen leerling- en lesgroepgegevens worden later aanvullend gecontroleerd.</p>
@php $selected=old('permissions',$role->permissions ?? []); @endphp
@foreach(\App\Support\SchoolRoleCatalog::CATEGORIES as $category=>$modules)<h3 style="background:#edf3f7;padding:10px">{{ $category }}</h3>@foreach($modules as $module=>$permissions)<h4>{{ $module }}</h4><div class="perms">@foreach($permissions as $key)<label class="perm"><input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key,$selected,true))><span><strong>{{ \App\Support\SchoolRoleCatalog::PERMISSIONS[$key] }}</strong><br><small>{{ $key }}</small></span></label>@endforeach</div>@endforeach @endforeach
</div><div class="buttons"><a class="btn alt" href="{{ route('school.roles.index') }}">Annuleren</a><button type="submit" class="btn">Rol opslaan</button></div></form></div>
@endsection
@push('styles')
<style>
body{font-family:system-ui,sans-serif;background:#f0f4f7;color:#142b45;margin:0}.wrap{max-width:1000px;margin:35px auto;padding:0 20px}.card{background:#fff;padding:30px;border:1px solid #e1e8ee;border-radius:13px}.fields{display:grid;grid-template-columns:1fr 1fr;gap:15px}.fields label{display:flex;flex-direction:column;gap:8px;font-weight:600}.fields input,.fields textarea{padding:12px;border:1px solid #d5dfe7;border-radius:8px;font:inherit}.full{grid-column:1/-1}.perms{display:grid;grid-template-columns:1fr 1fr;gap:12px}.perm{display:flex;align-items:center;gap:10px;padding:14px;border:1px solid #e1e8ee;border-radius:8px;background:#f8fafc}.perm small{color:#718096}.buttons{display:flex;justify-content:flex-end;gap:10px;margin-top:25px}.btn{background:#142b45;border:0;border-radius:8px;color:#fff;padding:12px 17px;cursor:pointer;text-decoration:none}.btn.alt{background:#e9f3f2;color:#286e69}.error{background:#fff1f2;color:#9f1239;padding:15px;margin-bottom:20px}@media(max-width:650px){.fields,.perms{grid-template-columns:1fr}.full{grid-column:auto}}
.wrap{max-width:1120px;margin:0 auto;padding:0} .wrap>.header,.wrap>.head{margin-bottom:20px} .wrap h1{font-size:25px;line-height:1.3;margin:0 0 8px} .wrap>p{color:#718096;font-size:12px} .wrap .card{margin-top:19px} .wrap table{width:100%} .wrap .btn{font-family:inherit;font-weight:700} .wrap .btn.alt{background:#e9f3f2;color:#286e69} .wrap .error{border-radius:9px;margin:12px 0} .wrap .success{border-radius:9px;margin:12px 0}
</style>
@endpush
