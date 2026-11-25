@extends($layout)
@section('title','DKO3 onderwijsimports')
@section('page-title','DKO3 onderwijsimports')
@section('content')
<div class="page-header"><h1>DKO3 - inschrijvingen importeren</h1>
<p>{{ $school->name }}. Importeer leerlingen eerst apart. Daarna kun je domein- en vakinschrijvingen per schooljaar importeren.</p></div>
@if(session('success'))<div class="di-ok">{{ session('success') }}</div>@endif
@if($errors->any())<div class="di-error"><strong>Import niet uitgevoerd.</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach@if(session('import_errors'))@foreach(session('import_errors') as $err)<p>{{ $err }}</p>@endforeach@endif</div>@endif
<div class="di-grid">
@foreach([
 ['type'=>'domains','title'=>'2. Domeininschrijvingen','filename'=>'alle_inschrijvingen_2026-2027.xlsx',
  'columns'=>'stamnummer, domein', 'explanation'=>'Per leerling en schooljaar Muziek (Mu), Beeld (Be) en Woord (Wo) koppelen. Eén leerling kan meerdere domeinen volgen. Adresvelden worden genegeerd.'],
 ['type'=>'subjects','title'=>'3. Vakken per leerling','filename'=>'werklijst_allevakken.xlsx',
  'columns'=>'leerling, vak, graad, vestigingsplaats, leerkracht',
  'explanation'=>'Koppel per jaar vak, tekstcode van graad/leerjaar, vestigingsplaats en leerkracht. Leerlingen, vakken, personeelsleden en vestigingen moeten al bestaan. Dubbele of ontbrekende koppelingen blokkeren de import.'],
] as $step)
<div class="card di-panel">
<h2>{{ $step['title'] }}</h2>
<p>{{ $step['explanation'] }}</p>
<p><strong>Export:</strong> {{ $step['filename'] }}<br><strong>Kolommen:</strong> {{ $step['columns'] }}</p>
<form method="POST" enctype="multipart/form-data" action="{{ route($platform ? 'platform.education.imports.preview' : 'school.education.imports.preview', $platform ? ['school_id'=>$school->id] : []) }}">
@csrf
<input type="hidden" name="type" value="{{ $step['type'] }}">
<label>Schooljaar<input type="text" name="school_year" required pattern="20[0-9]{2}-20[0-9]{2}" value="{{ old('school_year', '2026-2027') }}" placeholder="2026-2027"></label>
<label>DKO3-bestand (XLSX/CSV)<input type="file" name="file" accept=".xlsx,.csv" required></label>
<button class="btn" type="submit">Bestand controleren</button>
</form>
</div>
@endforeach
</div>
<div class="card di-panel"><h2>Veilige import</h2><p>Beide imports zijn afzonderlijk opnieuw uitvoerbaar. Afwezigheid in een export verwijdert of deactiveert niemand. Er worden geen wijzigingen doorgevoerd zonder voorbeeldscherm en bevestiging. De vakkenimport maakt bij onbekende of dubbelzinnige koppelingen geen gok.</p>
<a class="btn btn-secondary" href="{{ route($platform ? 'platform.education.index' : 'school.education.index', $platform ? ['school_id'=>$school->id] : []) }}">Terug naar domeinen en vakken</a></div>
@endsection
@push('styles')<style>.di-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:18px}.di-panel h2{font-size:18px}.di-panel p{line-height:1.7;color:#52657b;font-size:12px}.di-panel label{display:flex;flex-direction:column;gap:7px;font-weight:700;font-size:12px;margin:12px 0}.di-panel input{height:40px;border:1px solid #d0e0e7;border-radius:8px;padding:8px}.di-panel input[type=file]{height:auto}.di-error,.di-ok{padding:14px;border-radius:8px;margin-bottom:14px;background:#fff0f0;color:#94333f}.di-ok{background:#e9f7ee;color:#216d48}@media(max-width:760px){.di-grid{grid-template-columns:1fr}}</style>@endpush
