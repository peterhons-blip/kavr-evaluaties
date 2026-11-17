
@extends('layouts.platform')

@section('title', 'Schoolinstellingen')
@section('page-title', 'Huisstijl en instellingen')

@section('content')

<div class="school-settings-page">

    {{-- Broodkruimelnavigatie --}}
    @include('platform.schools.partials.breadcrumbs', [
        'school' => $school,
        'section' => 'Huisstijl'
    ])

    {{-- Schooltitel --}}
    <div class="page-header">
        <h1>{{ $school->name }}</h1>

        <p>
            Beheer de gegevens en instellingen van deze school.
        </p>
    </div>

    {{-- Tabbladen --}}
    @include('platform.schools.partials.tabs', [
        'school' => $school
    ])

    {{-- Succesmelding --}}
    @if (session('success'))
        <div class="settings-success" role="status">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Foutmeldingen --}}
    @if ($errors->any())
        <div class="settings-errors" role="alert">
            <strong>Controleer de ingevulde gegevens.</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="settings-heading">
        <div>
            <h2>Huisstijl en instellingen</h2>
            <p>
                Pas de uitstraling van de school aan binnen EvolvIT.
            </p>
        </div>

    <a href="{{ route('platform.schools.login-preview', $school) }}"
    class="btn btn-secondary"
    target="_blank"
    rel="noopener noreferrer">
        Bekijk loginpagina ↗
    </a>
    </div>

    <form method="POST"
          action="{{ route('platform.schools.settings.update', $school) }}"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        {{-- LOGINPAGINA --}}
        <div class="card settings-card">

            <h2>Teksten op de loginpagina</h2>

            <div class="settings-field">
                <label for="slogan">Slogan</label>

                <input
                    id="slogan"
                    type="text"
                    name="slogan"
                    maxlength="255"
                    value="{{ old('slogan', $school->settings?->slogan) }}"
                    placeholder="Bijvoorbeeld: Ruimte voor talent.">
            </div>

            <div class="settings-field">
                <label for="subtitle">Ondertitel</label>

                <input
                    id="subtitle"
                    type="text"
                    name="subtitle"
                    maxlength="255"
                    value="{{ old('subtitle', $school->settings?->subtitle) }}"
                    placeholder="Bijvoorbeeld: Beeld. Muziek. Theater.">
            </div>

            <div class="settings-field">
                <label for="login_description">
                    Beschrijving
                </label>

                <textarea
                    id="login_description"
                    name="login_description"
                    maxlength="2000"
                    rows="4"
                    placeholder="Korte beschrijving voor de loginpagina">{{ old('login_description', $school->settings?->login_description) }}</textarea>
            </div>

        </div>

        {{-- KLEUREN --}}
        <div class="card settings-card">

            <h2>Schoolkleuren</h2>

            <p class="settings-help">
                Deze kleuren bepalen de uitstraling van
                de schoolspecifieke loginpagina.
            </p>

            <div class="color-settings-grid">

                @foreach ([
                    'primary_color' => [
                        'label' => 'Primaire kleur',
                        'default' => '#142B45'
                    ],
                    'secondary_color' => [
                        'label' => 'Secundaire kleur',
                        'default' => '#59AAA5'
                    ],
                    'accent_color' => [
                        'label' => 'Accentkleur',
                        'default' => '#E5A65B'
                    ]
                ] as $field => $config)

                    @php
                        $color = old(
                            $field,
                            $school->settings?->{$field}
                                ?? $config['default']
                        );
                    @endphp

                    <div class="settings-field">
                        <label for="{{ $field }}">
                            {{ $config['label'] }}
                        </label>

                        <div class="color-input-row">

                            <input
                                type="color"
                                id="{{ $field }}_picker"
                                value="{{ $color }}"
                                data-color-target="{{ $field }}"
                                aria-label="{{ $config['label'] }} kiezen">

                            <input
                                type="text"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                value="{{ $color }}"
                                maxlength="7"
                                pattern="#[0-9a-fA-F]{6}"
                                class="color-hex"
                                required>

                        </div>
                    </div>

                @endforeach

            </div>

            {{-- Voorbeeldweergave --}}
            <div class="settings-preview">

                <div class="preview-title">
                    Voorbeeld van de schoolkleuren
                </div>

                <div id="color-preview" class="preview-panel">

                    <div class="preview-school">
                        {{ $school->name }}
                    </div>

                    <div id="preview-slogan" class="preview-slogan">
                        {{ old(
                            'slogan',
                            $school->settings?->slogan
                                ?? 'Ruimte voor talent.'
                        ) }}
                    </div>

                    <div id="preview-subtitle" class="preview-subtitle">
                        {{ old(
                            'subtitle',
                            $school->settings?->subtitle ?? ''
                        ) }}
                    </div>

                    <div class="preview-bars">
                        <span id="preview-accent"></span>
                        <span id="preview-secondary"></span>
                    </div>

                </div>

                <p class="settings-help">
                    Dit is een vereenvoudigd voorbeeld.
                    Gebruik 'Bekijk loginpagina' om de
                    volledige pagina te controleren na opslaan.
                </p>

            </div>

        </div>

        {{-- SCHOOLLOGO --}}
        <div class="card settings-card">

            <h2>Schoollogo</h2>

            <p class="settings-help">
                Optioneel logo van de school.
                Het EvolvIT-logo blijft de vaste platformidentiteit.
            </p>

            @if ($school->settings?->logo_path)

                <div class="current-logo">
                    <img
                        src="{{ asset('storage/' . $school->settings->logo_path) }}"
                        alt="Huidig schoollogo">

                    <label class="remove-logo">
                        <input
                            type="checkbox"
                            name="remove_logo"
                            value="1"
                            @checked(old('remove_logo'))>

                        Huidig logo verwijderen
                    </label>
                </div>

            @else

                <p class="settings-help">
                    Er is nog geen schoollogo geüpload.
                </p>

            @endif

            <div class="settings-field">
                <label for="logo">
                    {{ $school->settings?->logo_path
                        ? 'Logo vervangen'
                        : 'Logo uploaden' }}
                </label>

                <input
                    type="file"
                    id="logo"
                    name="logo"
                    accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">

                <small>
                    PNG, JPEG of WebP. Maximaal 2 MB.
                </small>
            </div>

        </div>

        {{-- ALGEMENE INSTELLINGEN --}}
        <div class="card settings-card">

            <h2>Algemene instellingen</h2>

            <div class="settings-grid">

                <div class="settings-field">
                    <label for="language">Taal</label>

                    <select id="language" name="language" required>
                        @foreach ([
                            'nl' => 'Nederlands',
                            'fr' => 'Frans',
                            'en' => 'Engels',
                            'de' => 'Duits'
                        ] as $code => $label)

                            <option value="{{ $code }}"
                                @selected(old(
                                    'language',
                                    $school->settings?->language ?? 'nl'
                                ) === $code)>
                                {{ $label }}
                            </option>

                        @endforeach
                    </select>
                </div>

                <div class="settings-field">
                    <label for="timezone">Tijdzone</label>

                    <select id="timezone" name="timezone" required>
                        @foreach ([
                            'Europe/Brussels',
                            'Europe/Amsterdam',
                            'Europe/Paris',
                            'Europe/Berlin',
                            'UTC'
                        ] as $timezone)

                            <option value="{{ $timezone }}"
                                @selected(old(
                                    'timezone',
                                    $school->settings?->timezone
                                        ?? 'Europe/Brussels'
                                ) === $timezone)>
                                {{ $timezone }}
                            </option>

                        @endforeach
                    </select>
                </div>

            </div>

            <p class="settings-help">
                Deze instellingen worden opgeslagen per school.
                De meertalige interface bouwen we later verder uit.
            </p>

        </div>

        {{-- OPSLAAN --}}
        <div class="settings-actions">

            <a href="{{ route('platform.schools.index') }}"
               class="btn btn-secondary">
                Terug naar scholen
            </a>

            <button type="submit" class="btn">
                Instellingen opslaan
            </button>

        </div>

    </form>

