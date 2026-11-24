@extends('layouts.platform')
@section('title', $user->exists ? 'Personeelslid bewerken' : 'Personeelslid toevoegen')
@section('page-title', 'Personeelsleden')
@section('content')
<div class="page-header">
    <h1>{{ $user->exists ? 'Personeelslid bewerken' : 'Nieuw personeelslid' }}</h1>
    <p>Een personeelslid heeft één algemene accountstatus, maar kan per school afzonderlijk actief of inactief zijn.</p>
</div>
@if($errors->any())
    <div class="uf-errors"><strong>Controleer het formulier:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ $user->exists ? route('platform.users.update',$user) : route('platform.users.store') }}" method="POST">
    @csrf
    @if($user->exists) @method('PUT') @endif
    <div class="card uf-card">
        <h2>Accountgegevens (voor alle scholen)</h2>
        <div class="uf-grid">
            <label>Voornaam *<input name="first_name" required maxlength="100" value="{{ old('first_name',$user->first_name) }}"></label>
            <label>Achternaam *<input name="last_name" required maxlength="100" value="{{ old('last_name',$user->last_name) }}"></label>
            <label class="uf-full">E-mailadres *<input type="email" name="email" required maxlength="255" autocomplete="off" value="{{ old('email',$user->email) }}"></label>
            <label>Telefoonnummer<input type="text" name="phone" maxlength="60" value="{{ old('phone', $user->phone) }}" placeholder="Bijvoorbeeld +32 4..."></label>
            <label>Wachtwoord (optioneel)
                <input type="password" name="password" minlength="8" autocomplete="new-password">
            </label>
            <label>Wachtwoord bevestigen<input type="password" name="password_confirmation" autocomplete="new-password"></label>
        </div>
        <p class="uf-help">Laat het wachtwoord leeg om een willekeurig beginwachtwoord te gebruiken. Activeer het account en stuur via het overzicht een beveiligde instellink. Een handmatig gekozen wachtwoord moet minstens 8 tekens bevatten.</p>
        <div class="uf-checks">
            <label><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$user->exists ? $user->is_active : false))> Algemene accountstatus: actief</label>
            <label><input type="hidden" name="is_platform_admin" value="0"><input type="checkbox" name="is_platform_admin" value="1" @checked(old('is_platform_admin',$user->is_platform_admin))> Platformadministrator</label>
        </div>
        <div class="uf-info"><strong>Let op: dit is de algemene accountstatus.</strong> Een personeelslid dat in één school inactief werd gezet, kan hier nog altijd <strong>actief</strong> zijn. Dat is normaal, want die persoon kan bij een andere school werken. Pas de status voor één school hieronder aan bij <strong>Schoolkoppelingen</strong>.</div>
        <p class="uf-help">Platformadministrator is een afzonderlijk globaal recht. Schoolrollen geven geen toegang tot het centrale platformbeheer.</p>
    </div>
    <div class="card uf-card">
        <h2>Schoolkoppelingen (status per school)</h2>
        <p class="uf-help">Vink de scholen aan waar dit personeelslid toegang mag krijgen. De schoolstatus hieronder bepaalt de toegang voor alleen die school.</p>
        @foreach($schools as $school)
            @php
                $saved = old('memberships.'.$school->id, $assigned[$school->id] ?? null);
                $selected = is_array($saved);
                $roleValue = $saved['role'] ?? '';
                // Eloquent geeft een boolean terug; (string) false is '' en niet '0'.
                // Normaliseer naar 0/1 zodat een inactieve koppeling inactief geselecteerd blijft.
                $activeValue = isset($saved['is_active']) ? (int) $saved['is_active'] : 1;
            @endphp
            <div class="uf-school {{ (string) request('school_id') === (string) $school->id ? 'uf-school-focus' : '' }}">
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
                    <label>Status bij {{ $school->name }}
                        <select data-field="is_active" name="{{ $selected ? 'memberships['.$school->id.'][is_active]' : '' }}" @disabled(!$selected)>
                            <option value="1" @selected($activeValue === 1)>Actief</option>
                            <option value="0" @selected($activeValue === 0)>Inactief</option>
                        </select>
                    </label>
                    <div class="uf-dko3-info">
                        <p><strong>DKO3-gegevens voor {{ $school->name }}</strong> – worden bij import voor deze school bijgewerkt en kunnen hier handmatig aangepast worden.</p>
                        @foreach([
                            'dko3_roles' => 'DKO3-rollen',
                            'dko3_active_assignments' => 'Actieve opdrachten',
                            'dko3_subject_groups' => 'Vakgroepen',
                            'dko3_locations' => 'Vestigingsplaatsen',
                            'dko3_subjects' => 'Vakken (huidig schooljaar)',
                        ] as $field => $label)
                            <label>{{ $label }}
                                <textarea data-field="{{ $field }}" rows="2" maxlength="10000"
                                    name="{{ $selected ? 'memberships['.$school->id.']['.$field.']' : '' }}"
                                    @disabled(!$selected)>{{ old('memberships.'.$school->id.'.'.$field, $saved[$field] ?? '') }}</textarea>
                            </label>
                        @endforeach
                    </div>
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
.uf-card{margin-bottom:22px;max-width:1050px}.uf-info{margin:16px 0;padding:14px 16px;border:1px solid #cce7e3;background:#f1faf9;border-radius:10px;color:#355b64;font-size:12px;line-height:1.7}.uf-school-focus{background:#f1faf9;margin:0 -12px;padding:16px 12px;border-radius:9px}.uf-card h2{margin:0 0 20px;padding-bottom:14px;border-bottom:1px solid #e1e8ee}.uf-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.uf-grid label,.uf-school-fields label{font-size:12px;font-weight:700;display:flex;flex-direction:column;gap:8px}.uf-grid input,.uf-school-fields select{width:100%;min-width:0;padding:12px;border:1px solid #d5dfe7;border-radius:8px;background:#fafcfd;font:inherit}.uf-full{grid-column:1/-1}.uf-help{font-size:12px;color:#718096;line-height:1.7}.uf-checks{display:flex;flex-wrap:wrap;gap:30px;margin-top:24px}.uf-checks label{display:flex;gap:9px;align-items:center;font-weight:600}.uf-school{padding:16px 0;border-bottom:1px solid #e1e8ee}.uf-school-title label{display:flex;gap:10px;align-items:center}.uf-school-fields{display:grid;grid-template-columns:2fr 1fr;gap:20px;padding:16px 0 0 24px}.uf-school-fields[hidden]{display:none} .uf-dko3-info{grid-column:1/-1;padding:13px 16px;background:#f7fafb;border:1px solid #e0ebee;border-radius:9px}.uf-dko3-info p{font-size:12px;color:#577083;margin:0 0 12px}.uf-dko3-info label{margin-top:10px}.uf-dko3-info textarea{width:100%;padding:10px;border:1px solid #d5dfe7;border-radius:8px;font:inherit;background:white}.uf-actions{display:flex;justify-content:flex-end;gap:10px;max-width:1050px;margin:22px 0}.uf-errors{padding:18px;background:#fff1f2;color:#9f1239;border-radius:10px;margin-bottom:20px}@media(max-width:650px){.uf-grid,.uf-school-fields{grid-template-columns:1fr}.uf-full{grid-column:auto}}
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
