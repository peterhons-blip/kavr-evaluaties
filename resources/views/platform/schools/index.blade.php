
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


@if ($errors->any())
    <div class="school-delete-error" role="alert">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
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
                    <th>Publieke inloglink</th>
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
                                <div class="school-login-url">{{ route('school.login', $school->slug) }}</div>
                                <div class="school-login-actions">
                                    <a href="{{ route('school.login', $school->slug) }}" target="_blank" rel="noopener noreferrer" class="school-login-action">Openen</a>
                                    <button type="button" class="school-login-action school-copy-link" data-url="{{ route('school.login', $school->slug) }}">Kopiëren</button>
                                </div>
                            @else
                                <span class="school-login-muted">Inactief – geen publieke login</span>
                            @endif
                            @if ((int) $defaultLoginSchoolId === (int) $school->id)
                                <span class="school-default-badge">Standaard voor /login</span>
                            @endif
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


                        <td class="school-actions-cell">
                            <div class="school-row-actions">

                                <a href="{{ route('platform.schools.edit', $school) }}"
                                class="btn btn-secondary school-edit-btn">
                                    Bewerken
                                </a>

                                <form method="POST"
                                    action="{{ route('platform.schools.destroy', $school) }}"
                                    onsubmit="return confirm('Weet je zeker dat je deze school definitief wilt verwijderen? Dit verwijdert ook de bijhorende vestigingsplaatsen en instellingen. Deze actie kan niet ongedaan worden gemaakt.');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="school-delete-btn"
                                            title="School verwijderen"
                                            aria-label="Verwijder {{ $school->name }}">

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

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="empty-state">
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


    /* Acties in het scholenoverzicht */

    .school-actions-cell {
        width: 1%;
        white-space: nowrap;
    }

    .school-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
    }

    .school-row-actions form {
        display: flex;
        margin: 0;
    }

    .school-edit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 13px;
    }

    .school-delete-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
        color: #b94747;
        cursor: pointer;
        transition: background .2s, border-color .2s;
    }

    .school-delete-btn:hover {
        background: #fff1f2;
        border-color: #fecdd3;
    }

    .school-delete-btn:focus-visible {
        outline: 2px solid #b94747;
        outline-offset: 2px;
    }


    .school-delete-error {
        margin-bottom: 25px;
        padding: 16px 20px;
        border: 1px solid #f2c7cc;
        border-radius: 10px;
        background: #fff3f4;
        color: #b84252;
        font-size: 13px;
    }



    .school-login-url {max-width:310px; overflow-wrap:anywhere; font-size:12px; color:#445a70;}
    .school-login-actions {display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;}
    .school-login-action {border:1px solid #d7e5ea; border-radius:6px; padding:5px 9px; color:#246b75; background:#fff; cursor:pointer; font:inherit; font-size:12px; text-decoration:none;}
    .school-login-action:hover {background:#eef8f7;}
    .school-login-muted {font-size:12px; color:#718096;}
    .school-default-badge {display:inline-block; margin-top:8px; padding:4px 8px; border-radius:12px; background:#e9f7ef; color:#246b52; font-size:11px; font-weight:700;}

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

<script>
document.querySelectorAll('.school-copy-link').forEach(function(button) {
    button.addEventListener('click', async function() {
        const url = button.dataset.url;
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
            } else {
                const input = document.createElement('textarea');
                input.value = url;
                input.style.position = 'fixed';
                input.style.left = '-9999px';
                document.body.appendChild(input);
                input.select();
                if (!document.execCommand('copy')) throw new Error('Copy failed');
                input.remove();
            }
            button.textContent = 'Gekopieerd!';
            setTimeout(function() { button.textContent = 'Kopiëren'; }, 1800);
        } catch (error) {
            window.prompt('Kopieer deze inloglink:', url);
        }
    });
});
</script>

@endsection
