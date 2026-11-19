@extends('layouts.platform')
@section('title', $user->exists ? 'Personeelslid bewerken' : 'Personeelslid toevoegen')
@section('page-title', 'Gebruikersbeheer')
@section('content')
<div class="page-header">
    <h1>{{ $user->exists ? 'Personeelslid bewerken' : 'Nieuw personeelslid' }}</h1>
    <p>Accountgegevens en schoolkoppelingen worden afzonderlijk beheerd.</p>
</div>
@if($errors->any())
    <div class="uf-errors"><strong>Controleer het formulier:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ $user->exists ? route('platform.users.update',$user) : route('platform.users.store') }}" method="POST">
    @csrf
    @if($user->exists) @method('PUT') @endif
    <div class="card uf-card">
        <h2>Persoonlijke gegevens</h2>
        <div class="uf-grid">
            <label>Voornaam *<input name="first_name" required maxlength="100" value="{{ old('first_name',$user->first_name) }}"></label>
            <label>Achternaam *<input name="last_name" required maxlength="100" value="{{ old('last_name',$user->last_name) }}"></label>
            <label class="uf-full">E-mailadres *<input type="email" name="email" required maxlength="255" autocomplete="off" value="{{ old('email',$user->email) }}"></label>
            <label>Wachtwoord (optioneel)
                <input type="password" name="password" minlength="12" autocomplete="new-password">
            </label>
            <label>Wachtwoord bevestigen<input type="password" name="password_confirmation" autocomplete="new-password"></label>
        </div>
        <p class="uf-help">Laat het wachtwoord leeg om een willekeurig beginwachtwoord te gebruiken. Activeer het account en stuur via het overzicht een beveiligde instellink. Een handmatig gekozen wachtwoord moet minstens 12 tekens bevatten.</p>
        <div class="uf-checks">
            <label><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$user->exists ? $user->is_active : false))> Account actief</label>
            <label><input type="hidden" name="is_platform_admin" value="0"><input type="checkbox" name="is_platform_admin" value="1" @checked(old('is_platform_admin',$user->is_platform_admin))> Platformadministrator</label>
        </div>
        <p class="uf-help">Platformadministrator is een afzonderlijk globaal recht. Schoolrollen geven geen toegang tot het centrale platformbeheer.</p>
    </div>
    <div class="card uf-card">
        <h2>Schoolkoppelingen</h2>
        <p class="uf-help">Vink de scholen aan waar deze gebruiker toegang toe mag krijgen, kies telkens een eigen rol en status.</p>
        @foreach($schools as $school)
            @php
                $saved = old('memberships.'.$school->id, $assigned[$school->id] ?? null);
                $selected = is_array($saved);
                $roleValue = $saved['role'] ?? '';
                $activeValue = $saved['is_active'] ?? true;
            @endphp
            <div class="uf-school">
                <div class="uf-school-title"><label><input type="checkbox" class="uf-school-check" data-target="uf-school-{{ $school->id }}" @checked($selected)> <strong>{{ $school->name }}</strong></label></div>
                <div id="uf-school-{{ $school->id }}" class="uf-school-fields" @if(!$selected) hidden @endif>
                    <label>Schoolrol
                        <select data-field="role" name="{{ $selected ? 'memberships['.$school->id.'][role]' : '' }}" @disabled(!$selected)>
                            <option value="">Kies een rol</option>
                            @foreach($roles->get($school->id,collect()) as $role)
                                <option value="{{ $role->slug }}" @selected($roleValue === $role->slug)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Status
                        <select data-field="is_active" name="{{ $selected ? 'memberships['.$school->id.'][is_active]' : '' }}" @disabled(!$selected)>
                            <option value="1" @selected((string)$activeValue === '1')>Actief</option>
                            <option value="0" @selected((string)$activeValue === '0')>Inactief</option>
                        </select>
                    </label>
                </div>
            </div>
        @endforeach
    </div>
    <div class="uf-actions">
        <a class="btn btn-secondary" href="{{ route('platform.users.index') }}">Annuleren</a>
        <button class="btn" type="submit">Personeelslid opslaan</button>
    </div>
</form>
@endsection
@push('styles')
<style>
.uf-card{margin-bottom:22px;max-width:1050px}.uf-card h2{margin:0 0 20px;padding-bottom:14px;border-bottom:1px solid #e1e8ee}.uf-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.uf-grid label,.uf-school-fields label{font-size:12px;font-weight:700;display:flex;flex-direction:column;gap:8px}.uf-grid input,.uf-school-fields select{width:100%;min-width:0;padding:12px;border:1px solid #d5dfe7;border-radius:8px;background:#fafcfd;font:inherit}.uf-full{grid-column:1/-1}.uf-help{font-size:12px;color:#718096;line-height:1.7}.uf-checks{display:flex;flex-wrap:wrap;gap:30px;margin-top:24px}.uf-checks label{display:flex;gap:9px;align-items:center;font-weight:600}.uf-school{padding:16px 0;border-bottom:1px solid #e1e8ee}.uf-school-title label{display:flex;gap:10px;align-items:center}.uf-school-fields{display:grid;grid-template-columns:2fr 1fr;gap:20px;padding:16px 0 0 24px}.uf-school-fields[hidden]{display:none}.uf-actions{display:flex;justify-content:flex-end;gap:10px;max-width:1050px;margin:22px 0}.uf-errors{padding:18px;background:#fff1f2;color:#9f1239;border-radius:10px;margin-bottom:20px}@media(max-width:650px){.uf-grid,.uf-school-fields{grid-template-columns:1fr}.uf-full{grid-column:auto}}
</style>
@endpush
@push('scripts')
<script>
document.querySelectorAll('.uf-school-check').forEach(function(box) {
    function change() {
        const section = document.getElementById(box.dataset.target);
        const id = box.dataset.target.replace('uf-school-', '');
        section.hidden = !box.checked;
        section.querySelectorAll('[data-field]').forEach(function(field) {
            field.disabled = !box.checked;
            field.name = box.checked ? 'memberships[' + id + '][' + field.dataset.field + ']' : '';
            if (box.checked && field.dataset.field === 'role') field.required = true;
        });
    }
    box.addEventListener('change', change);
    change();
});
</script>
@endpush
