
@extends('layouts.platform')

@section('title', 'Dashboard')
@section('page-title', 'Platformoverzicht')

@section('content')

<!-- WELKOM -->
<div class="page-header dashboard-heading">

    <div>
        <h1>
            Welkom terug, {{ auth()->user()->first_name }}!
        </h1>

        <p>
            Dit is het centrale overzicht van EvolvIT.
            Beheer hier alle aangesloten scholen en gebruikers.
        </p>
    </div>

    <a href="{{ route('platform.schools.index') }}"
       class="btn">
        Scholen beheren
    </a>

</div>

<!-- STATISTIEKEN -->
<div class="stats-grid">

    <!-- Totaal scholen -->
    <div class="stat-card">

        <div class="stat-top">
            <span class="stat-icon stat-icon-teal">🏫</span>
            <span class="stat-caption">TOTAAL</span>
        </div>

        <div class="stat-value">
            {{ number_format($stats['schools_total'], 0, ',', '.') }}
        </div>

        <div class="stat-label">
            Aangesloten scholen
        </div>

        <div class="stat-note">
            Geregistreerd in EvolvIT
        </div>

    </div>

    <!-- Actieve scholen -->
    <div class="stat-card">

        <div class="stat-top">
            <span class="stat-icon stat-icon-green">✓</span>
            <span class="stat-caption">ACTIEF</span>
        </div>

        <div class="stat-value">
            {{ number_format($stats['schools_active'], 0, ',', '.') }}
        </div>

        <div class="stat-label">
            Actieve scholen
        </div>

        <div class="stat-note">
            {{ $stats['schools_total'] - $stats['schools_active'] }}
            inactief
        </div>

    </div>

    <!-- Gebruikers -->
    <div class="stat-card">

        <div class="stat-top">
            <span class="stat-icon stat-icon-gold">♙</span>
            <span class="stat-caption">ACCOUNTS</span>
        </div>

        <div class="stat-value">
            {{ number_format($stats['users_total'], 0, ',', '.') }}
        </div>

        <div class="stat-label">
            Gebruikers
        </div>

        <div class="stat-note">
            {{ $stats['users_active'] }}
            actieve accounts
        </div>

    </div>

    <!-- Vestigingsplaatsen -->
    <div class="stat-card">

        <div class="stat-top">
            <span class="stat-icon stat-icon-pink">⌖</span>
            <span class="stat-caption">LOCATIES</span>
        </div>

        <div class="stat-value">
            {{ number_format($stats['locations_total'], 0, ',', '.') }}
        </div>

        <div class="stat-label">
            Vestigingsplaatsen
        </div>

        <div class="stat-note">
            Over alle scholen heen
        </div>

    </div>

</div>

<!-- ONDERSTE GEDEELTE -->
<div class="dashboard-grid">

    <!-- RECENTE SCHOLEN -->
    <section class="card dashboard-section">

        <div class="section-heading">

            <div>
                <h2>Recent toegevoegde scholen</h2>
                <p>De nieuwste scholen binnen EvolvIT.</p>
            </div>

            <a href="{{ route('platform.schools.index') }}"
               class="section-link">
                Alle scholen bekijken &rarr;
            </a>

        </div>

        <div class="table-scroll">

            <table class="recent-schools-table">

                <thead>
                    <tr>
                        <th>School</th>
                        <th>Toegevoegd</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($recentSchools as $school)

                        <tr>

                            <td>
                                <div class="recent-school-name">
                                    {{ $school->name }}
                                </div>

                                <div class="recent-school-code">
                                    {{ $school->code }}
                                </div>
                            </td>

                            <td>
                                {{ $school->created_at
                                    ? $school->created_at->format('d/m/Y')
                                    : '—' }}
                            </td>

                            <td>
                                @if ($school->is_active)

                                    <span class="status-pill active">
                                        <span class="status-dot"></span>
                                        Actief
                                    </span>

                                @else

                                    <span class="status-pill inactive">
                                        <span class="status-dot"></span>
                                        Inactief
                                    </span>

                                @endif
                            </td>

                            <td class="action-cell">
                                <a href="{{ route('platform.schools.edit', $school) }}"
                                   class="edit-link">
                                    Beheren
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="empty-schools">
                                Er zijn nog geen scholen toegevoegd.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

    <!-- SNELLE ACTIES -->
    <section class="card dashboard-section">

        <div class="section-heading">
            <div>
                <h2>Snelle acties</h2>
                <p>Ga rechtstreeks naar het scholenbeheer.</p>
            </div>
        </div>

        <div class="quick-actions">

            <a href="{{ route('platform.schools.create') }}"
               class="quick-action">

                <span class="quick-icon quick-icon-teal">
                    +
                </span>

                <span class="quick-text">
                    <strong>Nieuwe school</strong>
                    <small>Een school registreren</small>
                </span>

                <span class="quick-arrow">&rsaquo;</span>

            </a>

            <a href="{{ route('platform.schools.index') }}"
               class="quick-action">

                <span class="quick-icon quick-icon-blue">
                    🏫
                </span>

                <span class="quick-text">
                    <strong>Scholen beheren</strong>
                    <small>Schoolgegevens bekijken en wijzigen</small>
                </span>

                <span class="quick-arrow">&rsaquo;</span>

            </a>

            <a href="{{ route('schools.select') }}"
               class="quick-action">

                <span class="quick-icon quick-icon-gold">
                    ⇄
                </span>

                <span class="quick-text">
                    <strong>Wissel omgeving</strong>
                    <small>Overschakelen naar een school</small>
                </span>

                <span class="quick-arrow">&rsaquo;</span>

            </a>

        </div>

    </section>

