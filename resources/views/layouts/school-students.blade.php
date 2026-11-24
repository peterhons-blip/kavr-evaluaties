<!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Leerlingen') | Artelynx</title>
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--navy:#142b45;--teal:#59aaa5;--muted:#718096;--border:#e1e8ee}
*{box-sizing:border-box}body{margin:0;font:14px/1.6 'Plus Jakarta Sans',sans-serif;background:#f0f4f7;color:var(--navy)}
.shell{min-height:100vh;display:flex}.side{width:235px;flex:0 0 235px;padding:23px 15px;background:var(--navy);color:#fff}
.logo{display:block;padding:7px 12px 22px;color:#fff;text-decoration:none;font-size:21px;font-weight:800}
.side a.nav{display:block;padding:12px 14px;border-radius:8px;color:#e1eaf0;text-decoration:none;margin-bottom:5px;font-size:12px;font-weight:700}
.side a.nav:hover,.side a.active{background:#28546c}.side small{display:block;margin:20px 12px 8px;color:#acc3d2}
.main{flex:1;min-width:0}.top{background:white;border-bottom:1px solid var(--border);padding:22px 30px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.content{max-width:1400px;margin:auto;padding:30px}.card{background:white;border:1px solid var(--border);border-radius:13px;padding:26px}
.btn{display:inline-flex;align-items:center;gap:7px;padding:10px 15px;border:0;border-radius:8px;background:var(--navy);color:white;text-decoration:none;font:inherit;font-size:12px;font-weight:700;cursor:pointer}
.btn-secondary{background:#e9f3f2;color:#286e69}.btn:hover{opacity:.9}.page-header{margin-bottom:20px}.page-header h1{margin:0 0 7px;font-size:26px}.page-header p{margin:0;color:var(--muted)}
@media(max-width:780px){.shell{display:block}.side{width:auto;padding:12px;display:flex;flex-wrap:wrap;gap:6px}.side a.nav{margin:0}.side small{display:none}.logo{padding:5px 12px}.top{padding:15px}.content{padding:15px}}
</style>
@stack('styles')
</head>
<body><div class="shell"><aside class="side">
<a class="logo" href="{{ route('dashboard') }}">Arte<span style="color:#5dc8c6">lynx</span></a>
<a class="nav" href="{{ route('dashboard') }}">⌂ Dashboard</a>
@if(\App\Support\SchoolAccess::allows(auth()->user(), $school, 'students.view') || \App\Support\SchoolAccess::allows(auth()->user(), $school, 'students.manage'))
<a class="nav {{ request()->routeIs('school.students.*') ? 'active' : '' }}" href="{{ route('school.students.index') }}">♙ Leerlingen</a>
@endif
@if(\App\Support\SchoolAccess::allows(auth()->user(), $school, 'users.view') || \App\Support\SchoolAccess::allows(auth()->user(), $school, 'users.manage'))
<a class="nav" href="{{ route('school.members.index') }}">Personeelsleden</a>
@endif
@if(\App\Support\SchoolAccess::allows(auth()->user(), $school, 'roles.manage'))
<a class="nav" href="{{ route('school.roles.index') }}">Rollen &amp; rechten</a>
@endif
@if(\App\Support\SchoolAccess::allows(auth()->user(), $school, 'school.settings'))
<a class="nav {{ request()->routeIs('school.locations.*') ? 'active' : '' }}" href="{{ route('school.locations.index') }}">Vestigingsplaatsen</a>
<a class="nav {{ request()->routeIs('school.settings.*') ? 'active' : '' }}" href="{{ route('school.settings.edit') }}">Schoolinstellingen</a>
@endif
<small>{{ $school->name }}</small>
</aside><div class="main"><header class="top"><strong>@yield('page-title', 'Leerlingen')</strong><a class="btn btn-secondary" href="{{ route('schools.select') }}">Wissel school</a></header><main class="content">@yield('content')</main></div></div>
@stack('scripts')
</body></html>
