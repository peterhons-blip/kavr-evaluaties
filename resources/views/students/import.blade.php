@extends($layout)
@section('title','Leerlingen importeren')
@section('page-title','Leerlingen')
@section('content')
<div class="page-header"><h1>Leerlingen importeren uit DKO3</h1><p>School: <strong>{{ $school->name }}</strong>. Controleer eerst een voorbeeld voordat gegevens worden aangepast.</p></div>
@if($errors->any())<div class="si-errors"><strong>Er is niets geïmporteerd.</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
@if(session('import_errors'))<ul>@foreach(session('import_errors') as $line)<li>{{ $line }}</li>@endforeach</ul>@endif</div>@endif
<div class="card" style="max-width:860px">
<form method="POST" action="{{ route($platform ? 'platform.students.preview' : 'school.students.preview', $platform ? ['school_id' => $school->id] : []) }}" enctype="multipart/form-data">@csrf
<h2>1. Kies de DKO3-export</h2>
<p>Gebruik <strong>alleleerlingen_2026-2027.xlsx</strong> of een volgende DKO3-export. De vereiste kolommen zijn <code>stamnummer</code>, <code>naam</code> en <code>voornaam</code>. XLSX/CSV, maximaal 5 MB.</p>
<input type="file" name="file" accept=".xlsx,.csv" required>
<div class="si-info"><strong>Veilige standaard</strong><p>Nieuwe leerlingen worden toegevoegd en bestaande namen worden bijgewerkt op basis van stamnummer binnen deze school. Leerlingen die niet in het bestand staan, blijven ongewijzigd. Er worden geen leerlingen of evaluaties verwijderd of gedeactiveerd. Een al manueel gedeactiveerde leerling wordt niet automatisch geactiveerd.</p></div>
<div class="si-actions"><a class="btn btn-secondary" href="{{ route($platform ? 'platform.students.index' : 'school.students.index', $platform ? ['school_id' => $school->id] : []) }}">Annuleren</a><button class="btn" type="submit">Bestand controleren →</button></div>
</form>
</div>
@endsection
@push('styles')<style>.si-errors{padding:16px;background:#fff0f0;color:#9b3232;border-radius:10px;margin-bottom:15px}.card p{line-height:1.8;color:#718096;font-size:12px}.card input[type=file]{display:block;width:100%;border:1px solid #d4dfe8;border-radius:9px;padding:13px;margin:16px 0 22px}.si-info{background:#edf8f7;border:1px solid #d5eae8;padding:17px;border-radius:10px;font-size:12px}.si-info p{margin:6px 0 0}.si-actions{display:flex;justify-content:flex-end;gap:12px;margin-top:20px}</style>@endpush
