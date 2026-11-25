@extends($layout)
@section('title', $student->exists ? 'Leerling bewerken' : 'Leerling toevoegen')
@section('page-title','Leerlingen')
@section('content')
<div class="page-header"><h1>{{ $student->exists ? 'Leerling bewerken' : 'Leerling toevoegen' }}</h1><p>{{ $school->name }} · Het DKO3-stamnummer is uniek binnen de academie.</p></div>
@if($errors->any())<div class="sf-errors">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card" style="max-width:780px"><form method="POST" action="{{ route($platform ? ($student->exists ? 'platform.students.update' : 'platform.students.store') : ($student->exists ? 'school.students.update' : 'school.students.store'), ($platform ? ['school_id' => $school->id] : []) + ($student->exists ? ['student' => $student->id] : [])) }}">
@csrf @if($student->exists)@method('PUT')@endif
<div class="sf-grid">
    <label>DKO3-stamnummer<input name="dko3_stamnummer" required maxlength="32" inputmode="numeric" value="{{ old('dko3_stamnummer', $student->dko3_stamnummer) }}"><small>Als tekst opgeslagen; voorloopnullen blijven behouden.</small></label>
    <label>Voornaam<input name="first_name" required maxlength="150" value="{{ old('first_name', $student->first_name) }}"></label>
    <label>Familienaam<input name="last_name" required maxlength="150" value="{{ old('last_name', $student->last_name) }}"></label>
    <label>Dossierstatus<select name="is_active"><option value="1" @selected((string)old('is_active', $student->exists ? (int)$student->is_active : 1)==='1')>Actief dossier</option><option value="0" @selected((string)old('is_active', $student->exists ? (int)$student->is_active : 1)==='0')>Inactief dossier</option></select><small>Geen loginstatus. Inschrijvingen horen afzonderlijk bij schooljaren.</small></label>
</div>
<div class="sf-actions"><a class="btn btn-secondary" href="{{ route($platform ? 'platform.students.index' : 'school.students.index', $platform ? ['school_id' => $school->id] : []) }}">Annuleren</a><button class="btn" type="submit">Opslaan</button></div>
</form></div>
@endsection
@push('styles')<style>.sf-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.sf-grid label{display:flex;flex-direction:column;gap:7px;font-size:12px;font-weight:700}.sf-grid input,.sf-grid select{padding:12px;border:1px solid #d4dfe8;border-radius:8px;font:inherit}.sf-grid small{color:#718096;font-size:10px;font-weight:400}.sf-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:25px}.sf-errors{padding:15px;background:#fff0f0;color:#9b3232;border-radius:9px;margin-bottom:15px}@media(max-width:640px){.sf-grid{grid-template-columns:1fr}}</style>@endpush
