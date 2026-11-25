@extends($layout)
@section('title','Leerlingen')
@section('page-title','Leerlingen')
@section('content')
<div class="page-header"><h1>Leerlingen</h1><p>DKO3-leerlingendossiers van {{ $school->name }}. Stamnummer is uniek binnen deze academie.</p></div>
@if(session('success'))<div class="st-notice">{{ session('success') }}</div>@endif
@if($errors->any())<div class="st-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card">
    <div class="st-heading"><div><h2>Leerlingenoverzicht</h2><p>{{ $students->total() }} leerlingen volgens de huidige filters</p></div>
        @if($canManage)<div class="st-actions">
            <a class="btn btn-secondary" href="{{ route($platform ? 'platform.students.import' : 'school.students.import', $platform ? ['school_id' => $school->id] : []) }}">↓ Importeren uit DKO3</a>
            <a class="btn" href="{{ route($platform ? 'platform.students.create' : 'school.students.create', $platform ? ['school_id' => $school->id] : []) }}">+ Leerling toevoegen</a>
        </div>@endif
    </div>
    <form method="GET" class="st-filters" action="{{ route($platform ? 'platform.students.index' : 'school.students.index') }}">
        @if($platform)<label>Academie<select name="school_id" onchange="this.form.submit()">@foreach($schools as $choice)<option value="{{ $choice->id }}" @selected($choice->id === $school->id)>{{ $choice->name }}</option>@endforeach</select></label>@endif
        <label>Zoeken<input type="search" name="q" value="{{ $search }}" placeholder="Naam, voornaam of stamnummer"></label>
        <label>Status<select name="status"><option value="active" @selected($status==='active')>Actief</option><option value="inactive" @selected($status==='inactive')>Inactief</option><option value="all" @selected($status==='all')>Alle leerlingen</option></select></label>
        <button class="btn" type="submit">Zoeken</button>
    </form>
    <div class="st-scroll"><table class="st-table">
        <thead><tr><th>Stamnummer</th><th>Familienaam</th><th>Voornaam</th><th>Status</th><th>Schooljaren</th>@if($canManage)<th>Acties</th>@endif</tr></thead>
        <tbody>@forelse($students as $student)<tr>
            <td><strong>{{ $student->dko3_stamnummer }}</strong></td>
            <td>{{ $student->last_name }}</td><td>{{ $student->first_name }}</td>
            <td><span class="st-pill {{ $student->is_active ? 'st-on' : 'st-off' }}">{{ $student->is_active ? 'Dossier actief' : 'Dossier inactief' }}</span></td>
            <td>@forelse($student->schoolYears as $year)<span class="st-year">{{ $year->school_year }} ({{ $year->status === 'enrolled' ? 'ingeschreven' : 'afgesloten' }})</span>@empty<span class="st-muted">Nog niet gekoppeld</span>@endforelse</td>
            @if($canManage)<td><a class="btn btn-secondary" href="{{ route($platform ? 'platform.students.edit' : 'school.students.edit', ($platform ? ['school_id' => $school->id] : []) + ['student' => $student->id]) }}">Bewerken</a></td>@endif
        </tr>@empty<tr><td colspan="{{ $canManage ? 6 : 5 }}" class="st-empty">Geen leerlingen gevonden. Importeer een DKO3-export of pas je filters aan.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="st-pages">{{ $students->links('vendor.pagination.artelynx') }}</div>
</div>
@endsection
@push('styles')
<style>
.st-notice,.st-error{border-radius:9px;padding:13px 17px;margin-bottom:17px;background:#eaf7ef;color:#236e4d}.st-error{background:#fff0f0;color:#a32d39}
.st-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-bottom:20px}.st-heading h2{margin:0;font-size:18px}.st-heading p{margin:5px 0;color:#718096;font-size:12px}.st-actions{display:flex;gap:9px;flex-wrap:wrap}
.st-filters{display:flex;align-items:end;gap:12px;flex-wrap:wrap;background:#f6fafb;padding:17px;border:1px solid #e3ecf0;border-radius:10px;margin-bottom:20px}
.st-filters label{display:flex;flex:1;min-width:170px;flex-direction:column;gap:6px;color:#475d73;font-size:11px;font-weight:700}
.st-filters input,.st-filters select{height:41px;border:1px solid #cddbe4;border-radius:8px;padding:0 12px;font:inherit;color:#142b45;background:white}
.st-scroll{overflow-x:auto}.st-table{border-collapse:collapse;width:100%}.st-table th,.st-table td{padding:13px 11px;border-bottom:1px solid #edf1f5;text-align:left;font-size:12px}.st-table th{color:#718096;font-size:10px;text-transform:uppercase}.st-table tr:hover td{background:#f9fbfc}
.st-year{display:inline-block;margin:2px 4px 2px 0;font-size:10px;padding:4px 8px;background:#edf3f6;border-radius:9px}.st-muted{color:#718096}.st-pill{display:inline-block;font-size:10px;font-weight:800;border-radius:15px;padding:5px 9px}.st-on{background:#e6f4ed;color:#277653}.st-off{background:#fcecec;color:#ac4848}.st-empty{text-align:center;padding:30px!important;color:#718096}.st-pages{margin-top:19px}
</style>
@endpush
