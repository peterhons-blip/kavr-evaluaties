
@extends('layouts.platform')

@section('title', 'Vestigingsplaatsen')
@section('page-title', 'Vestigingsplaatsen')

@section('content')

<div class="locations-container">

    {{-- Terug naar scholen --}}
    @include('platform.schools.partials.breadcrumbs', [
        'school' => $school,
        'section' => 'Vestigingsplaatsen'
    ])


    {{-- Schooltitel --}}
    <div class="page-header">
        <h1>{{ $school->name }}</h1>
        <p>
            Beheer de gegevens en instellingen van deze school.
        </p>
    </div>

    {{-- Centrale navigatie --}}
    @include('platform.schools.partials.tabs', [
        'school' => $school
    ])

    {{-- Succesmelding --}}
    @if (session('success'))
        <div class="locations-alert" role="status">
            <span class="alert-check">✓</span>
            {{ session('success') }}
        </div>
    @endif


    @if ($errors->any())
        <div class="locations-delete-error" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif


    {{-- Vestigingenoverzicht --}}
    <div class="card">

        <div class="locations-card-header">

            <div>
                <h2>Vestigingsplaatsen</h2>

                <p>
                    {{ $locations->count() }}
                    {{ $locations->count() === 1
                        ? 'geregistreerde vestigingsplaats'
                        : 'geregistreerde vestigingsplaatsen' }}
                </p>
            </div>

            <a href="{{ route(
                'platform.schools.locations.create',
                $school
            ) }}" class="btn">
                + Nieuwe vestigingsplaats
            </a>

        </div>

        <div class="locations-table-wrapper">
            <table class="locations-table">

                <thead>
                    <tr>
                        <th>Vestigingsplaats</th>
                        <th>Adres</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($locations as $location)
                        <tr>

                            <td>
                                <div class="location-name">
                                    {{ $location->name }}
                                </div>

                                <div class="location-number">
                                    @if ($location->location_number)
                                        Nr. {{ $location->location_number }}
                                    @else
                                        Geen vestigingsnummer
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="location-address">
                                    @if ($location->street)
                                        <div>{{ $location->street }}</div>
                                    @endif

                                    @if ($location->postal_code || $location->city)
                                        <div>
                                            {{ $location->postal_code }}
                                            {{ $location->city }}
                                        </div>
                                    @endif

                                    @if (
                                        !$location->street &&
                                        !$location->postal_code &&
                                        !$location->city
                                    )
                                        <span class="muted">
                                            Niet ingevuld
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if ($location->is_main_location)
                                    <span class="location-pill main">
                                        Hoofdvestiging
                                    </span>
                                @else
                                    <span class="location-pill standard">
                                        Vestiging
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($location->is_active)
                                    <span class="location-pill active">
                                        <span class="status-dot"></span>
                                        Actief
                                    </span>
                                @else
                                    <span class="location-pill inactive">
                                        <span class="status-dot"></span>
                                        Inactief
                                    </span>
                                @endif
                            </td>

                            <td class="location-actions-cell">

                                <div class="location-actions">

                                    <a href="{{ route(
                                        'platform.schools.locations.edit',
                                        [$school, $location]
                                    ) }}" class="btn btn-secondary location-edit-btn">
                                        Bewerken
                                    </a>

                                    @if ($locations->count() > 1)

                                        <form method="POST"
                                            action="{{ route(
                                                'platform.schools.locations.destroy',
                                                [$school, $location]
                                            ) }}"
                                            onsubmit="return confirm('Weet je zeker dat je deze vestigingsplaats wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.');">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="location-delete-btn"
                                                title="Vestigingsplaats verwijderen"
                                                aria-label="Verwijder {{ $location->name }}">

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    width="17"
                                                    height="17"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true">

                                                    <path d="M3 6h18"/>
                                                    <path d="M8 6V4h8v2"/>
                                                    <path d="M19 6l-1 14H6L5 6"/>
                                                    <path d="M10 10v6"/>
                                                    <path d="M14 10v6"/>

                                                </svg>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>


                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="locations-empty">

                                <div class="empty-icon">🏫</div>

                                <strong>
                                    Nog geen vestigingsplaatsen
                                </strong>

                                <p>
                                    Voeg de eerste vestigingsplaats
                                    van deze school toe.
                                </p>

                                <a href="{{ route(
                                    'platform.schools.locations.create',
                                    $school
                                ) }}" class="btn">
                                    + Eerste vestigingsplaats
                                </a>

                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