</div>

@endsection

@push('styles')
<style>

    /* DASHBOARD HEADER */

    .dashboard-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    /* STATISTIEKEN */

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 26px;
    }

    .stat-card {
        min-width: 0;
        padding: 24px;
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 13px;
        box-shadow: 0 5px 20px rgba(20,43,69,.035);
        transition: transform .2s, box-shadow .2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(20,43,69,.07);
    }

    .stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
    }

    .stat-icon {
        width: 43px;
        height: 43px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        font-size: 22px;
        font-weight: 700;
    }

    .stat-icon-teal {
        background: #e4f5f2;
        color: #278a80;
    }

    .stat-icon-green {
        background: #e8f7ed;
        color: #2d9367;
    }

    .stat-icon-gold {
        background: #fff2dd;
        color: #c88b37;
    }

    .stat-icon-pink {
        background: #f9eaf0;
        color: #bb7194;
    }

    .stat-caption {
        color: #a0adbb;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .stat-value {
        color: var(--navy);
        font-size: 36px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -1px;
    }

    .stat-label {
        margin-top: 7px;
        color: var(--navy);
        font-size: 13px;
        font-weight: 700;
    }

    .stat-note {
        margin-top: 10px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.6;
    }

    /* ONDERSTE KOLOMMEN */

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
        align-items: start;
        gap: 22px;
    }

    .dashboard-section {
        min-width: 0;
    }

    .section-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 24px;
    }

    .section-heading h2 {
        margin: 0 0 7px;
        font-size: 17px;
        font-weight: 800;
    }

    .section-heading p {
        margin: 0;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .section-link {
        color: #328d86;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .section-link:hover {
        text-decoration: underline;
    }

    /* RECENTE SCHOLEN */

    .table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .recent-schools-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .recent-schools-table th {
        padding: 13px 12px;
        background: #f8fafb;
        color: #8a9aac;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
    }

    .recent-schools-table td {
        padding: 17px 12px;
        border-bottom: 1px solid #edf0f3;
        font-size: 12px;
        vertical-align: middle;
    }

    .recent-schools-table tbody tr:last-child td {
        border-bottom: none;
    }

    .recent-school-name {
        color: var(--navy);
        font-size: 12px;
        font-weight: 700;
    }

    .recent-school-code {
        margin-top: 5px;
        color: var(--muted);
        font-size: 11px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-pill.active {
        background: #e8f7ee;
        color: #25845b;
    }

    .status-pill.inactive {
        background: #fceeee;
        color: #b94747;
    }

    .status-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .action-cell {
        text-align: right;
    }

    .edit-link {
        color: #328d86;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .edit-link:hover {
        text-decoration: underline;
    }

    .empty-schools {
        padding: 35px 15px !important;
        color: var(--muted);
        text-align: center;
    }

    /* SNELLE ACTIES */

    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 11px;
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 15px;
        background: #f8fafb;
        border: 1px solid #e6edf1;
        border-radius: 10px;
        color: var(--navy);
        text-decoration: none;
        transition: background .2s, border-color .2s;
    }

    .quick-action:hover {
        background: #eef8f6;
        border-color: #b7dad5;
    }

    .quick-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 10px;
        font-size: 22px;
        font-weight: 700;
    }

    .quick-icon-teal {
        background: #e2f4f0;
        color: #298d82;
    }

    .quick-icon-blue {
        background: #e8edf4;
        color: #345e82;
    }

    .quick-icon-gold {
        background: #fff2de;
        color: #c58c39;
    }

    .quick-text {
        display: flex;
        flex: 1;
        min-width: 0;
        flex-direction: column;
        gap: 5px;
    }

    .quick-text strong {
        font-size: 12px;
        font-weight: 700;
    }

    .quick-text small {
        color: var(--muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .quick-arrow {
        color: #9daab8;
        font-size: 23px;
    }

    /* RESPONSIVE */

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .stats-grid {
            gap: 12px;
        }

        .stat-card {
            padding: 18px;
        }

        .stat-value {
            font-size: 29px;
        }

        .dashboard-heading {
            align-items: flex-start;
        }

        .dashboard-heading .btn {
            width: 100%;
        }

        .section-heading {
            flex-direction: column;
        }
    }

</style>
@endpush
