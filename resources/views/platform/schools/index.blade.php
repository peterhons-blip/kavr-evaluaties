
@extends('layouts.platform')

@section('title', 'Scholen')
@section('page-title', 'Scholenbeheer')

@section('content')

{{-- PAGINATITEL --}}
<div class="page-header">
    <h1>Scholen</h1>
    <p>
        Overzicht van alle scholen die aangesloten zijn
        bij het evaluatieplatform.
    </p>
</div>

{{-- SUCCESMELDING NA TOEVOEGEN OF WIJZIGEN --}}
@if (session('success'))
    <div class="alert-success" role="status">
        <span class="alert-icon">✓</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- SCHOLENOVERZICHT --}}
<div class="card">

    {{-- TITEL EN KNOP NIEUWE SCHOOL --}}
    <div class="card-header">

        <div>
            <h2>Aangesloten scholen</h2>

            <span class="school-count">
                {{ $schools->total() }}
                {{ $schools->total() === 1 ? 'school' : 'scholen' }}
            </span>
        </div>

        <a href="{{ route('platform.schools.create') }}"
           class="btn">
            + Nieuwe school
        </a>

    </div>

    {{-- TABEL --}}
    <div class="table-wrapper">

        <table class="schools-table">

            <thead>
                <tr>
                    <th>School</th>
                    <th>Instellingsnummer</th>
                    <th>Vestigingen</th>
                    <th>Status</th>
                    <th>Acties</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($schools as $school)

                    <tr>
                        <td>
                            <div class="school-name">
                                {{ $school->name }}
                            </div>

                            <div class="school-code">
                                {{ $school->code }}
                            </div>
                        </td>

                        <td>
                            {{ $school->institution_number ?? '—' }}
                        </td>

                        <td>
                            {{ $school->locations_count }}
                        </td>

                        <td>
                            @if ($school->is_active)
                                <span class="status status-active">
                                    <span class="status-dot"></span>
                                    Actief
                                </span>
                            @else
                                <span class="status status-inactive">
                                    <span class="status-dot"></span>
                                    Inactief
                                </span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('platform.schools.edit', $school) }}"
                            class="btn btn-secondary">
                                Bewerken
                            </a>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="empty-state">
                            Er zijn nog geen scholen toegevoegd.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- PAGINERING --}}
    @if ($schools->hasPages())
        <div class="pagination-wrapper">
            {{ $schools->links() }}
        </div>
    @endif

</div>

<style>
    /* Succesmelding */
    .alert-success {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        margin-bottom: 25px;
        background: #edf8f3;
        border: 1px solid #b9dfcf;
        border-radius: 10px;
        color: #246b52;
        font-size: 14px;
        font-weight: 500;
    }

    .alert-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #d1ebdf;
        color: #246b52;
        font-size: 16px;
        font-weight: 700;
    }

    /* Bovenkant kaart */
    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }

    .card-header h2 {
        margin: 0 0 6px;
        font-size: 20px;
    }

    .school-count {
        color: #718096;
        font-size: 14px;
    }

    /* Tabel */
    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .schools-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .schools-table th {
        padding: 16px;
        border-bottom: 2px solid #e1e8ee;
        color: #718096;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
    }

    .schools-table td {
        padding: 18px 16px;
        border-bottom: 1px solid #edf0f3;
        font-size: 14px;
        vertical-align: middle;
    }

    .schools-table tbody tr:hover {
        background: #f8fbfc;
    }

    .school-name {
        font-weight: 700;
        color: #142b45;
        font-size: 14px;
    }

    .school-code {
        color: #718096;
        font-size: 12px;
        margin-top: 5px;
    }

    /* Status */
    .status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-active {
        background: #e9f7ef;
        color: #248568;
    }

    .status-inactive {
        background: #fceeee;
        color: #b94747;
    }

    /* Lege tabel */
    .empty-state {
        padding: 40px !important;
        text-align: center;
        color: #718096;
    }

    /* Paginering */
    .pagination-wrapper {
        margin-top: 25px;
    }

    /* Smartphone */
    @media (max-width: 650px) {
        .card-header {
            align-items: stretch;
        }

        .card-header .btn {
            width: 100%;
            text-align: center;
        }

        .schools-table th,
        .schools-table td {
            padding: 13px 10px;
        }
    }
</style>

@endsection
