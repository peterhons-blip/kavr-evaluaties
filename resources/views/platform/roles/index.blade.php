@extends('layouts.platform')
@section('title','Schoolrollen')
@section('page-title','Schoolrollen en rechten')
@section('content')
<div class="page-header"><h1>{{ $school->name }}</h1><p>Beheer de rollen en de toegestane functies voor deze school.</p></div>
@include('platform.schools.partials.tabs',['school'=>$school])
@if(session('success'))<div class="rr-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="rr-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card">
    <div class="rr-head"><div><h2>Rollen van deze school</h2><p>Geen enkele wijziging heeft invloed op rollen van andere scholen.</p></div>
    <a href="{{ route('platform.schools.roles.create',$school) }}" class="btn">+ Nieuwe rol</a></div>
    <div class="rr-list">
    @foreach($roles as $role)
    <div class="rr-row">
        <div><strong>{{ $role->name }}</strong><p>{{ $role->description }}</p><small>{{ count($role->permissions ?? []) }} rechten · {{ $role->is_system ? 'Standaardrol' : 'Eigen rol' }}</small></div>
        <div class="rr-actions">
            <a class="btn btn-secondary" href="{{ route('platform.schools.roles.edit',[$school,$role]) }}">Rechten instellen</a>
            @unless($role->is_system)
            <form method="POST" action="{{ route('platform.schools.roles.destroy',[$school,$role]) }}" onsubmit="return confirm('Deze rol definitief verwijderen?');">@csrf @method('DELETE')
                <button type="submit" class="rr-delete" title="Rol verwijderen">Verwijderen</button>
            </form>
            @endunless
        </div>
    </div>
    @endforeach
    </div>
</div>
@endsection
@push('styles')
<style>
.rr-success,.rr-error{padding:16px;margin-bottom:20px;border-radius:9px}.rr-success{background:#edf8f3;color:#246b52}.rr-error{background:#fff1f2;color:#9f1239}.rr-head{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}.rr-head h2{margin:0}.rr-head p,.rr-row p,.rr-row small{color:#718096}.rr-row{display:flex;justify-content:space-between;gap:20px;align-items:center;padding:22px 0;border-bottom:1px solid #e1e8ee}.rr-row:last-child{border:0}.rr-row strong{font-size:15px}.rr-row p{font-size:12px;margin:7px 0}.rr-row small{font-size:11px}.rr-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.rr-delete{padding:11px 14px;border:1px solid #fecdd3;background:#fff1f2;color:#b94747;border-radius:8px;cursor:pointer}@media(max-width:650px){.rr-row{align-items:flex-start;flex-direction:column}}
</style>
@endpush
