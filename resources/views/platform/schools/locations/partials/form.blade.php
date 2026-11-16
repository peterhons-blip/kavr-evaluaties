
@php
    $editing = isset($location);
    $value = fn ($field, $default = '') =>
        old($field, $editing ? ($location->{$field} ?? $default) : $default);
@endphp

@if ($errors->any())
    <div class="location-form-alert" role="alert">
        <strong>Controleer de ingevulde gegevens.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ $editing
          ? route('platform.schools.locations.update', [$school, $location])
          : route('platform.schools.locations.store', $school) }}">

    @csrf

    @if ($editing)
        @method('PUT')
    @endif

    <div class="card location-form-card">

        <h2>Algemene gegevens</h2>

        <div class="location-form-grid">

            <div class="location-field full">
                <label for="name">Naam vestigingsplaats *</label>
                <input type="text"
                       id="name"
                       name="name"
                       maxlength="255"
                       required
                       value="{{ $value('name') }}"
                       placeholder="Bijvoorbeeld: Campus Speelhof">
            </div>

            <div class="location-field">
                <label for="location_number">
                    Vestigingsnummer
                </label>
                <input type="text"
                       id="location_number"
                       name="location_number"
                       maxlength="50"
                       value="{{ $value('location_number') }}"
                       placeholder="Optioneel">
                <small>
                    Moet uniek zijn binnen deze school.
                </small>
            </div>

        </div>

        <h2>Adresgegevens</h2>

        <div class="location-form-grid">

            <div class="location-field full">
                <label for="street">Straat en huisnummer</label>
                <input type="text"
                       id="street"
                       name="street"
                       maxlength="255"
                       value="{{ $value('street') }}">
            </div>

            <div class="location-field">
                <label for="postal_code">Postcode</label>
                <input type="text"
                       id="postal_code"
                       name="postal_code"
                       maxlength="20"
                       value="{{ $value('postal_code') }}">
            </div>

            <div class="location-field">
                <label for="city">Gemeente</label>
                <input type="text"
                       id="city"
                       name="city"
                       maxlength="255"
                       value="{{ $value('city') }}">
            </div>

        </div>

        <h2>Contactgegevens</h2>

        <div class="location-form-grid">

            <div class="location-field">
                <label for="email">E-mailadres</label>
                <input type="email"
                       id="email"
                       name="email"
                       maxlength="255"
                       value="{{ $value('email') }}">
            </div>

            <div class="location-field">
                <label for="phone">Telefoonnummer</label>
                <input type="tel"
                       id="phone"
                       name="phone"
                       maxlength="30"
                       value="{{ $value('phone') }}">
            </div>

        </div>

        <h2>Instellingen</h2>

        <div class="location-options">

            <label class="location-option">
                <input type="hidden"
                       name="is_main_location"
                       value="0">

                <input type="checkbox"
                       name="is_main_location"
                       value="1"
                       @checked((bool) $value('is_main_location', false))>

                <span>
                    <strong>Hoofdvestiging</strong>
                    <small>
                        Duid deze locatie aan als administratieve
                        hoofdvestiging van de school.
                    </small>
                </span>
            </label>

            <label class="location-option">
                <input type="hidden"
                       name="is_active"
                       value="0">

                <input type="checkbox"
                       name="is_active"
                       value="1"
                       @checked((bool) $value('is_active', true))>

                <span>
                    <strong>Actieve vestigingsplaats</strong>
                    <small>
                        Een inactieve vestiging blijft bewaard
                        voor historiek en rapportering.
                    </small>
                </span>
            </label>

        </div>

        <div class="location-form-actions">

            <a href="{{ route(
                'platform.schools.locations.index',
                $school
            ) }}" class="btn btn-secondary">
                Annuleren
            </a>

            <button type="submit" class="btn">
                {{ $editing
                    ? 'Wijzigingen opslaan'
                    : 'Vestigingsplaats toevoegen' }}
            </button>

        </div>

    </div>

</form>

@once
    @push('styles')
        <style>
            .location-form-card {
                width: 100%;
                max-width: 1000px;
            }

            .location-form-card h2 {
                margin: 0 0 22px;
                padding-bottom: 14px;
                border-bottom: 1px solid #e1e8ee;
                font-size: 19px;
            }

            .location-form-card h2:not(:first-child) {
                margin-top: 38px;
            }

            .location-form-grid {
                display: grid;
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
                gap: 22px;
            }

            .location-field {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .location-field.full {
                grid-column: 1 / -1;
            }

            .location-field label {
                font-size: 13px;
                font-weight: 700;
            }

            .location-field input {
                width: 100%;
                padding: 12px 14px;
                border: 1px solid #d5dfe7;
                border-radius: 8px;
                background: #fafcfd;
                color: #142b45;
                font: inherit;
                font-size: 13px;
            }

            .location-field input:focus {
                outline: none;
                border-color: #59aaa5;
                box-shadow: 0 0 0 3px
                    rgba(89,170,165,.15);
            }

            .location-field small,
            .location-option small {
                color: #718096;
                font-size: 11px;
                line-height: 1.6;
            }

            .location-options {
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            .location-option {
                display: flex;
                gap: 13px;
                align-items: flex-start;
                padding: 16px;
                background: #f8fafb;
                border: 1px solid #e1e8ee;
                border-radius: 10px;
                cursor: pointer;
            }

            .location-option input[type="checkbox"] {
                width: 18px;
                height: 18px;
                flex-shrink: 0;
                accent-color: #142b45;
            }

            .location-option span {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .location-option strong {
                font-size: 13px;
            }

            .location-form-actions {
                display: flex;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 35px;
                padding-top: 24px;
                border-top: 1px solid #e1e8ee;
            }

            .location-form-alert {
                margin-bottom: 22px;
                padding: 16px 20px;
                background: #fff1f2;
                border: 1px solid #fecdd3;
                border-radius: 10px;
                color: #9f1239;
                font-size: 13px;
            }

            .location-form-alert ul {
                margin-bottom: 0;
            }

            @media (max-width: 650px) {
                .location-form-grid {
                    grid-template-columns: 1fr;
                }

                .location-field.full {
                    grid-column: auto;
                }
            }
        </style>
    @endpush
@endonce
