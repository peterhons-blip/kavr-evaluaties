
@extends('layouts.platform')

@section('title', 'Vestigingsplaats bewerken')
@section('page-title', 'Vestigingsplaats bewerken')

@section('content')

<div class="school-location-page">

    @include('platform.schools.partials.breadcrumbs', [
        'school' => $school,
        'section' => 'Vestigingsplaatsen',
        'page' => 'Bewerken'
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
        <h2>{{ $location->name }}</h2>
        <p>
            Wijzig de gegevens van deze vestigingsplaats.
        </p>
    </div>

    @include('platform.schools.locations.partials.form', [
        'school' => $school,
        'location' => $location
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
