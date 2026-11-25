@extends($layout)
@section('title','Domeinen en vakken')
@section('page-title','Domeinen en vakken')
@section('content')
<div class="page-header">
    <h1>Domeinen en vakken</h1>
    <p>Onderwijsaanbod voor {{ $school->name }}. De catalogus is schoolgebonden; bestaande vakken worden bij voorkeur inactief gezet in plaats van verwijderd.</p>
</div>
@if(session('success'))<div class="ec-message">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ec-errors"><strong>Controleer de ingevoerde gegevens:</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

@if($platform)
<form method="GET" action="{{ route('platform.education.index') }}" class="card ec-school-switch">
    <label>Academie<select name="school_id" onchange="this.form.submit()">
    @foreach($schools as $choice)<option value="{{ $choice->id }}" @selected((int)$choice->id===(int)$school->id)>{{ $choice->name }}{{ $choice->is_active ? '' : ' (inactief)' }}</option>@endforeach
    </select></label><button class="btn" type="submit">Open academie</button>
</form>
@endif

<div class="card ec-panel">
    <div class="ec-header"><h2>Domeinen</h2><p>Voorbeelden van mogelijke DKO-domeinen: Muziek, Woordkunst-drama, Dans, Beeldende en audiovisuele kunsten. Voeg uitsluitend de domeinen toe die jouw academie effectief aanbiedt.</p></div>
    @if($canManage)
    <form class="ec-add" method="POST" action="{{ route($platform ? 'platform.education.domains.store' : 'school.education.domains.store', $platform ? ['school_id'=>$school->id] : []) }}">@csrf
       <label>Naam<input name="name" required maxlength="150" placeholder="Bijv. Muziek" value="{{ old('name') }}"></label>
       <label>Code <small>(optioneel)</small><input name="code" maxlength="50" placeholder="Bijv. MUZ"></label>
       <button class="btn" type="submit">+ Domein toevoegen</button>
    </form>
    @endif
    <div class="ec-scroll"><table class="ec-table"><thead><tr><th>Domein</th><th>Vakken</th><th>Status</th>@if($canManage)<th>Beheer</th>@endif</tr></thead><tbody>
    @forelse($domains as $domain)<tr>
       <td><strong>{{ $domain->name }}</strong><small>{{ $domain->code ?: 'Geen code' }}</small></td>
       <td>{{ $domain->subjects_count }}</td>
       <td><span class="ec-pill {{ $domain->is_active ? 'ec-on' : 'ec-off' }}">{{ $domain->is_active ? 'Actief' : 'Inactief' }}</span></td>
       @if($canManage)<td>
           <form class="ec-inline" method="POST" action="{{ route($platform ? 'platform.education.domains.update' : 'school.education.domains.update', ($platform ? ['school_id'=>$school->id] : []) + ['domain'=>$domain->id]) }}">@csrf @method('PUT')
             <input name="name" value="{{ $domain->name }}" required maxlength="150" aria-label="Domeinnaam">
             <input name="code" value="{{ $domain->code }}" maxlength="50" aria-label="Domeincode" placeholder="Code">
             <select name="is_active" aria-label="Domeinstatus"><option value="1" @selected($domain->is_active)>Actief</option><option value="0" @selected(! $domain->is_active)>Inactief</option></select>
             <button class="btn btn-secondary" type="submit">Opslaan</button>
           </form>
       </td>@endif
    </tr>@empty<tr><td colspan="{{ $canManage ? 4 : 3 }}">Nog geen domeinen toegevoegd.</td></tr>@endforelse
    </tbody></table></div>
</div>