</div>

@endsection

@push('styles')
<style>
    .school-settings-page {
        width: 100%;
        max-width: 1000px;
    }

    .settings-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
        margin: 0 0 22px;
    }

    .settings-heading h2 {
        margin: 0 0 7px;
        font-size: 21px;
    }

    .settings-heading p,
    .settings-help {
        margin: 0;
        color: #718096;
        font-size: 12px;
        line-height: 1.8;
    }

    .settings-card {
        margin-bottom: 22px;
    }

    .settings-card h2 {
        margin: 0 0 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e1e8ee;
        font-size: 18px;
    }

    .settings-card > .settings-help {
        margin-bottom: 20px;
    }

    .settings-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 20px;
    }

    .settings-field:last-child {
        margin-bottom: 0;
    }

    .settings-field label {
        font-size: 13px;
        font-weight: 700;
    }

    .settings-field input:not([type="color"]):not([type="checkbox"]),
    .settings-field textarea,
    .settings-field select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d5dfe7;
        border-radius: 8px;
        background: #fafcfd;
        color: #142b45;
        font: inherit;
        font-size: 13px;
    }

    .settings-field input:focus,
    .settings-field textarea:focus,
    .settings-field select:focus {
        outline: 2px solid #59aaa5;
        outline-offset: 1px;
    }

    .settings-field small {
        color: #718096;
        font-size: 11px;
    }

    .settings-grid,
    .color-settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .color-settings-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .color-input-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .color-input-row input[type="color"] {
        width: 48px;
        height: 45px;
        padding: 4px;
        border: 1px solid #d5dfe7;
        border-radius: 8px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .color-input-row .color-hex {
        min-width: 0;
    }

    /* LIVE VOORBEELD */

    .settings-preview {
        margin-top: 10px;
        padding-top: 24px;
        border-top: 1px solid #e1e8ee;
    }

    .preview-title {
        margin-bottom: 13px;
        font-size: 13px;
        font-weight: 700;
    }

    .preview-panel {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 14px;
        min-height: 230px;
        margin-bottom: 13px;
        padding: 30px;
        border-radius: 12px;
        background: #142b45;
        color: #ffffff;
    }

    .preview-school {
        color: #59aaa5;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .preview-slogan {
        font-size: 26px;
        font-weight: 800;
        line-height: 1.3;
    }

    .preview-subtitle {
        font-size: 13px;
        line-height: 1.6;
    }

    .preview-bars {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .preview-bars span {
        display: block;
        width: 90px;
        height: 5px;
        border-radius: 10px;
    }

    /* LOGO */

    .current-logo {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid #e1e8ee;
        border-radius: 10px;
        background: #f8fafb;
    }

    .current-logo img {
        display: block;
        max-width: 230px;
        max-height: 130px;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .remove-logo {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 12px;
        cursor: pointer;
    }

    /* MELDINGEN EN KNOPPEN */

    .settings-success,
    .settings-errors {
        margin-bottom: 22px;
        padding: 16px 20px;
        border-radius: 10px;
        font-size: 13px;
    }

    .settings-success {
        background: #edf8f3;
        border: 1px solid #b9dfcf;
        color: #246b52;
    }

    .settings-errors {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #9f1239;
    }

    .settings-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 12px;
        margin: 25px 0;
    }

    @media (max-width: 700px) {
        .settings-grid,
        .color-settings-grid {
            grid-template-columns: 1fr;
        }

        .settings-heading .btn {
            width: 100%;
        }

        .preview-panel {
            padding: 22px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const primary = document.getElementById('primary_color');
    const secondary = document.getElementById('secondary_color');
    const accent = document.getElementById('accent_color');

    const preview = document.getElementById('color-preview');
    const previewSchool = document.querySelector('.preview-school');
    const previewAccent = document.getElementById('preview-accent');
    const previewSecondary = document.getElementById('preview-secondary');

    const slogan = document.getElementById('slogan');
    const subtitle = document.getElementById('subtitle');

    const previewSlogan = document.getElementById('preview-slogan');
    const previewSubtitle = document.getElementById('preview-subtitle');

    const validHex = value => /^#[0-9a-fA-F]{6}$/.test(value);

    function updatePreview() {
        if (validHex(primary.value)) {
            preview.style.backgroundColor = primary.value;
        }

        if (validHex(secondary.value)) {
            previewSchool.style.color = secondary.value;
            previewSecondary.style.backgroundColor = secondary.value;
        }

        if (validHex(accent.value)) {
            previewAccent.style.backgroundColor = accent.value;
        }

        previewSlogan.textContent = slogan.value;
        previewSubtitle.textContent = subtitle.value;
    }

    document.querySelectorAll('[data-color-target]')
        .forEach(function (picker) {

            const textInput = document.getElementById(
                picker.dataset.colorTarget
            );

            picker.addEventListener('input', function () {
                textInput.value = picker.value.toUpperCase();
                updatePreview();
            });

            textInput.addEventListener('input', function () {
                if (validHex(textInput.value)) {
                    picker.value = textInput.value;
                }

                updatePreview();
            });
        });

    [slogan, subtitle].forEach(function (input) {
        input.addEventListener('input', updatePreview);
    });

    updatePreview();

});
</script>
@endpush
