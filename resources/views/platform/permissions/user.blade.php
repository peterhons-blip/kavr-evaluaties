@extends('layouts.platform')
@section('title','Persoonlijke rechten')
@section('page-title','Persoonlijke rechten')
@section('content')
<div class="page-header"><h1>Individuele rechten</h1><p>{{ $user->full_name }} · {{ $school->name }} · Basisrol: {{ $role->name }}</p></div>
@if(session('success'))<div class="card" style="color:#147d50;margin-bottom:15px">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" style="color:#991b1b;margin-bottom:15px">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form method="POST" action="{{ route('platform.permissions.user.update',[$school,$user]) }}">@csrf @method('PUT')
    <div class="card" style="max-width:1100px">
        <p style="color:#64748b;font-size:12px">Overnemen = recht volgens schoolrol. Expliciet toestaan of weigeren geldt alleen voor deze persoon en school. Een inactieve school of schoolkoppeling blijft altijd geblokkeerd.</p>
        @foreach(\App\Support\SchoolRoleCatalog::CATEGORIES as $category => $modules)
        <h2 style="background:#eff5f7;padding:12px;border-radius:9px;margin-top:22px">{{ $category }}</h2>
            @foreach($modules as $module => $permissions)
                <h3 style="font-size:15px;margin:18px 0 9px">{{ $module }}</h3>
                @foreach($permissions as $permission)
                    @php $mode = old('overrides.'.$permission, $overrides->has($permission) ? ($overrides->get($permission) ? 'allow' : 'deny') : 'inherit'); @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:11px;border-bottom:1px solid #edf0f3">
                        <div><strong>{{ \App\Support\SchoolRoleCatalog::PERMISSIONS[$permission] }}</strong><br><small style="color:#64748b">{{ $permission }} · Rol: {{ in_array($permission,$role->permissions ?? [],true) ? 'Toegestaan' : 'Niet toegestaan' }}</small></div>
                        <select name="overrides[{{ $permission }}]" style="padding:9px;border-radius:7px;border:1px solid #cbd5e1">
                            <option value="inherit" @selected($mode === 'inherit')>Overnemen van rol</option>
                            <option value="allow" @selected($mode === 'allow')>Toestaan</option>
                            <option value="deny" @selected($mode === 'deny')>Weigeren</option>
                        </select>
                    </div>
                @endforeach
            @endforeach
        @endforeach
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px">
            <a class="btn btn-secondary" href="{{ route('platform.permissions.index',['school_id'=>$school->id]) }}">Terug</a>
            <button class="btn" type="submit">Rechten opslaan</button>
        </div>
    </div>
</form>
@endsection
