@extends('layouts.platform')
@section('title', 'Personeelsleden')
@section('page-title', 'Personeelsleden')

@section('content')
<div class="page-header">
    <h1>Personeelsleden</h1>
    <p>Zoek personeelsleden en filter op school en rol. Algemene accountstatus en schoolstatus zijn afzonderlijk.</p>
</div>

@if(session('success'))
    <div class="um-notice">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="um-notice um-notice-error">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<div class="card um-panel">
    <div class="um-heading">
        <div>
            <h2>Personeelsledenoverzicht</h2>
            <p>Eén account kan meerdere schoolgebonden rollen hebben.</p>
        </div>
        <div class="um-header-actions">
            <a class="btn btn-secondary" href="{{ route('platform.users.import') }}">↓ Importeren uit DKO3</a>
            <a class="btn" href="{{ route('platform.users.create') }}">+ Personeelslid toevoegen</a>
        </div>
    </div>

    <form method="GET" action="{{ route('platform.users.index') }}" id="um-filter-form"
          class="um-filters" role="search">
        <label class="um-field um-field-search">
            <span>Zoeken</span>
            <input type="search" name="q" id="um-search" value="{{ $search }}"
                   placeholder="Naam of e-mailadres…" autocomplete="off"
                   aria-label="Gebruikers zoeken">
        </label>

        <label class="um-field">
            <span>School</span>
            <select name="school_id" id="um-school">
                <option value="">Alle scholen</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected($schoolId === (int) $school->id)>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="um-field">
            <span>Schoolrol</span>
            <select name="role" id="um-role">
                <option value="">Alle rollen</option>
                @foreach($roles->unique('slug')->sortBy('name') as $role)
                    <option value="{{ $role->slug }}" @selected($roleFilter === $role->slug)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="um-field um-field-status">
            <span>Algemene accountstatus</span>
            <select name="status" id="um-status">
                <option value="">Alle statussen</option>
                <option value="active" @selected($statusFilter === 'active')>Actief</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Inactief</option>
            </select>
        </label>

        <label class="um-field um-field-password">
            <span>Wachtwoord</span>
            <select name="password" id="um-password">
                <option value="">Alle wachtwoordstatussen</option>
                <option value="not_set" @selected($passwordFilter === 'not_set')>Nog instellen</option>
                <option value="set" @selected($passwordFilter === 'set')>Ingesteld</option>
                <option value="unknown" @selected($passwordFilter === 'unknown')>Onbekend (oud account)</option>
            </select>
        </label>

        <label class="um-field">
            <span>Per pagina</span>
            <select name="per_page" id="um-per-page">
                <option value="20" @selected($perPage === 20)>20 personeelsleden</option>
                <option value="50" @selected($perPage === 50)>50 personeelsleden</option>
                <option value="100" @selected($perPage === 100)>100 personeelsleden</option>
            </select>
        </label>
        <div class="um-filter-actions">
            <button type="button" id="um-reset" class="btn btn-secondary">Wissen</button>
            <button type="submit" class="btn um-submit">Zoeken</button>
        </div>
    </form>

    <form id="um-bulk-form" method="POST" action="{{ route('platform.users.bulk') }}" class="um-bulk">
        @csrf
        <span id="um-selected-count"><strong>0</strong> geselecteerd</span>
        <small style="color:#637b8c">Alleen selectie op deze pagina (max. 100)</small>
        <select name="action" id="um-bulk-action" required aria-label="Bulkactie">
            <option value="">Kies bulkactie…</option>
            <option value="activate">Accounts activeren</option>
            <option value="deactivate">Accounts deactiveren</option>
            <option value="set_role">Schoolrol wijzigen</option>
            <option value="send_setup_links">Wachtwoordinstellinks versturen</option>
        </select>
        <select name="school_id" id="um-bulk-school" aria-label="School voor rolwijziging" hidden disabled>
            <option value="">Kies school</option>
            @foreach($schools as $school)
                <option value="{{ $school->id }}">{{ $school->name }}</option>
            @endforeach
        </select>
        <select name="role" id="um-bulk-role" aria-label="Nieuwe schoolrol" hidden disabled>
            <option value="">Kies rol</option>
        </select>
        <button type="submit" class="btn" id="um-bulk-apply" disabled>Toepassen</button>
    </form>
    <div id="um-feedback" class="um-feedback" aria-live="polite"></div>
    <div id="um-results">
        @include('platform.users.partials.results')
    </div>
</div>
@endsection

