
@extends('layouts.platform')

@section('title', 'School bewerken')
@section('page-title', 'School bewerken')

@section('content')


@include('platform.schools.partials.breadcrumbs', [
    'school' => $school,
    'section' => 'Algemene gegevens'
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



@if ($errors->any())
    <div class="card" role="alert"
         style="margin-bottom:25px; background:#fff1f2;
                border-color:#fecdd3; color:#9f1239;">
        <strong>Controleer de ingevulde gegevens.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ route('platform.schools.update', $school) }}">
    @csrf
    @method('PUT')

    <div class="card school-form">

        <h2>Algemene gegevens</h2>

        <div class="form-grid">
            <div class="form-field full-width">
                <label for="name">Schoolnaam *</label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $school->name) }}"
                       maxlength="255" required>
            </div>

            <div class="form-field">
                <label for="code">Schoolcode *</label>
                <input id="code" name="code" type="text"
                       value="{{ old('code', $school->code) }}"
                       maxlength="30" required>
                <small>Unieke interne identificatie.</small>
            </div>

            <div class="form-field">
                <label for="institution_number">
                    Instellingsnummer
                </label>
                <input id="institution_number"
                       name="institution_number" type="text"
                       value="{{ old('institution_number', $school->institution_number) }}"
                       maxlength="30">
            </div>

            <div class="form-field full-width">
                <label for="slug">URL-naam (slug) *</label>
                <input id="slug" name="slug" type="text"
                       value="{{ old('slug', $school->slug) }}"
                       maxlength="100" required>
                <small>
                    Bijvoorbeeld: kunstacademie-voeren-riemst.
                    Een wijziging verandert ook de schoollogin-URL.
                </small>
            </div>
        </div>

        <h2>Contactgegevens</h2>

        <div class="form-grid">
            <div class="form-field">
                <label for="email">E-mailadres</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $school->email) }}"
                       maxlength="255">
            </div>

            <div class="form-field">
                <label for="phone">Telefoonnummer</label>
                <input id="phone" name="phone" type="tel"
                       value="{{ old('phone', $school->phone) }}"
                       maxlength="30">
            </div>

            <div class="form-field full-width">
                <label for="website">Website</label>
                <input id="website" name="website" type="url"
                       value="{{ old('website', $school->website) }}"
                       maxlength="255"
                       placeholder="https://www.voorbeeld.be">
            </div>

            <div class="form-field full-width">
                <label for="director_name">Directeur</label>
                <input id="director_name" name="director_name"
                       type="text"
                       value="{{ old('director_name', $school->director_name) }}"
                       maxlength="255">
            </div>
        </div>

        <h2>Administratieve hoofdzetel</h2>

        <div class="form-grid">
            <div class="form-field full-width">
                <label for="street">Straat en huisnummer</label>
                <input id="street" name="street" type="text"
                       value="{{ old('street', $school->street) }}"
                       maxlength="255">
            </div>

            <div class="form-field">
                <label for="postal_code">Postcode</label>
                <input id="postal_code" name="postal_code"
                       type="text"
                       value="{{ old('postal_code', $school->postal_code) }}"
                       maxlength="20">
            </div>

            <div class="form-field">
                <label for="city">Gemeente</label>
                <input id="city" name="city" type="text"
                       value="{{ old('city', $school->city) }}"
                       maxlength="255">
            </div>

            <div class="form-field">
                <label for="country_code">Landcode *</label>
                <input id="country_code" name="country_code"
                       type="text"
                       value="{{ old('country_code', $school->country_code ?? 'BE') }}"
                       maxlength="2" required>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('platform.schools.index') }}"
               class="btn btn-secondary">
                Annuleren
            </a>

            <button type="submit" class="btn">
                Wijzigingen opslaan
            </button>
        </div>

    </div>
</form>

<style>
    .school-form {
        max-width: 1000px;
    }

    .school-form h2 {
        margin: 0 0 22px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e1e8ee;
        font-size: 19px;
    }

    .school-form h2:not(:first-child) {
        margin-top: 38px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .form-field label {
        font-size: 14px;
        font-weight: 600;
    }

    .form-field input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d5dfe7;
        border-radius: 8px;
        background: #fafcfd;
        color: #142b45;
        font: inherit;
        font-size: 14px;
    }

    .form-field input:focus {
        outline: none;
        border-color: #59aaa5;
        box-shadow: 0 0 0 3px rgba(89,170,165,.15);
    }

    .form-field small {
        color: #718096;
        font-size: 12px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 38px;
        padding-top: 25px;
        border-top: 1px solid #e1e8ee;
    }


    @media (max-width: 650px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .full-width {
            grid-column: auto;
        }
    }
</style>

@endsection
