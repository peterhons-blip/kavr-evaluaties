<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Schoolomgeving') | {{ $school->name }} | Artelynx</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=3" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --navy:#142b45;--navy-deep:#11243e;--navy-muted:#8299b3;--teal:#139b9f;
            --bg:#f1f5f9;--ink:#172e4d;--muted:#70829c;--line:#e2eaf2;
            --white:#fff;--radius:16px;
        }
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;background:var(--bg);color:var(--ink);font:14px/1.6 'Plus Jakarta Sans',Arial,sans-serif;-webkit-font-smoothing:antialiased}
        a{color:inherit}
        .shell{display:flex;min-height:100vh}
        .sidebar{display:flex;flex-direction:column;flex:0 0 255px;min-width:0;background:linear-gradient(160deg,#173353,#10243e 70%);color:#e1ebf4}
        .sidebar-brand{display:flex;align-items:center;gap:12px;padding:28px 23px 25px;text-decoration:none;border-bottom:1px solid #ffffff19}
        .brand-mark{width:43px;height:43px;object-fit:contain;flex:0 0 43px}
        .brand-name{font-size:20px;font-weight:800;letter-spacing:-.6px;color:#fff;line-height:1.15}
        .brand-name span{color:#5dc8c6}
        .brand-tagline{font-size:10px;color:#b5c7d8;letter-spacing:.1px}
        .nav{padding:22px 13px;flex:1}
        .nav-label{padding:0 14px;margin:17px 0 11px;font-size:10px;font-weight:800;letter-spacing:1.3px;color:#88a0b8}
        .nav-link,.nav-disabled{display:flex;align-items:center;gap:13px;padding:13px 15px;margin-bottom:5px;border-radius:10px;text-decoration:none;font-size:12px;font-weight:700}
        .nav-link{color:#dae6f2;transition:background .18s}
        .nav-link:hover{background:#ffffff18}
        .nav-link.active{background:#167f9a;color:#fff}
        .nav-icon{width:23px;font-size:19px;text-align:center;line-height:1}
        .nav-disabled{opacity:.39;cursor:default}
        .nav-bottom{border-top:1px solid #ffffff19;padding:19px 21px;font-size:11px;color:#abc0d4}
        .nav-bottom strong{display:block;color:#f4f8fd;font-size:12px;line-height:1.5;margin-top:5px}
        .main{flex:1;min-width:0}
        .topbar{min-height:78px;display:flex;justify-content:space-between;align-items:center;gap:18px;padding:15px 36px;background:#fff;border-bottom:1px solid var(--line)}
        .top-title{font-size:17px;font-weight:800}
        .top-crumb{font-size:11px;color:var(--muted)}
        .top-actions{display:flex;align-items:center;gap:15px;flex-wrap:wrap}
        .change-school{font-size:12px;font-weight:700;text-decoration:none;color:#237f88}
        .profile{display:flex;align-items:center;gap:10px}
        .profile-icon{width:37px;height:37px;border-radius:50%;display:grid;place-items:center;background:#e6f6f5;color:#167b84;font-weight:800}
        .profile-name{font-weight:800;font-size:12px;line-height:1.4}
        .profile-role{color:var(--muted);font-size:10px}
        .logout-btn{background:transparent;border:1px solid #e2e8f0;border-radius:8px;padding:9px 13px;font:inherit;font-size:11px;font-weight:700;color:#567086;cursor:pointer}
        .content{max-width:1380px;padding:33px 36px 48px;margin:0 auto}
        .welcome{position:relative;overflow:hidden;min-height:184px;border:1px solid var(--line);border-radius:18px;background:linear-gradient(118deg,#fff,#f4f8fb);box-shadow:0 9px 30px #17345307;display:flex;align-items:center;margin-bottom:24px}
        .welcome-copy{padding:36px 38px;max-width:73%;position:relative;z-index:1}
        .welcome-eyebrow{color:#0f969d;font-weight:800;font-size:11px;letter-spacing:1.3px;text-transform:uppercase;margin:0 0 10px}
        .welcome h1{margin:0 0 10px;font-size:clamp(25px,3vw,36px);line-height:1.2;letter-spacing:-1.1px}
        .welcome p{margin:0;color:#637d94;max-width:630px;font-size:13px;line-height:1.9}
        .welcome-art{position:absolute;right:-11px;top:-53px;width:308px;height:308px;opacity:.17;transform:rotate(-9deg)}
        .welcome-art img{width:100%;height:100%;object-fit:contain}
        .section-title{font-size:17px;font-weight:800;margin:27px 0 15px}
        .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px}
        .stat{border:1px solid var(--line);background:#fff;border-radius:14px;padding:22px 23px;box-shadow:0 6px 22px #132b4405}
        .stat-icon{width:41px;height:41px;border-radius:12px;background:#e6f7f7;color:#1594a0;display:grid;place-items:center;font-size:19px;margin-bottom:12px}
        .stat-icon.lilac{background:#f3efff;color:#7657ab}.stat-icon.gold{background:#fff4e7;color:#c98a42}
        .stat-num{font-size:30px;line-height:1.15;font-weight:800;letter-spacing:-.6px}
        .stat-label{font-size:12px;font-weight:800;margin-top:5px}
        .stat-desc{font-size:11px;color:var(--muted);margin-top:5px}
        .panels{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(290px,.8fr);gap:20px;margin-top:24px}
        .panel{background:#fff;border-radius:15px;border:1px solid var(--line);padding:25px;box-shadow:0 6px 22px #132b4405}

        .panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px}
        .panel-head h2{margin:0 0 5px;font-size:16px}
        .panel-head .panel-sub{margin:0}
        .panel-link{color:#168a91;font-size:11px;font-weight:800;text-decoration:none;white-space:nowrap}
        .panel-link:hover{text-decoration:underline}
        .activity-row{display:flex;align-items:center;gap:14px;padding:15px 0;border-bottom:1px solid #edf1f5}
        .activity-row:last-child{border-bottom:0;padding-bottom:0}
        .activity-symbol{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;border-radius:50%;background:#e8f6f5;color:#15959d;font-size:20px}
        .activity-symbol.purple{background:#f2eafd;color:#935bb5}
        .activity-symbol.gold{background:#fff3e6;color:#d58a34}
        .activity-info{min-width:0;flex:1}
        .activity-info strong{font-size:12px;display:block}
        .activity-info small{font-size:11px;color:var(--muted);display:block;margin-top:3px}
        .activity-date{font-size:10px;color:#98a8b8;white-space:nowrap}
        .dashboard-right{display:flex;flex-direction:column;gap:20px}
        .empty-activity{padding:24px 0 16px;color:var(--muted);font-size:12px;line-height:1.8}
        .mini-avatar{flex:0 0 37px;width:37px;height:37px;display:grid;place-items:center;border-radius:50%;background:#e9f1fb;color:#346da2;font-size:12px;font-weight:800}
        .member-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #edf1f5}
        .member-row:last-child{border:0;padding-bottom:0}
        .member-row strong{font-size:12px;display:block}
        .member-row small{font-size:10px;color:var(--muted);display:block;margin-top:3px;overflow-wrap:anywhere}
        .intro-band{display:flex;align-items:center;gap:10px;margin:0 0 20px;padding:13px 17px;background:#edf8f7;border:1px solid #d5eae8;border-radius:11px;color:#397c7c;font-size:12px}
        .intro-band strong{color:#176c74}
        .panel h2{font-size:16px;font-weight:800;margin:0 0 4px}
        .panel-sub{color:var(--muted);font-size:12px;margin:0 0 21px}
        .feature-row{display:flex;align-items:center;gap:14px;padding:16px 0;border-bottom:1px solid #edf1f5;text-decoration:none}
        .feature-row:last-child{border:0;padding-bottom:0}
        .feature-row:first-of-type{padding-top:0}
        .feature-icon{width:42px;height:42px;flex:0 0 42px;border-radius:12px;background:#e7f7f6;color:#0e9298;display:grid;place-items:center;font-size:20px}
        .feature-icon.alt{background:#eef2fb;color:#4b7dbc}
        .feature-icon.gold{background:#fff4e7;color:#c88d43}
        .feature-text{flex:1;min-width:0}
        .feature-text strong{display:block;font-size:12px}
        .feature-text small{display:block;color:var(--muted);font-size:11px;margin-top:4px}
        .feature-arrow{font-size:23px;color:#97aabe}
        .feature-off{opacity:.53;cursor:default}
        .pill{display:inline-block;border-radius:100px;padding:5px 9px;background:#e6f7f6;color:#19847f;font-size:10px;font-weight:800}
        .platform-link{display:inline-block;margin-top:20px;padding:11px 16px;border-radius:9px;color:#fff;background:#152e4b;text-decoration:none;font-size:12px;font-weight:800}
        .foot{text-align:center;font-size:11px;color:#8b9cb0;margin-top:34px}
        @media(max-width:1100px){.sidebar{flex-basis:220px}.content{padding:25px}.stats{gap:12px}.panels{grid-template-columns:1fr}}
        @media(max-width:730px){.shell{display:block}.sidebar{display:block}.sidebar-brand{padding:14px 20px}.nav{display:flex;gap:7px;overflow-x:auto;padding:10px}.nav-label,.nav-disabled,.nav-bottom{display:none}.nav-link{white-space:nowrap}.topbar{padding:16px 20px;flex-wrap:wrap}.content{padding:20px 15px 35px}.welcome-copy{max-width:100%;padding:27px 23px}.welcome-art{opacity:.07}.stats{grid-template-columns:1fr}.panel{padding:21px}.profile-name,.profile-role{display:none}}


        /* One consistent school layout across dashboard and administration */
        .sidebar-brand{display:flex;justify-content:center;align-items:center;min-height:120px;padding:22px 17px;background:#fff;border-bottom:1px solid #e1e8ee}
        .school-brand-logo{display:block;width:190px;max-width:100%;max-height:82px;height:auto;object-fit:contain}
        .school-brand-fallback{color:#142b45;font-size:24px;font-weight:800;letter-spacing:-.7px}
        .brand-mark{width:44px;height:44px;flex:0 0 44px}
        .brand-wordmark{font-size:18px;font-weight:800;color:#fff;letter-spacing:-.6px;white-space:nowrap}
        .brand-wordmark span{color:#5dc8c6}
        .school-identity{display:flex;align-items:center;gap:12px;min-width:0}
        .school-identity-logo{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;background:#fff;border:1px solid #ffffff32;border-radius:10px;overflow:hidden;padding:5px}
        .school-identity-logo img{max-width:100%;max-height:100%;object-fit:contain}
        .school-identity-logo .fallback{font-size:23px;color:#286e69}
        .school-identity-name{min-width:0;overflow-wrap:anywhere}
        .school-identity-name small{display:block;font-size:10px;letter-spacing:1px;font-weight:700;color:#b5c7d8;margin-bottom:4px}
        .school-identity-name strong{display:block;font-size:12px;color:#fff;line-height:1.5}
        .top-action-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid #dbe7eb;border-radius:9px;padding:10px 14px;background:#e9f3f2;color:#286e69;text-decoration:none;font:inherit;font-size:12px;font-weight:750;white-space:nowrap;transition:background .2s,border-color .2s}
        .top-action-btn:hover{background:#d7ebe8;border-color:#bcd7d3}
        .logout-btn{height:40px;padding:9px 15px;background:#142b45;color:#fff;border:1px solid #142b45;font-size:12px;font-weight:700}
        .logout-btn:hover{background:#223e5b;cursor:pointer}
        .profile-icon{flex-shrink:0}
        .profile-name,.profile-role{display:block}
        .top-actions{gap:9px}
        .intro-band{margin-top:19px}
        .content .card{background:#fff;border:1px solid var(--line);border-radius:13px;padding:26px;box-shadow:0 6px 22px #132b4405}
        .content .page-header{margin-bottom:24px}
        .content .page-header h1{font-size:25px;font-weight:800;margin:0 0 8px}
        .content .page-header p{font-size:12px;color:var(--muted);margin:0}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 16px;border:0;border-radius:9px;background:#142b45;color:#fff;text-decoration:none;font:inherit;font-size:12px;font-weight:700;cursor:pointer}
        .btn:hover{background:#223e5b}
        .btn-secondary,.btn.alt{background:#e9f3f2;color:#286e69}
        .btn-secondary:hover,.btn.alt:hover{background:#d7ebe8}
        @media(max-width:950px){.top-actions{gap:7px}.topbar{padding:16px 20px}.top-action-btn{padding:9px 11px}}
        @media(max-width:730px){.sidebar-brand{min-height:70px;padding:12px 18px}.school-brand-logo{width:135px;max-height:55px}.brand-wordmark{font-size:18px}.top-actions{width:100%}.profile{display:none}.top-action-btn{flex:1}.school-identity-logo{width:38px;height:38px;flex-basis:38px}.nav-bottom{display:flex;padding:12px;flex:1}}
    </style>
    @stack('styles')
</head>
<body>
@php
    // Active school is validated by middleware; each permission remains school-scoped.
    $schoolCanViewStudents = \App\Support\SchoolAccess::allows(auth()->user(), $school, 'students.view')
        || \App\Support\SchoolAccess::allows(auth()->user(), $school, 'students.manage');
    $schoolCanViewUsers = \App\Support\SchoolAccess::allows(auth()->user(), $school, 'users.view')
        || \App\Support\SchoolAccess::allows(auth()->user(), $school, 'users.manage');
    $schoolCanManageRoles = \App\Support\SchoolAccess::allows(auth()->user(), $school, 'roles.manage');
    $schoolCanManageSettings = \App\Support\SchoolAccess::allows(auth()->user(), $school, 'school.settings');
    $schoolLogoPath = $school->settings?->logo_path;
@endphp
<div class="shell">
    <aside class="sidebar">
        <a class="sidebar-brand" href="{{ route('dashboard') }}" aria-label="Artelynx schooldashboard">
            @if (file_exists(public_path('images/artelynx-logo.webp')))
                <img class="school-brand-logo" src="{{ asset('images/artelynx-logo.webp') }}" alt="Artelynx">
            @else
                <span class="school-brand-fallback">Artelynx</span>
            @endif
        </a>
        <nav class="nav" aria-label="Schoolnavigatie">
            <div class="nav-label">ALGEMEEN</div>
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">⌂</span> Dashboard</a>
            <span class="nav-disabled"><span class="nav-icon">✧</span> Evaluaties <span style="margin-left:auto;font-size:9px">BINNENKORT</span></span>
            @if ($schoolCanViewStudents)
                <a class="nav-link {{ request()->routeIs('school.students.*') ? 'active' : '' }}" href="{{ route('school.students.index') }}"><span class="nav-icon">♙</span> Leerlingen</a>
            @endif
            <span class="nav-disabled"><span class="nav-icon">▦</span> Lesgroepen</span>
            @if ($schoolCanViewUsers || $schoolCanManageRoles || $schoolCanManageSettings)
                <div class="nav-label">BEHEER</div>
                @if ($schoolCanViewUsers)
                    <a class="nav-link {{ request()->routeIs('school.members.*') ? 'active' : '' }}" href="{{ route('school.members.index') }}"><span class="nav-icon">♙</span> Personeelsleden</a>
                @endif
                @if ($schoolCanManageSettings)
                    <a class="nav-link {{ request()->routeIs('school.locations.*') ? 'active' : '' }}" href="{{ route('school.locations.index') }}"><span class="nav-icon">⌖</span> Vestigingsplaatsen</a>
                    <a class="nav-link {{ request()->routeIs('school.settings.*') ? 'active' : '' }}" href="{{ route('school.settings.edit') }}"><span class="nav-icon">⚙</span> Schoolinstellingen</a>
                @endif
                @if ($schoolCanManageRoles)
                    <a class="nav-link {{ request()->routeIs('school.roles.*') ? 'active' : '' }}" href="{{ route('school.roles.index') }}"><span class="nav-icon">⚙</span> Rollen &amp; rechten</a>
                @endif
            @endif
        </nav>
        <div class="nav-bottom">
            <div class="school-identity">
                <div class="school-identity-logo">
                    @if($schoolLogoPath)
                        <img src="{{ asset('storage/' . $schoolLogoPath) }}" alt="Logo {{ $school->name }}"
                             onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
                        <span class="fallback" hidden aria-hidden="true">⌂</span>
                    @else
                        <span class="fallback" aria-hidden="true">⌂</span>
                    @endif
                </div>
                <div class="school-identity-name"><small>ACTIEVE SCHOOL</small><strong>{{ $school->name }}</strong></div>
            </div>
        </div>
    </aside>
    <div class="main">
        <header class="topbar">
            <div>
                <div class="top-title">@yield('page-title','Schoolomgeving')</div>
                <div class="top-crumb">Artelynx / {{ $school->name }}</div>
            </div>
            <div class="top-actions">
                <a class="top-action-btn" href="{{ route('schools.select') }}">⇄ <span>Wissel omgeving</span></a>
                <a class="top-action-btn" href="{{ route('profile.edit') }}">♙ <span>Mijn profiel</span></a>
                <div class="profile" aria-label="Aangemelde gebruiker">
                    <div class="profile-icon">{{ mb_strtoupper(mb_substr(auth()->user()->first_name ?: 'G',0,1)) }}</div>
                    <div><span class="profile-name">{{ auth()->user()->full_name ?: auth()->user()->name }}</span><span class="profile-role">Aangemeld</span></div>
                </div>
                <form action="{{ route('logout') }}" method="POST">@csrf<button class="logout-btn" type="submit">Afmelden</button></form>
            </div>
        </header>
        <main class="content">@yield('content')</main>
    </div>
</div>
@stack('scripts')
</body>
</html>