<div class="card ec-panel">
    <div class="ec-header"><h2>Vakken en instrumenten</h2><p>Vakken zijn gekoppeld aan één domein. Volgens de KA-VR-afspraak krijgt Instrument een individuele evaluatiefiche (ICJ); alle andere vakken een klassikale fiche (KCJ). Een instrument/specialisatie kan afzonderlijk vermeld worden.</p></div>
    @if($canManage)
    <form class="ec-add" method="POST" action="{{ route($platform ? 'platform.education.subjects.store' : 'school.education.subjects.store', $platform ? ['school_id'=>$school->id] : []) }}">@csrf
       <label>Domein<select name="education_domain_id" required><option value="">Kies domein</option>@foreach($domains->where('is_active',true) as $domain)<option value="{{ $domain->id }}">{{ $domain->name }}</option>@endforeach</select></label>
       <label>Vak<input name="name" required maxlength="200" placeholder="Bijv. Instrument: klassiek"></label>
       <label>Instrument / specialisatie<input name="discipline" maxlength="120" placeholder="Bijv. Piano"></label>
       <label>DKO3-code <small>(optioneel)</small><input name="code" maxlength="60" placeholder="Officiële code indien bekend"></label>
       <button class="btn" type="submit" @disabled($domains->where('is_active',true)->isEmpty())>+ Vak toevoegen</button>
    </form>
    @endif
    <div class="ec-scroll"><table class="ec-table"><thead><tr><th>Domein</th><th>Vak</th><th>Specialisatie</th><th>Evaluatie</th><th>Status</th>@if($canManage)<th>Beheer</th>@endif</tr></thead><tbody>
    @forelse($subjects as $subject)<tr>
        <td>{{ $subject->domain?->name }}</td><td><strong>{{ $subject->name }}</strong><small>{{ $subject->code ?: '' }}</small></td>
        <td>{{ $subject->discipline ?: '—' }}</td>
        <td><span class="ec-pill {{ $subject->evaluation_type === 'icj' ? 'ec-on' : '' }}">{{ strtoupper($subject->evaluation_type ?? 'kcj') }}</span></td>
        <td><span class="ec-pill {{ $subject->is_active ? 'ec-on' : 'ec-off' }}">{{ $subject->is_active ? 'Actief' : 'Inactief' }}</span></td>
        @if($canManage)<td>
            <form class="ec-inline" method="POST" action="{{ route($platform ? 'platform.education.subjects.update' : 'school.education.subjects.update', ($platform ? ['school_id'=>$school->id] : []) + ['subject'=>$subject->id]) }}">@csrf @method('PUT')
                <select name="education_domain_id" aria-label="Domein">@foreach($domains as $domain)<option value="{{ $domain->id }}" @selected($domain->id===$subject->education_domain_id)>{{ $domain->name }}</option>@endforeach</select>
                <input name="name" value="{{ $subject->name }}" required maxlength="200" aria-label="Vaknaam">
                <input name="discipline" value="{{ $subject->discipline }}" maxlength="120" aria-label="Specialisatie" placeholder="Instrument">
                <input name="code" value="{{ $subject->code }}" maxlength="60" aria-label="Code" placeholder="Code">
                <select name="is_active" aria-label="Vakstatus"><option value="1" @selected($subject->is_active)>Actief</option><option value="0" @selected(! $subject->is_active)>Inactief</option></select>
                <button class="btn btn-secondary" type="submit">Opslaan</button>
            </form>
        </td>@endif
    </tr>@empty<tr><td colspan="{{ $canManage ? 6 : 5 }}">Nog geen vakken toegevoegd.</td></tr>@endforelse
    </tbody></table></div>
    <div class="ec-pages">{{ $subjects->links('vendor.pagination.artelynx') }}</div>
</div>

<div class="card ec-panel">
    <h2>Volgende DKO3-imports</h2>
    <p>De leerlingidentiteit (stamnummer, naam en voornaam) kun je nu al afzonderlijk importeren. Voor domeinen, opleidingen en vakinschrijvingen voorzien we afzonderlijke importvoorbeelden met schooljaar en controles op ontbrekende of dubbelzinnige koppelingen zodra de werkelijke Excel-kolommen bekend zijn. Er wordt hier nog niets automatisch gekoppeld of gedeactiveerd.</p>
</div>
@endsection
@push('styles')<style>
.ec-panel{margin:0 0 21px}.ec-school-switch{margin-bottom:20px;display:flex;align-items:end;gap:15px}.ec-school-switch label{min-width:260px}
.ec-header{margin-bottom:20px}.ec-panel h2{font-size:18px;margin:0 0 7px}.ec-header p,.ec-panel>p{font-size:12px;line-height:1.8;color:#718096;margin:0 0 16px}
.ec-add{display:flex;align-items:end;gap:11px;flex-wrap:wrap;background:#f6fafb;border:1px solid #e5edf2;padding:16px;border-radius:10px;margin-bottom:16px}
.ec-add label,.ec-school-switch label{display:flex;flex-direction:column;gap:6px;font-size:11px;font-weight:750;color:#52677e;flex:1;min-width:150px}.ec-add small{font-weight:400}
.ec-add input,.ec-add select,.ec-inline input,.ec-inline select,.ec-school-switch select{height:39px;min-width:0;width:100%;border:1px solid #d5e0e8;border-radius:8px;background:#fff;padding:0 10px;font:inherit;font-size:12px;color:#142b45}
.ec-scroll{overflow-x:auto}.ec-table{width:100%;border-collapse:collapse}.ec-table th,.ec-table td{padding:13px 10px;border-bottom:1px solid #e9eef2;font-size:12px;text-align:left;vertical-align:middle}
.ec-table th{font-size:10px;text-transform:uppercase;color:#718096}.ec-table small{display:block;color:#8090a0;margin-top:4px}
.ec-inline{display:flex;gap:6px;align-items:center;min-width:420px}.ec-inline input{min-width:90px}.ec-inline select{min-width:90px}.ec-inline .btn{white-space:nowrap}
.ec-pill{font-size:10px;font-weight:700;padding:5px 9px;border-radius:15px}.ec-on{background:#e6f5ef;color:#277653}.ec-off{background:#fcecec;color:#ac4848}
.ec-message,.ec-errors{padding:14px;border-radius:9px;margin-bottom:17px;background:#eaf7ef;color:#256c50}.ec-errors{background:#fff0f0;color:#9f3540}.ec-pages{margin-top:16px}
@media(max-width:650px){.ec-school-switch{flex-direction:column;align-items:stretch}.ec-inline{min-width:400px}}
</style>@endpush
