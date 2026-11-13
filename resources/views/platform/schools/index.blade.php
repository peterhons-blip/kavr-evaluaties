
@extends('layouts.platform')

@section('title', 'Scholen')
@section('page-title', 'Scholenbeheer')

@section('content')

<div class="page-header">
    <h1>Scholen</h1>
    <p>
        Overzicht van alle scholen die aangesloten zijn
        bij het evaluatieplatform.
    </p>
</div>

<div class="card">

    <div style="display:flex; justify-content:space-between;
                align-items:center; margin-bottom:25px; gap:15px;">

        <h2 style="margin:0;">
            Aangesloten scholen
        </h2>

        <span style="color:#718096;">
            {{ $schools->total() }} scholen
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="text-align:left; border-bottom:2px solid #e1e8ee;">
                    <th style="padding:15px;">School</th>
                    <th style="padding:15px;">Instellingsnummer</th>
                    <th style="padding:15px;">Vestigingen</th>
                    <th style="padding:15px;">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($schools as $school)
                    <tr style="border-bottom:1px solid #e1e8ee;">
                        <td style="padding:15px;">
                            <strong>{{ $school->name }}</strong>
                            <div style="color:#718096; font-size:13px;
                                        margin-top:5px;">
                                {{ $school->code }}
                            </div>
                        </td>

                        <td style="padding:15px;">
                            {{ $school->institution_number ?? '—' }}
                        </td>

                        <td style="padding:15px;">
                            {{ $school->locations_count }}
                        </td>

                        <td style="padding:15px;">
                            @if ($school->is_active)
                                <span style="color:#248568; font-weight:600;">
                                    ● Actief
                                </span>
                            @else
                                <span style="color:#b94747; font-weight:600;">
                                    ● Inactief
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"
                            style="padding:30px; text-align:center;
                                   color:#718096;">
                            Er zijn nog geen scholen toegevoegd.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:25px;">
        {{ $schools->links() }}
    </div>

</div>

@endsection
