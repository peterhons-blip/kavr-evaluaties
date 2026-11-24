@extends('layouts.school-students')
@section('title','Vestigingsplaatsen')
@section('page-title','Vestigingsplaatsen')
@section('content')
<div class="page-header"><h1>Vestigingsplaatsen</h1><p>Beheer uitsluitend de vestigingen van {{ $school->name }}.</p></div>
@if(session('success'))<div class="card" style="color:#26734e;margin-bottom:16px">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" style="color:#a33b3b;margin-bottom:16px">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap"><h2 style="margin:0">{{ $locations->count() }} vestigingsplaatsen</h2><a class="btn" href="{{ route('school.locations.create') }}">+ Vestiging toevoegen</a></div>
<div style="overflow-x:auto"><table class="sl-table"><thead><tr><th>Naam</th><th>Nummer</th><th>Adres</th><th>Hoofdvestiging</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($locations as $location)<tr>
<td><strong>{{ $location->name }}</strong></td><td>{{ $location->location_number ?: '—' }}</td><td>{{ $location->street }}<br>{{ $location->postal_code }} {{ $location->city }}</td>
<td>{{ $location->is_main_location ? 'Ja' : 'Nee' }}</td><td>{{ $location->is_active ? 'Actief' : 'Inactief' }}</td><td><a class="btn btn-secondary" href="{{ route('school.locations.edit',$location) }}">Bewerken</a></td>
</tr>@empty<tr><td colspan="6" style="padding:25px">Er zijn nog geen vestigingsplaatsen geregistreerd.</td></tr>@endforelse
</tbody></table></div>
</div>
@endsection
@push('styles')<style>.sl-table{width:100%;border-collapse:collapse}.sl-table th,.sl-table td{text-align:left;border-bottom:1px solid #e6edf1;padding:12px;font-size:12px}.sl-table th{color:#718096;font-size:10px;text-transform:uppercase}</style>@endpush
