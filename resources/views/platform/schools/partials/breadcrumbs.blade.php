
@php
    $section = $section ?? null;
    $page = $page ?? null;
@endphp

<nav class="school-breadcrumbs" aria-label="Broodkruimelnavigatie">

    <a href="{{ route('platform.schools.index') }}">
        Scholen
    </a>

    <span class="breadcrumb-separator">›</span>

    @if ($section === null && $page === null)

        <span aria-current="page">
            {{ $school->name }}
        </span>

    @else

        <a href="{{ route('platform.schools.edit', $school) }}">
            {{ $school->name }}
        </a>

        @if ($section !== null)

            <span class="breadcrumb-separator">›</span>

            @if ($page !== null && $section === 'Vestigingsplaatsen')

                <a href="{{ route('platform.schools.locations.index', $school) }}">
                    Vestigingsplaatsen
                </a>

            @else

                <span @if ($page === null) aria-current="page" @endif>
                    {{ $section }}
                </span>

            @endif

        @endif

        @if ($page !== null)

            <span class="breadcrumb-separator">›</span>

            <span aria-current="page">
                {{ $page }}
            </span>

        @endif

    @endif

</nav>

@once
    @push('styles')
        <style>
            .school-breadcrumbs {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 9px;
                margin-bottom: 22px;
                color: #718096;
                font-size: 12px;
                line-height: 1.6;
            }

            .school-breadcrumbs a {
                color: #328d86;
                text-decoration: none;
                font-weight: 600;
            }

            .school-breadcrumbs a:hover {
                text-decoration: underline;
            }

            .breadcrumb-separator {
                color: #a0adbb;
                font-size: 16px;
            }

            .school-breadcrumbs [aria-current="page"] {
                color: #142b45;
                font-weight: 700;
            }
        </style>
    @endpush
@endonce
