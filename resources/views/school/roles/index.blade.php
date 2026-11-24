@extends('layouts.school')
@section('title','Rollen en rechten')
@section('page-title','Rollen en rechten')
@section('content')
<div class="wrap">
<div class="head"><div><h1>Schoolrollen</h1><p>{{ $school->name }} – beheer rechten uitsluitend voor deze school.</p></div></div>
@if(session('success'))<div class="success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card"><div class="head"><h2>Rollen en rechten</h2><a class="btn" href="{{ route('school.roles.create') }}">+ Nieuwe rol</a></div>
@foreach($roles as $role)<div class="role"><div><strong>{{ $role->name }}</strong><p>{{ $role->description }}</p><small>{{ count($role->permissions ?? []) }} rechten • {{ $role->is_system ? 'Standaardrol' : 'Eigen rol' }}</small></div>
<div class="actions"><a class="btn alt" href="{{ route('school.roles.edit',$role) }}">Bewerken</a>
@unless($role->is_system)<form action="{{ route('school.roles.destroy',$role) }}" method="POST" onsubmit="return confirm('Rol verwijderen?');">@csrf @method('DELETE')<button class="danger">Verwijderen</button></form>@endunless</div></div>@endforeach
</div></div>
@endsection
@push('styles')
<style>
body{font-family:system-ui,sans-serif;margin:0;background:#f0f4f7;color:#142b45}.wrap{max-width:1050px;margin:35px auto;padding:0 20px}.head,.role{display:flex;align-items:center;justify-content:space-between;gap:20px}.card{background:#fff;padding:25px;border-radius:13px;border:1px solid #e1e8ee;margin:20px 0}.role{padding:20px 0;border-bottom:1px solid #e1e8ee}.role:last-child{border:0}.role p,small{color:#718096}.actions{display:flex;gap:10px;align-items:center}.btn{display:inline-block;background:#142b45;color:white;padding:10px 15px;border-radius:8px;text-decoration:none;border:0;cursor:pointer}.btn.alt{background:#e9f3f2;color:#286e69}.danger{color:#b94747;border:1px solid #fecdd3;background:#fff1f2;padding:10px;border-radius:8px;cursor:pointer}.success{padding:14px;background:#edf8f3;color:#246b52}.error{padding:14px;background:#fff1f2;color:#9f1239}@media(max-width:700px){.role{align-items:flex-start;flex-direction:column}}
.wrap{max-width:1120px;margin:0 auto;padding:0} .wrap>.header,.wrap>.head{margin-bottom:20px} .wrap h1{font-size:25px;line-height:1.3;margin:0 0 8px} .wrap>p{color:#718096;font-size:12px} .wrap .card{margin-top:19px} .wrap table{width:100%} .wrap .btn{font-family:inherit;font-weight:700} .wrap .btn.alt{background:#e9f3f2;color:#286e69} .wrap .error{border-radius:9px;margin:12px 0} .wrap .success{border-radius:9px;margin:12px 0}
</style>
@endpush