</div>

@endsection

@push('styles')
<style>
    .locations-container {
        width: 100%;
        max-width: 1000px;
    }

    .locations-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        color: #718096;
        font-size: 12px;
    }

    .locations-breadcrumb a {
        color: #328d86;
        text-decoration: none;
        font-weight: 700;
    }

    .locations-breadcrumb a:hover {
        text-decoration: underline;
    }

    .locations-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }

    .locations-card-header h2 {
        margin: 0 0 7px;
        font-size: 19px;
        font-weight: 800;
    }

    .locations-card-header p {
        margin: 0;
        color: #718096;
        font-size: 12px;
    }

    .locations-alert {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding: 15px 18px;
        background: #edf8f3;
        border: 1px solid #b9dfcf;
        border-radius: 10px;
        color: #246b52;
        font-size: 13px;
        font-weight: 600;
    }

    .alert-check {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #d1ebdf;
    }

    .locations-table-wrapper {
        overflow-x: auto;
        width: 100%;
    }

    .locations-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .locations-table th {
        padding: 15px 12px;
        border-bottom: 2px solid #e1e8ee;
        color: #718096;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
    }

    .locations-table td {
        padding: 17px 12px;
        border-bottom: 1px solid #edf0f3;
        font-size: 12px;
        vertical-align: middle;
    }

    .locations-table tbody tr:hover {
        background: #f8fafb;
    }

    .location-name {
        color: #142b45;
        font-size: 13px;
        font-weight: 700;
    }

    .location-number {
        margin-top: 6px;
        color: #718096;
        font-size: 11px;
    }

    .location-address {
        line-height: 1.8;
    }

    .muted {
        color: #98a6b7;
    }

    .location-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .location-pill.main {
        background: #e7f4f2;
        color: #2e827a;
    }

    .location-pill.standard {
        background: #f0f3f7;
        color: #64748b;
    }

    .location-pill.active {
        background: #e8f7ee;
        color: #25845b;
    }

    .location-pill.inactive {
        background: #fceeee;
        color: #b94747;
    }

    .status-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .locations-empty {
        padding: 48px 20px !important;
        text-align: center;
    }

    .empty-icon {
        margin-bottom: 15px;
        font-size: 32px;
    }

    .locations-empty strong {
        display: block;
        color: #142b45;
        font-size: 15px;
    }

    .locations-empty p {
        margin: 10px 0 20px;
        color: #718096;
        font-size: 12px;
    }


    
    /* Acties vestigingsplaatsen */

    .location-actions-cell {
        white-space: nowrap;
        width: 1%;
    }

    .location-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
    }

    .location-actions form {
        display: flex;
        margin: 0;
    }

    .location-edit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 13px;
        white-space: nowrap;
    }

    .location-delete-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 36px;
        height: 36px;
        padding: 0;

        background: #ffffff;
        color: #b94747;

        border: 1px solid #e5e7eb;
        border-radius: 8px;

        cursor: pointer;
        transition: background .2s, border-color .2s;
    }

    .location-delete-btn:hover {
        background: #fff1f2;
        border-color: #fecdd3;
    }

    .location-delete-btn:focus-visible {
        outline: 2px solid #b94747;
        outline-offset: 2px;
    }

    .location-delete-btn svg {
        display: block;
        flex-shrink: 0;
    }



    .locations-delete-error {
        margin-bottom: 24px;
        padding: 15px 18px;
        border: 1px solid #f2c7cc;
        border-radius: 10px;
        background: #fff3f4;
        color: #b84252;
        font-size: 13px;
    }



    @media (max-width: 650px) {
        .locations-card-header .btn {
            width: 100%;
        }

        .locations-table td,
        .locations-table th {
            padding: 12px 10px;
        }
    }
</style>
@endpush
