
@extends('layouts.platform')

@section('title', 'Dashboard')
@section('page-title', 'Platformbeheer')

@section('content')

    <div class="page-header">
        <h1>Welkom, {{ auth()->user()->first_name }}!</h1>
        <p>
            Beheer vanuit deze omgeving het volledige
            evaluatieplatform.
        </p>
    </div>

    <div class="card">
        <h2>Platformoverzicht</h2>

        <p>
            Hier vind je binnenkort een overzicht van
            alle aangesloten scholen, gebruikers
            en platforminstellingen.
        </p>
    </div>

@endsection
