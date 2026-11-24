@extends('layouts.school')
@section('title','Dashboard')
@section('page-title','Schooldashboard')
@section('content')
            <section class="welcome">
                <div class="welcome-copy">
                    <div class="welcome-eyebrow">{{ $school->name }}</div>
                    <h1>Welkom, {{ auth()->user()->first_name ?: auth()->user()->name }}!</h1>
                    <p>Fijn dat je er bent. Dit is jouw plek voor evaluaties, leerdoelen en de ontwikkeling van elk talent.</p>
                </div>
                <div class="welcome-art" aria-hidden="true"><img src="{{ asset('images/branding/artelynx-lynx.webp') }}" alt=""></div>
            </section>

            <h2 class="section-title">Jouw school in één oogopslag</h2>
            <section class="stats" aria-label="Schooloverzicht">
                <div class="stat"><div class="stat-icon">⌖</div><div class="stat-num">{{ $school->locations_count }}</div><div class="stat-label">Vestigingsplaatsen</div><div class="stat-desc">Gekoppeld aan jouw school</div></div>
                <div class="stat"><div class="stat-icon lilac">♙</div><div class="stat-num">{{ $canViewStudents ? $school->students_count : '—' }}</div><div class="stat-label">Actieve leerlingen</div><div class="stat-desc">{{ $canViewStudents ? 'Leerlingen in deze academie' : 'Alleen zichtbaar met leerlingenrechten' }}</div></div>
                <div class="stat"><div class="stat-icon gold">♧</div><div class="stat-num">{{ $canViewUsers ? $school->users_count : '—' }}</div><div class="stat-label">Personeelsleden</div><div class="stat-desc">{{ $canViewUsers ? 'Gebruikers van deze academie' : 'Alleen zichtbaar voor beheerders' }}</div></div>
            </section>

            <div class="intro-band">
                <span aria-hidden="true">✦</span>
                <span><strong>Jouw schoolomgeving.</strong> Je ziet alleen gegevens en beheerfuncties waarvoor je rechten hebt.</span>
            </div>

            <section class="panels">
                <div class="panel">
                    <div class="panel-head">
                        <div><h2>Recente activiteiten</h2><p class="panel-sub">Wijzigingen en updates binnen jouw school</p></div>
                    </div>
                    @if ($canManageRoles && $recentEvents->isNotEmpty())
                        @foreach ($recentEvents as $event)
                            @php
                                $activityLabels = [
                                    'school.member.added' => 'Gebruiker toegevoegd',
                                    'school.member.updated' => 'Schoolkoppeling gewijzigd',
                                    'school.user.created' => 'Nieuw account aangemaakt',
                                    'school.role.created' => 'Schoolrol aangemaakt',
                                    'school.role.updated' => 'Rechten aangepast',
                                    'school.role.deleted' => 'Schoolrol verwijderd',
                                ];
                                $activityLabel = $activityLabels[$event->action] ?? 'Beheeractie uitgevoerd';
                            @endphp
                            <div class="activity-row">
                                <span class="activity-symbol {{ $loop->index % 3 === 1 ? 'purple' : ($loop->index % 3 === 2 ? 'gold' : '') }}" aria-hidden="true">
                                    {{ str_contains($event->action, 'role') ? '⚙' : '✓' }}
                                </span>
                                <div class="activity-info">
                                    <strong>{{ $activityLabel }}</strong>
                                    <small>{{ $school->name }}</small>
                                </div>
                                <span class="activity-date">{{ \Illuminate\Support\Carbon::parse($event->created_at)->format('d/m/Y H:i') }}</span>
                            </div>
                        @endforeach
                    @elseif ($canManageRoles)
                        <p class="empty-activity">Er zijn nog geen beheeracties geregistreerd voor deze school. Nieuwe wijzigingen verschijnen hier automatisch.</p>
                    @else
                        <p class="empty-activity">De evaluatie- en activiteitenoverzichten worden beschikbaar wanneer deze modules zijn ontwikkeld. Je kunt ondertussen je schoolomgeving gebruiken.</p>
                    @endif
                </div>
                <div class="dashboard-right">
                    @if ($canViewUsers)
                        <div class="panel">
                            <div class="panel-head">
                                <div><h2>Recent gekoppelde personeelsleden</h2><p class="panel-sub">Medewerkers van jouw school</p></div>
                                <a class="panel-link" href="{{ route('school.members.index') }}">Alle bekijken →</a>
                            </div>
                            @forelse ($recentMembers as $member)
                                <div class="member-row">
                                    <span class="mini-avatar">{{ mb_strtoupper(mb_substr($member->first_name ?: $member->name ?: 'G', 0, 1)) }}</span>
                                    <span style="min-width:0">
                                        <strong>{{ $member->full_name ?: $member->name }}</strong>
                                        <small>{{ $member->email }}</small>
                                    </span>
                                </div>
                            @empty
                                <p class="empty-activity">Nog geen gebruikers gekoppeld.</p>
                            @endforelse
                        </div>
                    @endif

                    <div class="panel">
                        <div class="panel-head">
                            <div><h2>Snel naar</h2><p class="panel-sub">Jouw beschikbare functies</p></div>
                        </div>
                        @if ($canViewStudents)
                            <a class="feature-row" href="{{ route('school.students.index') }}">
                                <span class="feature-icon">♙</span><span class="feature-text"><strong>Leerlingen</strong><small>Zoeken en leerlingendossiers bekijken{{ $canManageStudents ? ', toevoegen of importeren' : '' }}</small></span><span class="feature-arrow">›</span>
                            </a>
                        @endif
                        @if ($canViewUsers)
                            <a class="feature-row" href="{{ route('school.members.index') }}">
                                <span class="feature-icon">♙</span><span class="feature-text"><strong>Personeelsleden beheren</strong><small>Accountkoppelingen en medewerkers</small></span><span class="feature-arrow">›</span>
                            </a>
                        @endif
                        @if ($canManageSettings)
                            <a class="feature-row" href="{{ route('school.locations.index') }}">
                                <span class="feature-icon alt">⌖</span><span class="feature-text"><strong>Vestigingsplaatsen</strong><small>Eigen schoollocaties beheren</small></span><span class="feature-arrow">›</span>
                            </a>
                            <a class="feature-row" href="{{ route('school.settings.edit') }}">
                                <span class="feature-icon alt">⚙</span><span class="feature-text"><strong>Schoolinstellingen</strong><small>Schoolgebonden teksten en huisstijl</small></span><span class="feature-arrow">›</span>
                            </a>
                        @endif
                        @if ($canManageRoles)
                            <a class="feature-row" href="{{ route('school.roles.index') }}">
                                <span class="feature-icon alt">⚙</span><span class="feature-text"><strong>Rollen en rechten</strong><small>Toegang instellen voor je school</small></span><span class="feature-arrow">›</span>
                            </a>
                        @endif
                        <a class="feature-row" href="{{ route('schools.select') }}">
                            <span class="feature-icon gold">⇄</span><span class="feature-text"><strong>Andere school kiezen</strong><small>Overschakelen naar jouw andere omgevingen</small></span><span class="feature-arrow">›</span>
                        </a>
                        <div class="feature-row feature-off">
                            <span class="feature-icon alt">✧</span><span class="feature-text"><strong>Evaluaties en leerdoelen</strong><small>Wordt later beschikbaar</small></span><span class="pill">Binnenkort</span>
                        </div>
                    </div>
                </div>
            </section>
            <footer class="foot">Artelynx &copy; {{ date('Y') }} · Elk talent in ontwikkeling.</footer>

@endsection