@push('styles')
<style>
.um-panel{min-width:0}
.um-header-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.um-bulk{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:13px 16px;background:#f1f7f8;border:1px solid #dcebec;border-radius:10px;margin:0 0 17px}
.um-bulk select{border:1px solid #d5dfe7;background:#fff;padding:9px;border-radius:8px;font:inherit;font-size:12px;max-width:220px}
.um-bulk select[hidden]{display:none}
.um-bulk span{font-size:12px;margin-right:6px;white-space:nowrap}
.um-bulk .btn:disabled{opacity:.5;cursor:not-allowed}
.um-table th:first-child,.um-table td:first-child{width:38px}
.um-person-check,#um-select-all{height:16px;width:16px;accent-color:#1b949b;cursor:pointer}
.um-heading{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:24px}
.um-heading h2{margin:0 0 5px;font-size:19px}
.um-heading p{margin:0;color:#718096;font-size:12px}
.um-filters{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:end;gap:12px;padding:19px;background:#f7fafb;border:1px solid #e5edf2;border-radius:12px;margin-bottom:20px}
.um-field{display:flex;flex-direction:column;min-width:0;gap:7px}
.um-field>span{font-size:11px;font-weight:800;color:#475d73}
.um-field input,.um-field select{height:42px;width:100%;border:1px solid #d5dfe7;border-radius:9px;background:#fff;padding:0 12px;font:inherit;font-size:12px;color:#172e4d}
.um-field input:focus,.um-field select:focus{outline:2px solid #7dc8c2;outline-offset:1px;border-color:#369e96}
.um-filter-actions{display:flex;gap:7px;align-items:center}
.um-filter-actions .btn{height:42px;white-space:nowrap}
.um-submit{display:none}
.um-feedback{font-size:11px;color:#718096;min-height:0;margin:0}
.um-feedback:not(:empty){margin-bottom:9px}
.um-results-heading{display:flex;gap:7px;align-items:center;font-size:12px;margin-bottom:12px}
.um-results-heading strong{font-size:15px;color:#142b45}
.um-results-heading span{color:#718096}
.um-table-scroll{width:100%;overflow-x:auto}
.um-table{width:100%;border-collapse:collapse}
.um-table th,.um-table td{padding:15px 12px;text-align:left;border-bottom:1px solid #e8eef2;vertical-align:middle;font-size:12px}
.um-table th{font-size:10px;letter-spacing:.4px;color:#718096;text-transform:uppercase;white-space:nowrap;background:#fbfcfd}
.um-table td small{display:block;color:#718096;margin-top:6px;font-size:11px}
.um-membership{margin:5px 0}
.um-membership strong{font-weight:700;display:block}
.um-membership span,.um-muted{color:#718096}
.um-membership em{display:block;color:#b94747;font-style:normal;font-size:10px}
.um-tag{display:inline-flex;padding:6px 10px;border-radius:20px;background:#eef2f7;color:#526278;font-weight:700;font-size:10px;white-space:nowrap}
.um-on{background:#e9f7ef;color:#247958}
.um-off{background:#fff0f0;color:#b94747}
.um-actions{display:flex;flex-wrap:wrap;gap:7px}
.um-actions form{margin:0}
.um-pagination{margin-top:20px}
.um-empty{text-align:center;padding:32px!important;color:#718096}
.um-notice{padding:15px;background:#edf8f3;border-radius:9px;color:#246b52;margin-bottom:20px}
.um-notice-error{background:#fff1f2;color:#9f1239}
.um-loading{opacity:.53;transition:opacity .15s}
@media(max-width:1250px){.um-filters{grid-template-columns:repeat(2,minmax(0,1fr))} .um-field-search{grid-column:1/-1}.um-filter-actions{justify-content:flex-end}}
@media(max-width:660px){.um-filters{grid-template-columns:1fr}.um-field-search{grid-column:auto}.um-filter-actions{justify-content:stretch}.um-filter-actions button{flex:1}}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';
    const form = document.getElementById('um-filter-form');
    const results = document.getElementById('um-results');
    const feedback = document.getElementById('um-feedback');
    const school = document.getElementById('um-school');
    const role = document.getElementById('um-role');
    const reset = document.getElementById('um-reset');
    const allRoles = @json($roles->map(fn ($r) => ['school_id' => (int) $r->school_id, 'slug' => $r->slug, 'name' => $r->name])->values());
    if (!form || !results || !window.fetch || !window.AbortController) return;

    const bulkForm = document.getElementById('um-bulk-form');
    const bulkAction = document.getElementById('um-bulk-action');
    const bulkSchool = document.getElementById('um-bulk-school');
    const bulkRole = document.getElementById('um-bulk-role');
    const bulkSubmit = document.getElementById('um-bulk-apply');
    const selection = document.getElementById('um-selected-count');
    function selectedBoxes() {
        return [...results.querySelectorAll('.um-person-check:checked')];
    }
    function updateSelection() {
        const count = selectedBoxes().length;
        selection.innerHTML = '<strong>' + count + '</strong> geselecteerd';
        bulkSubmit.disabled = count === 0 || !bulkAction.value;
        const all = results.querySelector('#um-select-all');
        const boxes = results.querySelectorAll('.um-person-check');
        if (all) {
            all.checked = boxes.length > 0 && count === boxes.length;
            all.indeterminate = count > 0 && count < boxes.length;
        }
    }
    function refreshBulkRoles() {
        const selectedSchool = bulkSchool.value;
        bulkRole.replaceChildren(new Option('Kies rol', ''));
        allRoles.filter(item => String(item.school_id) === selectedSchool)
            .forEach(item => bulkRole.add(new Option(item.name, item.slug)));
    }
    bulkAction.addEventListener('change', function () {
        const changingRole = this.value === 'set_role';
        bulkSchool.hidden = !changingRole;
        bulkRole.hidden = !changingRole;
        bulkSchool.disabled = !changingRole;
        bulkRole.disabled = !changingRole;
        bulkSchool.required = changingRole;
        bulkRole.required = changingRole;
        updateSelection();
    });
    bulkSchool.addEventListener('change', refreshBulkRoles);
    results.addEventListener('change', function (event) {
        if (event.target.id === 'um-select-all') {
            results.querySelectorAll('.um-person-check').forEach(box => box.checked = event.target.checked);
        }
        updateSelection();
    });
    bulkForm.addEventListener('submit', function (event) {
        const count = selectedBoxes().length;
        if (count === 0) {event.preventDefault(); return;}
        const action = bulkAction.value;
        const messages = {
            deactivate: 'accounts deactiveren',
            activate: 'accounts activeren',
            set_role: 'schoolrollen aanpassen',
            send_setup_links: 'wachtwoordinstellinks per e-mail versturen'
        };
        if (!confirm(count + ' geselecteerde personeelsleden: ' + (messages[action] || action) + '?')) event.preventDefault();
    });
    let debounce = null;
    let pending = null;
    let requestNumber = 0;

    function updateRoleOptions() {
        const selectedSchool = school.value;
        const previous = role.value;
        const allowed = new Map();
        allRoles.forEach(function (item) {
            if (!selectedSchool || String(item.school_id) === selectedSchool) {
                if (!allowed.has(item.slug)) allowed.set(item.slug, item.name);
            }
        });
        const options = [['', 'Alle rollen'], ...Array.from(allowed, ([value, label]) => [value, label]).sort((a, b) => a[1].localeCompare(b[1], 'nl'))];
        role.replaceChildren(...options.map(function ([value, label]) {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = label;
            return opt;
        }));
        role.value = allowed.has(previous) ? previous : '';
    }

    function currentUrl() {
        const params = new URLSearchParams(new FormData(form));
        for (const [key, value] of [...params.entries()]) {
            if (!String(value).trim()) params.delete(key);
        }
        return form.action + (params.toString() ? '?' + params.toString() : '');
    }

    async function load(url, pushHistory) {
        if (pending) pending.abort();
        pending = new AbortController();
        const id = ++requestNumber;
        results.classList.add('um-loading');
        feedback.textContent = 'Resultaten bijwerken…';
        try {
            const response = await fetch(url, {
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'},
                credentials: 'same-origin',
                signal: pending.signal
            });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const html = await response.text();
            if (id !== requestNumber) return;
            results.innerHTML = html;
            updateSelection();
            feedback.textContent = '';
            if (pushHistory) history.replaceState(null, '', url);
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (id === requestNumber) feedback.textContent = 'Zoeken is tijdelijk niet gelukt. Probeer opnieuw.';
        } finally {
            if (id === requestNumber) results.classList.remove('um-loading');
        }
    }

    function refresh() {
        clearTimeout(debounce);
        debounce = setTimeout(() => load(currentUrl(), true), 250);
    }

    form.addEventListener('input', function (event) {
        if (event.target.matches('input[type="search"]')) refresh();
    });
    form.addEventListener('change', function (event) {
        if (event.target === school) updateRoleOptions();
        refresh();
    });
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        clearTimeout(debounce);
        load(currentUrl(), true);
    });
    reset.addEventListener('click', function () {
        form.reset();
        form.querySelector('#um-search').value = '';
        school.value = '';
        role.value = '';
        form.querySelector('#um-status').value = '';
        form.querySelector('#um-password').value = '';
        form.querySelector('#um-per-page').value = '20';
        updateRoleOptions();
        refresh();
    });
    results.addEventListener('click', function (event) {
        const link = event.target.closest('.um-pagination a[href]');
        if (!link) return;
        event.preventDefault();
        clearTimeout(debounce);
        load(link.href, true);
    });
    updateRoleOptions();
    updateSelection();
})();
</script>
@endpush
