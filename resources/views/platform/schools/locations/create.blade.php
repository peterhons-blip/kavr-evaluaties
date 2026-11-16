
@extends('layouts.platform')

@section('title', 'Nieuwe vestigingsplaats')
@section('page-title', 'Nieuwe vestigingsplaats')

@section('content')

<div class="school-location-page">

    @include('platform.schools.partials.breadcrumbs', [
        'school' => $school,
        'section' => 'Vestigingsplaatsen',
        'page' => 'Toevoegen'
    ])

    <div class="page-header">
        <h1>{{ $school->name }}</h1>
        <p>
            Beheer de gegevens en instellingen van deze school.
        </p>
    </div>

    @include('platform.schools.partials.tabs', [
        'school' => $school
    ])

    <div class="location-subheading">
        <h2>Nieuwe vestigingsplaats</h2>
        <p>
            Registreer een bijkomende locatie voor
            {{ $school->name }}.
        </p>
    </div>

    @include('platform.schools.locations.partials.form', [
        'school' => $school
    ])

</div>

@endsection

@push('styles')
<style>
    .school-location-page {
        max-width: 1000px;
    }

    .location-subheading {
        margin-bottom: 24px;
    }

    .location-subheading h2 {
        margin: 0 0 8px;
        font-size: 21px;
        font-weight: 800;
    }

    .location-subheading p {
        margin: 0;
        color: #718096;
        font-size: 13px;
        line-height: 1.7;
    }
</style>
@endpush
