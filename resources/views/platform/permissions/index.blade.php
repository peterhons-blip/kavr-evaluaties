@extends('layouts.platform')
@section('title','Rechtenbeheer')
@section('page-title','Rollen en rechten')
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
    <div style="overflow-x:auto"><table style="border-collapse:collapse;width:100%;font-size:12px">
        <thead><tr><th style="text-align:left;padding:12px;border-bottom:1px solid #ddd">Categorie / onderdeel / functie</th>
            @foreach($roles as $role)<th style="padding:10px;border-bottom:1px solid #ddd;min-width:110px"><a href="{{ route('platform.schools.roles.edit',[$school,$role]) }}">{{ $role->name }}</a></th>@endforeach
        </tr></thead>
        <tbody>
        @foreach(\App\Support\SchoolRoleCatalog::CATEGORIES as $category => $parts)
            <tr><th colspan="{{ $roles->count()+1 }}" style="text-align:left;padding:12px;background:#edf3f7">{{ $category }}</th></tr>
            @foreach($parts as $part => $permissions)
                <tr><th colspan="{{ $roles->count()+1 }}" style="text-align:left;padding:9px;color:#49687a">{{ $part }}</th></tr>
                @foreach($permissions as $key)
                    <tr><td style="padding:10px 12px;border-bottom:1px solid #eef2f5">{{ \App\Support\SchoolRoleCatalog::PERMISSIONS[$key] }}<br><small style="color:#94a3b8">{{ $key }}</small></td>
                    @foreach($roles as $role)<td style="text-align:center;border-bottom:1px solid #eef2f5">{{ in_array($key,$role->permissions ?? [],true) ? '✓' : '—' }}</td>@endforeach
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
