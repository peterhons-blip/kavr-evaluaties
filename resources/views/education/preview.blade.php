@extends($layout)
@section('title','Controle DKO3-import')
@section('page-title','DKO3 onderwijsimports')
@section('content')
<div class="page-header"><h1>Controleer {{ $type === 'domains' ? 'domeininschrijvingen' : 'vakinschrijvingen' }}</h1>
<p>{{ $school->name }} · {{ $schoolYear }} · {{ $total }} regels. Nog niets gewijzigd.</p></div>
@if($errors)<div class="dp-error"><strong>{{ $blocked }} regels vragen aandacht.</strong>
<p>De import is geblokkeerd zolang koppelingen niet eenduidig zijn. Controleer de onderstaande regels, maak ontbrekende vakken/vestigingen of personeelsleden eerst aan of corrigeer de Excel-export.</p>
@foreach($errors as $error)<p>{{ $error }}</p>@endforeach
@if($blocked > count($errors))<p>En nog {{ $blocked - count($errors) }} andere regels met problemen.</p>@endif
</div>@else
<div class="dp-ok">Alle {{ $total }} regels konden eenduidig gekoppeld worden. Je kunt de import bevestigen.</div>
@endif
<div class="card"><h2>Voorbeeld - eerste 40 regels</h2>
<div class="dp-scroll"><table><thead><tr><th>Rij</th><th>Leerling</th><th>{{ $type === 'domains' ? 'Domein' : 'Vak' }}</th>@if($type === 'subjects')<th>Graad</th><th>Vestiging</th><th>Leerkracht</th>@endif<th>Controle</th></tr></thead><tbody>
@foreach(array_slice($rows, 0, 40) as $row)
<tr><td>{{ $row['source']['line'] }}</td><td>{{ $type === 'domains' ? $row['source']['number'] : $row['source']['student'] }}</td>
<td>{{ $type === 'domains' ? $row['source']['domain'] : $row['source']['subject'] }}</td>
@if($type==='subjects')<td>{{ $row['source']['grade'] }}</td><td>{{ $row['source']['location'] }}</td><td>{{ $row['source']['teacher'] }}</td>@endif
<td>{{ $row['issues'] ? implode('; ', $row['issues']) : 'OK' }}</td></tr>
@endforeach
</tbody></table></div>
</div>
<div class="dp-actions"><a class="btn btn-secondary" href="{{ route($platform ? 'platform.education.imports' : 'school.education.imports', $platform ? ['school_id'=>$school->id] : []) }}">Terug / corrigeren</a>
@if($blocked === 0)<form method="POST" action="{{ route($platform ? 'platform.education.imports.commit' : 'school.education.imports.commit', $platform ? ['school_id'=>$school->id] : []) }}">@csrf
<input type="hidden" name="token" value="{{ $token }}"><label><input type="checkbox" name="confirm" value="1" required> Ik bevestig deze import voor {{ $schoolYear }}.</label><button class="btn" type="submit">Definitief importeren</button></form>@endif
</div>
@endsection
@push('styles')<style>.dp-error,.dp-ok{padding:16px;background:#fff0f0;border:1px solid #ecc6cb;border-radius:10px;margin-bottom:18px;font-size:12px;line-height:1.6}.dp-ok{background:#eaf7ef;border-color:#c4e8d4}.dp-error p{margin:5px 0}.dp-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse}th,td{font-size:12px;padding:10px;border-bottom:1px solid #e9eef2;text-align:left}th{color:#64778d}.dp-actions{display:flex;justify-content:flex-end;align-items:center;gap:12px;flex-wrap:wrap;margin-top:20px}.dp-actions form{display:flex;align-items:center;gap:14px;flex-wrap:wrap}.dp-actions label{font-size:12px}</style>@endpush
