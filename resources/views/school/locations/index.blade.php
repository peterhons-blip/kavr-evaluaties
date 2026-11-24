@extends('layouts.school')
@section('title','Vestigingsplaatsen')
@section('page-title','Vestigingsplaatsen')
@section('content')
<div class="page-header"><h1>Vestigingsplaatsen</h1><p>Beheer de locaties van {{ $school->name }}. Elke vestiging en elk vestigingsnummer blijft aan deze academie gekoppeld.</p></div>
@if(session('success'))<div class="sl-alert sl-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sl-alert sl-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="sl-summary">
  <div class="sl-stat"><span>Vestigingsplaatsen</span><strong>{{ $locations->count() }}</strong></div>
  <div class="sl-stat"><span>Actieve vestigingen</span><strong>{{ $locations->where('is_active',true)->count() }}</strong></div>
</div>
<div class="card">
<div class="sl-heading"><div><h2>Vestigingenoverzicht</h2><p>Locaties, adressen, vestigingsnummers en status van de eigen school.</p></div><a class="btn" href="{{ route('school.locations.create') }}">+ Nieuwe vestigingsplaats</a></div>
<div class="sl-scroll"><table class="sl-table"><thead><tr><th>Vestigingsplaats</th><th>Adres</th><th>Type</th><th>Status</th><th>Acties</th></tr></thead><tbody>
@forelse($locations as $location)<tr>
<td><strong>{{ $location->name }}</strong><small>{{ $location->location_number ? 'Nr. '.$location->location_number : 'Geen vestigingsnummer' }}</small></td>
<td>{{ $location->street ?: '—' }}<small>{{ trim(($location->postal_code ?? '').' '.($location->city ?? '')) }}</small></td>
<td><span class="sl-pill {{ $location->is_main_location?'sl-main':'sl-regular' }}">{{ $location->is_main_location?'Hoofdvestiging':'Vestiging' }}</span></td>
<td><span class="sl-pill {{ $location->is_active?'sl-active':'sl-inactive' }}">{{ $location->is_active?'Actief':'Inactief' }}</span></td>
<td><a class="btn btn-secondary" href="{{ route('school.locations.edit',$location) }}">Bewerken</a></td>
</tr>@empty<tr><td colspan="5" class="sl-empty">Nog geen vestigingsplaatsen geregistreerd. Voeg de eerste vestiging toe.</td></tr>@endforelse
</tbody></table></div>
</div>
@endsection
@push('styles')
<style>
.sl-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px;max-width:600px;margin-bottom:22px}.sl-stat{background:#fff;border:1px solid #e1e8ee;padding:19px 22px;border-radius:12px}.sl-stat span{font-size:11px;color:#718096}.sl-stat strong{display:block;font-size:27px;margin-top:3px}
.sl-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:22px}.sl-heading h2{font-size:18px;margin:0 0 5px}.sl-heading p{color:#718096;font-size:12px;margin:0}
.sl-scroll{overflow-x:auto}.sl-table{width:100%;border-collapse:collapse}.sl-table th,.sl-table td{padding:14px 11px;text-align:left;vertical-align:middle;border-bottom:1px solid #e9eef2;font-size:12px}
.sl-table th{font-size:10px;color:#718096;text-transform:uppercase;white-space:nowrap}.sl-table small{display:block;font-size:11px;color:#718096;margin-top:4px}.sl-pill{display:inline-block;padding:6px 10px;border-radius:16px;font-size:10px;font-weight:700;white-space:nowrap}
.sl-main{color:#267e73;background:#e9f6f3}.sl-regular{color:#687b8d;background:#edf1f6}.sl-active{color:#267b56;background:#e8f7ee}.sl-inactive{color:#a94c4c;background:#fff0f0}
.sl-empty{text-align:center;padding:32px!important;color:#718096}.sl-alert{padding:14px 17px;margin-bottom:17px;border-radius:9px}.sl-success{background:#e8f6ee;color:#267b56}.sl-error{background:#fff0f0;color:#a94c4c}
@media(max-width:600px){.sl-summary{grid-template-columns:1fr}}
</style>
@endpush
