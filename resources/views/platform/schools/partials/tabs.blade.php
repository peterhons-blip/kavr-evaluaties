
<nav class="school-tabs" aria-label="Schoolbeheer">

    <a href="{{ route('platform.schools.edit', $school) }}"
       class="school-tab {{ request()->routeIs('platform.schools.edit') ? 'active' : '' }}"
       @if (request()->routeIs('platform.schools.edit'))
           aria-current="page"
       @endif>
        Algemene gegevens
    </a>

    <a href="{{ route('platform.schools.locations.index', $school) }}"
       class="school-tab {{ request()->routeIs('platform.schools.locations.*') ? 'active' : '' }}"
       @if (request()->routeIs('platform.schools.locations.*'))
           aria-current="page"
       @endif>
        Vestigingsplaatsen
    </a>

    <a href="{{ route('platform.schools.settings.edit', $school) }}"
        class="school-tab {{ request()->routeIs('platform.schools.settings.*') ? 'active' : '' }}"
        @if (request()->routeIs('platform.schools.settings.*'))
            aria-current="page"
        @endif>
            Huisstijl
    </a>

    <a href="{{ route('platform.schools.users.index', $school) }}"
       class="school-tab {{ request()->routeIs('platform.schools.users.*') ? 'active' : '' }}"
       @if(request()->routeIs('platform.schools.users.*')) aria-current="page" @endif>
        Personeelsleden
    </a>

    <a href="{{ route('platform.schools.roles.index', $school) }}"
       class="school-tab {{ request()->routeIs('platform.schools.roles.*') ? 'active' : '' }}"
       @if(request()->routeIs('platform.schools.roles.*')) aria-current="page" @endif>
        Rollen &amp; rechten
    </a>

</nav>

@once
    @push('styles')
        <style>
            .school-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 9px;
                max-width: 1000px;
                margin: 0 0 25px;
                padding-bottom: 15px;
                border-bottom: 1px solid #dce5eb;
            }

            .school-tab {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 11px 17px;
                border-radius: 8px;
                color: #536579;
                background: #e5ebf0;
                text-decoration: none;
                font-size: 12px;
                font-weight: 700;
                transition: background .2s, color .2s;
            }

            .school-tab:hover {
                background: #d6e5e7;
                color: #142b45;
            }

            .school-tab.active {
                background: #142b45;
                color: #ffffff;
            }

            .school-tab.active:hover {
                background: #142b45;
                color: #ffffff;
            }

            .school-tab.disabled {
                opacity: .45;
                cursor: default;
            }

            @media (max-width: 650px) {
                .school-tabs {
                    gap: 7px;
                }

                .school-tab {
                    padding: 10px 12px;
                }
            }
        </style>
    @endpush
@endonce
