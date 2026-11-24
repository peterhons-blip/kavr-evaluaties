@extends('layouts.platform')
@section('title','DKO3-import controleren')
@section('page-title','Personeelsleden')
@section('content')
<div class="page-header"><h1>DKO3-import controleren</h1><p>Controleer de aantallen zorgvuldig voordat je wijzigingen toepast op <strong>{{ $school->name }}</strong>.</p></div>
<div class="di-alert" style="margin-bottom:18px"><strong>Importmodus: {{ $mode === 'full' ? 'Volledige synchronisatie' : 'Toevoegen en bijwerken' }}</strong><p>{{ $mode === 'full' ? 'Ontbrekende leerkrachten worden gedeactiveerd bij bevestiging.' : 'Ontbrekende personeelsleden blijven ongewijzigd.' }} Er worden geen wachtwoordmails verstuurd.</p></div>
<div class="di-summary">
    <div><strong>{{ $stats['total'] }}</strong><span>Leerkrachten in bestand</span></div>
    <div><strong>{{ $stats['new'] }}</strong><span>Nieuwe accounts</span></div>
    <div><strong>{{ $stats['existing'] }}</strong><span>Bestaande accounts</span></div>
    <div class="danger"><strong>{{ $stats['deactivate'] }}</strong><span>Te verbergen en deactiveren</span></div>
</div>
<div class="card di-card">
    <h2>Nieuwe en bestaande personeelsleden</h2>
    <p style="font-size:12px;color:#728198">De eerste 30 personen uit het aangeleverde bestand:</p>
    <div style="overflow-x:auto"><table class="di-table"><thead><tr><th>Voornaam</th><th>Familienaam</th><th>E-mail</th><th>Vakken (huidig schooljaar)</th><th>Vestigingen</th></tr></thead>
    <tbody>@foreach(array_slice($rows,0,30) as $row)<tr><td>{{ $row['first_name'] }}</td><td>{{ $row['last_name'] }}</td><td>{{ $row['email'] }}</td><td>{{ $row['dko3_subjects'] ?? '—' }}</td><td>{{ $row['dko3_locations'] ?? '—' }}</td></tr>@endforeach</tbody></table></div>
</div>
<div class="card di-card">
    <h2>Niet meer in DKO3: {{ $stats['deactivate'] }}</h2>
    <p style="font-size:12px;color:#728198">Deze leerkrachten verdwijnen uitsluitend uit de gewone personeelslijsten van {{ $school->name }}. Hun andere schoolkoppelingen blijven bewaard.</p>
    @if($missing->isNotEmpty())
    <div style="overflow-x:auto"><table class="di-table"><thead><tr><th>E-mailadres</th></tr></thead><tbody>
        @foreach($missing->take(50) as $person)<tr><td>{{ $person->email }}</td></tr>@endforeach
    </tbody></table></div>
    @else <p>Geen leerkrachten om uit te schakelen.</p> @endif
</div>
<div class="di-alert"><strong>Definitieve import</strong><p>Deze actie werkt bestaande personeelsgegevens bij. Alleen bij volledige synchronisatie kunnen ontbrekende leerkrachten in de geselecteerde school worden gedeactiveerd. Controleer of de export volledig is. Er worden nu nog geen e-mails verzonden.</p></div>
<form method="POST" action="{{ route('platform.users.import.commit') }}" class="di-actions">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<label><input type="checkbox" name="confirm" value="1" required> Ik bevestig dat dit de volledige en juiste DKO3-personeelslijst van {{ $school->name }} is.</label>
<a class="btn btn-secondary" href="{{ route('platform.users.import') }}">Annuleren</a>
<button type="submit" class="btn">Import definitief uitvoeren</button>
</form>
@endsection
@push('styles')<style>
.di-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:13px;margin:0 0 22px}.di-summary>div{background:#fff;border:1px solid #e4eaf0;padding:19px;border-radius:12px}.di-summary strong{font-size:30px;display:block}.di-summary span{font-size:11px;color:#718096}.di-summary .danger{border-color:#f0cecd;background:#fff8f8}.di-summary .danger strong{color:#af4b49}.di-card{margin:0 0 18px}.di-card h2{font-size:17px}.di-table{width:100%;border-collapse:collapse}.di-table th,.di-table td{text-align:left;border-bottom:1px solid #edf0f3;padding:11px 12px;font-size:12px}.di-table th{color:#718096;font-size:10px;text-transform:uppercase}.di-alert{background:#fff7e9;border:1px solid #f3deb6;padding:16px;border-radius:10px;font-size:12px;line-height:1.7}.di-actions{display:flex;justify-content:flex-end;align-items:center;gap:14px;margin:22px 0;flex-wrap:wrap}.di-actions label{margin-right:auto;display:flex;gap:9px;align-items:center;font-size:12px}@media(max-width:800px){.di-summary{grid-template-columns:repeat(2,1fr)}}@media(max-width:460px){.di-summary{grid-template-columns:1fr}}
</style>@endpush