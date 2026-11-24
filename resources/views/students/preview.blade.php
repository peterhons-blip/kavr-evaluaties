@extends($layout)
@section('title','Importvoorbeeld leerlingen')
@section('page-title','Leerlingen')
@section('content')
<div class="page-header"><h1>Controleer de DKO3-leerlingenimport</h1><p>Alleen voor <strong>{{ $school->name }}</strong>. Er zijn nog geen databankwijzigingen uitgevoerd.</p></div>
<div class="sp-stats">
<div><strong>{{ $stats['total'] }}</strong><span>Leerlingen in bestand</span></div>
<div><strong>{{ $stats['new'] }}</strong><span>Nieuw toe te voegen</span></div>
<div><strong>{{ $stats['existing'] }}</strong><span>Al aanwezig</span></div>
<div><strong>{{ $stats['changed'] }}</strong><span>Namen te corrigeren</span></div>
</div>
<div class="card"><h2>Voorbeeld van de eerste 30 leerlingen</h2><p>Controleer school en stamnummer zorgvuldig. De volledige import wordt pas na bevestiging uitgevoerd.</p>
<div style="overflow-x:auto"><table class="sp-table"><thead><tr><th>Stamnummer</th><th>Familienaam</th><th>Voornaam</th></tr></thead>
<tbody>@foreach(array_slice($rows,0,30) as $row)<tr><td>{{ $row['dko3_stamnummer'] }}</td><td>{{ $row['last_name'] }}</td><td>{{ $row['first_name'] }}</td></tr>@endforeach</tbody></table></div>
</div>
<div class="sp-warning"><strong>Let op:</strong> er worden geen leerlingen verwijderd of gedeactiveerd. Deze import bevat uitsluitend de DKO3-identificatie en namen; vakken en lesgroepen worden later gekoppeld.</div>
<form method="POST" action="{{ route($platform ? 'platform.students.commit' : 'school.students.commit', $platform ? ['school_id' => $school->id] : []) }}" class="sp-actions">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<label><input type="checkbox" name="confirm" value="1" required> Ik bevestig de import voor {{ $school->name }}.</label>
<a class="btn btn-secondary" href="{{ route($platform ? 'platform.students.import' : 'school.students.import', $platform ? ['school_id' => $school->id] : []) }}">Annuleren</a>
<button class="btn" type="submit">Import definitief uitvoeren</button>
</form>
@endsection
@push('styles')<style>.sp-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:20px}.sp-stats>div{background:#fff;padding:20px;border:1px solid #e1e8ee;border-radius:11px}.sp-stats strong{display:block;font-size:30px}.sp-stats span{font-size:11px;color:#718096}.sp-table{width:100%;border-collapse:collapse}.sp-table td,.sp-table th{padding:11px;text-align:left;border-bottom:1px solid #e8eef2;font-size:12px}.sp-table th{text-transform:uppercase;color:#718096;font-size:10px}.sp-warning{background:#fff6e8;border:1px solid #f3deb6;padding:15px;border-radius:9px;font-size:12px;margin-top:18px}.sp-actions{display:flex;align-items:center;gap:12px;justify-content:flex-end;flex-wrap:wrap;margin-top:20px}.sp-actions label{margin-right:auto;font-size:12px;display:flex;gap:9px;align-items:center}@media(max-width:700px){.sp-stats{grid-template-columns:repeat(2,1fr)}}</style>@endpush
