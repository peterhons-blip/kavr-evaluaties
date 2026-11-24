@extends('layouts.platform')
@section('title','Rechtenbeheer')
@section('page-title','Rollen en rechten')
@push('styles')
<style>
.rights-matrix-wrap{overflow-x:auto;border:1px solid #e1e8ee;border-radius:10px;margin-top:18px}
.rights-matrix{width:100%;min-width:850px;border-collapse:separate;border-spacing:0;font-size:12px}
.rights-matrix th,.rights-matrix td{border-bottom:1px solid #edf1f5}
.rights-matrix thead th{padding:14px 10px;background:#f7f9fb;color:#142b45;text-align:center}
.rights-matrix thead th:first-child{text-align:left;min-width:220px}
.rights-role{display:inline-flex;align-items:center;gap:7px;padding:9px 11px;border:1px solid #cbdedc;border-radius:8px;background:#e9f3f2;color:#286e69!important;text-decoration:none!important;font-weight:700;white-space:nowrap}
.rights-role:hover{background:#d7ebe8;border-color:#59aaa5}
.rights-role:focus-visible{outline:3px solid #59aaa5;outline-offset:2px}
.rights-category th{text-align:left;padding:12px 15px;background:#eaf1f5;color:#142b45}
.rights-section th{text-align:left;padding:10px 15px;background:#f7fafb;color:#49687a}
.rights-permission td{padding:11px 13px;text-align:center}
.rights-permission td:first-child{text-align:left;line-height:1.5}
.rights-permission:hover td{background:#f8fbfc}
.rights-permission small{display:block;color:#94a3b8;font-size:10px}
.rights-yes{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#e3f3ed;color:#207653;font-size:15px;font-weight:800}
.rights-no{color:#a5b1bc;font-size:16px}
</style>
@endpush
@section('content')
<div class="page-header"><h1>Rollen en rechten</h1><p>Beheer per school de toegestane functies, gegroepeerd per categorie en onderdeel. Individuele uitzonderingen kunnen per personeelslid worden ingesteld.</p></div>
@if(session('success'))<div class="card" style="margin-bottom:15px;color:#15734c">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" style="margin-bottom:15px;color:#991b1b">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card" style="margin-bottom:20px">
    <form method="GET" action="{{ route('platform.permissions.index') }}" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
        <label style="display:grid;gap:6px">School
            <select name="school_id" onchange="this.form.submit()" style="padding:10px;border:1px solid #cbd5e1;border-radius:8px;min-width:260px">
                @foreach($schools as $option)<option value="{{ $option->id }}" @selected($school?->id === $option->id)>{{ $option->name }}</option>@endforeach
            </select>
        </label>
        <button class="btn" type="submit">Tonen</button>
    </form>
</div>
@if($school)
<div class="card" style="margin-bottom:20px">
    <h2>Rechtenmatrix: {{ $school->name }}</h2>
    <p style="color:#718096;font-size:12px">Vinkjes tonen de rechten per rol. Klik op een rol om de rechten te wijzigen. De platformbeheerderrol staat hier los van.</p>
    <div class="rights-matrix-wrap"><table class="rights-matrix">
        <thead><tr><th scope="col">Categorie / onderdeel / functie</th>
            @foreach($roles as $role)<th scope="col"><a class="rights-role" href="{{ route('platform.schools.roles.edit',[$school,$role]) }}" aria-label="Rechten van {{ $role->name }} bewerken">{{ $role->name }} <span aria-hidden="true">✎</span></a></th>@endforeach
        </tr></thead>
        <tbody>
        @foreach(\App\Support\SchoolRoleCatalog::CATEGORIES as $category => $parts)
            <tr class="rights-category"><th colspan="{{ $roles->count()+1 }}">{{ $category }}</th></tr>
            @foreach($parts as $part => $permissions)
                <tr class="rights-section"><th colspan="{{ $roles->count()+1 }}">{{ $part }}</th></tr>
                @foreach($permissions as $key)
                    <tr class="rights-permission"><td>{{ \App\Support\SchoolRoleCatalog::PERMISSIONS[$key] }}<small>{{ $key }}</small></td>
                    @foreach($roles as $role)<td>@if(in_array($key,$role->permissions ?? [],true))<span class="rights-yes" title="Toegestaan" aria-label="Toegestaan">✓</span>@else<span class="rights-no" title="Niet toegestaan" aria-label="Niet toegestaan">—</span>@endif</td>@endforeach
                    </tr>
                @endforeach
            @endforeach
        @endforeach
        </tbody>
    </table></div>
    <p style="margin-top:18px"><a href="{{ route('platform.schools.roles.create',$school) }}" class="btn">Nieuwe schoolrol</a></p>
</div>
<div class="card">
    <h2>Individuele uitzonderingen</h2><p style="color:#718096;font-size:12px">De schoolrol is het uitgangspunt. Per personeelslid kun je specifieke functies toestaan of weigeren.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:10px">
        @foreach($members as $member)
            <a href="{{ route('platform.permissions.user',[$school,$member]) }}" style="padding:13px;border:1px solid #e1e8ee;border-radius:9px;text-decoration:none;color:#142b45">
                <strong>{{ $member->full_name }}</strong><br><small>{{ $member->email }}</small>
            </a>
        @endforeach
    </div>
</div>
@else
<div class="card">Voeg eerst een school toe om rollen en rechten te beheren.</div>
@endif
@endsection
