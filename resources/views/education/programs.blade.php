@extends($layout)
@section('title','Opleidingen, opties en leerjaren')
@section('page-title','Onderwijsstructuur')
@section('content')
<div class="page-header"><h1>Opleidingen, opties en leerjaren</h1><p>{{ $school->name }} · Structureer het onderwijsaanbod tussen domeinen en vakken. Leerjaarcodes blijven tekst, bijvoorbeeld 2.1, 3.5, C.1 en S.1.</p></div>
@if(session('success'))<div class="ep-notice">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ep-errors"><strong>Controleer de gegevens.</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="ep-links">
<a class="btn btn-secondary" href="{{ route($platform ? 'platform.education.index' : 'school.education.index', $platform ? ['school_id'=>$school->id] : []) }}">Domeinen en vakken</a>
<a class="btn btn-secondary" href="{{ route($platform ? 'platform.education.imports' : 'school.education.imports', $platform ? ['school_id'=>$school->id] : []) }}">DKO3-imports</a>
</div>
@if($platform)
<form method="GET" action="{{ route('platform.education.programs') }}" class="card ep-switch"><label>Academie<select name="school_id" onchange="this.form.submit()">@foreach($schools as $choice)<option value="{{ $choice->id }}" @selected($choice->id === $school->id)>{{ $choice->name }}{{ $choice->is_active ? '' : ' (inactief)' }}</option>@endforeach</select></label><button class="btn">Open academie</button></form>
@endif
@if($canManage)
<div class="card ep-panel"><h2>1. Opleiding of studierichting toevoegen</h2>
<form class="ep-form" method="POST" action="{{ route($platform ? 'platform.education.programs.store' : 'school.education.programs.store', $platform ? ['school_id'=>$school->id] : []) }}">@csrf
<label>Domein<select name="education_domain_id" required><option value="">Kies domein</option>@foreach($domains as $domain)<option value="{{ $domain->id }}">{{ $domain->name }}</option>@endforeach</select></label>
<label>Naam<input name="name" required maxlength="160" placeholder="Bijv. Vertolkend muzikant"></label>
<label>Type<select name="kind"><option value="regular">Regulier</option><option value="short">Kortlopend</option><option value="specialization">Specialisatie</option></select></label>
<label>Doelgroep<select name="audience"><option value="">Niet gespecificeerd</option><option value="youth">Jongeren</option><option value="adult">Volwassenen</option><option value="both">Beide</option></select></label>
<button class="btn">+ Opleiding toevoegen</button></form></div>
<div class="card ep-panel"><h2>2. Optie toevoegen</h2><p>Bijvoorbeeld Klassiek, Jazz-Pop-Rock of Verteltheater. Niet alle opleidingen hebben een optie.</p>
<form class="ep-form" method="POST" action="{{ route($platform ? 'platform.education.options.store' : 'school.education.options.store', $platform ? ['school_id'=>$school->id] : []) }}">@csrf
<label>Opleiding<select name="education_program_id" required>@foreach($programs as $program)<option value="{{ $program->id }}">{{ $program->domain?->name }} — {{ $program->name }}</option>@endforeach</select></label>
<label>Optienaam<input name="name" required maxlength="150" placeholder="Bijv. Klassiek"></label><button class="btn">+ Optie toevoegen</button></form></div>
<div class="card ep-panel"><h2>3. Graad en leerjaar toevoegen</h2>
<p>Per studierichting kunnen leerjaren verschillen: muziek volwassenen in graad 2 drie jaren, jongeren vier, Beeld graad 3 zes, en de kortlopende opleidingen gebruiken C.1 enz.</p>
<form class="ep-form" method="POST" action="{{ route($platform ? 'platform.education.years.store' : 'school.education.years.store', $platform ? ['school_id'=>$school->id] : []) }}">@csrf
<label>Opleiding<select name="education_program_id" id="ep-year-program" required><option value="">Kies opleiding</option>@foreach($programs as $program)<option value="{{ $program->id }}">{{ $program->name }}</option>@endforeach</select></label>
<label>Optie (indien van toepassing)<select name="education_option_id" id="ep-year-option"><option value="">Geen optie</option>@foreach($programs as $program)@foreach($program->options as $option)<option value="{{ $option->id }}" data-program="{{ $program->id }}">{{ $program->name }} / {{ $option->name }}</option>@endforeach @endforeach</select></label>
<label>Leerjaarcode<input name="code" required maxlength="20" placeholder="3.2 / C.1 / S.1"></label>
<label>Graad (optioneel)<input name="grade" maxlength="20" placeholder="3"></label>
<label>Leerjaarvolgnummer<input name="study_year" type="number" min="1" max="9" placeholder="2"></label>
<button class="btn">+ Leerjaar toevoegen</button></form></div>
@endif
<div class="card ep-panel"><h2>Bestaande onderwijsstructuur</h2>
@if($programs->isEmpty())<p>Nog geen opleidingen of studierichtingen voor deze academie. Begin bij stap 1.</p>@endif
@foreach($programs as $program)
<div class="ep-program">
<div class="ep-title"><strong>{{ $program->domain?->name }} / {{ $program->name }}</strong><span>{{ $program->kind }} · {{ $program->audience ?: 'alle doelgroepen' }}</span><span class="ep-status">{{ $program->is_active ? 'Actief' : 'Inactief' }}</span></div>
@if($canManage)
<form class="ep-inline" method="POST" action="{{ route($platform ? 'platform.education.programs.update' : 'school.education.programs.update', ($platform ? ['school_id'=>$school->id] : []) + ['program'=>$program->id]) }}">@csrf @method('PUT')
<select name="education_domain_id">@foreach($domains as $domain)<option value="{{ $domain->id }}" @selected($domain->id===$program->education_domain_id)>{{ $domain->name }}</option>@endforeach</select>
<input name="name" value="{{ $program->name }}" required maxlength="160" aria-label="Studierichting">
<select name="kind">@foreach(['regular'=>'Regulier','short'=>'Kortlopend','specialization'=>'Specialisatie'] as $code=>$label)<option value="{{ $code }}" @selected($program->kind===$code)>{{ $label }}</option>@endforeach</select>
<select name="audience"><option value="">Onbekend</option>@foreach(['youth'=>'Jongeren','adult'=>'Volwassenen','both'=>'Beide'] as $code=>$label)<option value="{{ $code }}" @selected($program->audience===$code)>{{ $label }}</option>@endforeach</select>
<select name="is_active"><option value="1" @selected($program->is_active)>Actief</option><option value="0" @selected(! $program->is_active)>Inactief</option></select>
<button class="btn btn-secondary">Opslaan</button></form>
@endif
<div class="ep-subheading">Opties</div>
@forelse($program->options as $option)
@if($canManage)<form class="ep-inline ep-child" method="POST" action="{{ route($platform ? 'platform.education.options.update' : 'school.education.options.update', ($platform ? ['school_id'=>$school->id] : []) + ['option'=>$option->id]) }}">@csrf @method('PUT')
<input name="name" value="{{ $option->name }}" required maxlength="150"><select name="is_active"><option value="1" @selected($option->is_active)>Actief</option><option value="0" @selected(! $option->is_active)>Inactief</option></select><button class="btn btn-secondary">Opslaan</button></form>
@else <p>{{ $option->name }} — {{ $option->is_active ? 'Actief' : 'Inactief' }}</p> @endif
@empty<p class="ep-empty">Geen opties gekoppeld.</p>@endforelse
<div class="ep-subheading">Graden en leerjaren</div>
@forelse($program->gradeYears as $year)
@if($canManage)<form class="ep-inline ep-child" method="POST" action="{{ route($platform ? 'platform.education.years.update' : 'school.education.years.update', ($platform ? ['school_id'=>$school->id] : []) + ['gradeYear'=>$year->id]) }}">@csrf @method('PUT')
<span>{{ $year->option?->name ?: 'Algemeen' }}</span><input name="code" value="{{ $year->code }}" required maxlength="20"><input name="grade" value="{{ $year->grade }}" placeholder="Graad"><input name="study_year" type="number" min="1" max="9" value="{{ $year->study_year }}" placeholder="Jaar">
<select name="is_active"><option value="1" @selected($year->is_active)>Actief</option><option value="0" @selected(! $year->is_active)>Inactief</option></select>
<button class="btn btn-secondary">Opslaan</button></form>
@else <p>{{ $year->code }} — {{ $year->option?->name ?: 'Algemeen' }} ({{ $year->is_active ? 'Actief' : 'Inactief' }})</p>@endif
@empty<p class="ep-empty">Nog geen leerjaren gekoppeld.</p>@endforelse
</div>
@endforeach
</div>
@endsection
@push('styles')<style>.ep-links{display:flex;gap:9px;margin-bottom:18px;flex-wrap:wrap}.ep-panel,.ep-switch{margin-bottom:17px}.ep-panel h2{font-size:18px;margin:0 0 12px}.ep-panel p{font-size:12px;color:#718096;line-height:1.6}.ep-switch{display:flex;align-items:end;gap:12px}.ep-form{display:flex;align-items:end;flex-wrap:wrap;gap:9px}.ep-form label,.ep-switch label{display:flex;flex:1;min-width:145px;flex-direction:column;font-size:11px;font-weight:700;gap:6px;color:#4c6276}.ep-form input,.ep-form select,.ep-switch select,.ep-inline input,.ep-inline select{height:40px;border:1px solid #d8e4eb;border-radius:8px;font:inherit;font-size:12px;padding:0 9px;background:#fff;min-width:0}.ep-form label input,.ep-form label select{width:100%}.ep-program{border:1px solid #e6edf2;border-radius:10px;padding:14px;margin:15px 0}.ep-title{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:11px}.ep-title span,.ep-status,.ep-empty{font-size:11px;color:#718096}.ep-inline{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin:9px 0}.ep-inline input,.ep-inline select{max-width:220px}.ep-inline span{font-size:12px}.ep-child{margin-left:15px}.ep-subheading{font-size:11px;font-weight:800;color:#52677e;margin:14px 0 7px}.ep-notice,.ep-errors{border-radius:8px;background:#eaf6ef;padding:13px;margin-bottom:14px;color:#286746;font-size:12px}.ep-errors{background:#fff0f0;color:#a43540}</style>@endpush
@push('scripts')<script>(function(){const pr=document.getElementById('ep-year-program'),op=document.getElementById('ep-year-option');if(!pr||!op)return;function filter(){const id=pr.value;Array.from(op.options).forEach((o,i)=>{if(i===0)return;o.hidden=o.dataset.program!==id;o.disabled=o.hidden});if(op.selectedOptions[0]?.disabled)op.value='';}pr.addEventListener('change',filter);filter();})();</script>@endpush
