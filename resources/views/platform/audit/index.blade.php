@extends('layouts.platform')
@section('title','Beheerhistoriek')
@section('page-title','Beheerhistoriek')
@section('content')
<div class="page-header"><h1>Beheerhistoriek</h1><p>Overzicht van wijzigingen aan gebruikers, schoolkoppelingen en rollen. Alleen toegankelijk voor platformadministrators.</p></div>
<div class="card">
<div style="overflow-x:auto"><table class="audit-table"><thead><tr><th>Datum</th><th>Actie</th><th>Beheerder</th><th>School</th><th>Gebruiker</th><th>Details</th></tr></thead><tbody>
@forelse($events as $event)
<tr>
<td>{{ \Illuminate\Support\Carbon::parse($event->created_at)->timezone('Europe/Brussels')->format('d/m/Y H:i') }}</td>
<td><strong>{{ $event->action }}</strong></td>
<td>{{ $event->actor_name ?? 'Onbekend' }}</td>
<td>{{ $event->school_name ?? 'Platform' }}</td>
<td>{{ $event->subject_name ?? '—' }}</td>
<td><code>{{ $event->details ? json_encode(json_decode($event->details, true),JSON_UNESCAPED_UNICODE) : '—' }}</code></td>
</tr>
@empty<tr><td colspan="6">Nog geen wijzigingen geregistreerd.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:20px">{{ $events->links() }}</div>
</div>
@endsection
@push('styles')
<style>.audit-table{width:100%;border-collapse:collapse}.audit-table th,.audit-table td{padding:13px;border-bottom:1px solid #e1e8ee;vertical-align:top;text-align:left;font-size:12px}.audit-table th{font-size:11px;text-transform:uppercase;color:#718096}.audit-table code{font-size:11px;white-space:pre-wrap;overflow-wrap:anywhere}</style>
@endpush
