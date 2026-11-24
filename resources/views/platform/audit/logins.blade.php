@extends('layouts.platform')
@section('title','Aanmeldgeschiedenis')
@section('page-title','Aanmeldgeschiedenis')
@section('content')
<div class="page-header"><h1>Aanmeldgeschiedenis</h1><p>Geslaagde en mislukte aanmeldingen, afmeldingen en schoolkeuzes. Alleen zichtbaar voor platformbeheerders.</p></div>
<div class="card">
<form method="GET" action="{{ route('platform.audit.logins') }}" class="la-filters">
<label>Zoeken op gebruiker<input type="search" name="q" value="{{ $search }}" placeholder="Naam of e-mailadres"></label>
<label>Gebeurtenis<select name="event">
<option value="">Alle gebeurtenissen</option>
@foreach(['login_success'=>'Geslaagd','login_failed'=>'Mislukt','logout'=>'Afgemeld','school_selected'=>'School gekozen'] as $value=>$title)
<option value="{{ $value }}" @selected($event===$value)>{{ $title }}</option>
@endforeach
</select></label>
<label>School<select name="school_id"><option value="">Alle scholen</option>
@foreach($schools as $school)<option value="{{ $school->id }}" @selected((string)$schoolId===(string)$school->id)>{{ $school->name }}</option>@endforeach
</select></label>
<button type="submit" class="btn">Filteren</button>
<a class="btn btn-secondary" href="{{ route('platform.audit.logins') }}">Wissen</a>
</form>
<p class="la-note">Mislukte pogingen worden met een gedeeltelijk afgeschermd e-mailadres geregistreerd. Wachtwoorden, sessiesleutels en tokens worden nooit gelogd. De school is pas bekend zodra een gebruiker ze kiest.</p>
<div class="la-scroll"><table class="la-table">
<thead><tr><th>Datum en tijd</th><th>Gebeurtenis</th><th>Gebruiker</th><th>School</th><th>IP-adres</th><th>Browser / toestel</th></tr></thead>
<tbody>
@forelse($events as $row)
<tr>
<td>{{ \Illuminate\Support\Carbon::parse($row->created_at)->format('d/m/Y H:i:s') }}</td>
<td>
@php $eventLabels=['login_success'=>'Geslaagd','login_failed'=>'Mislukt','logout'=>'Afgemeld','school_selected'=>'School gekozen']; @endphp
<span class="la-pill {{ $row->event==='login_failed'?'la-danger':($row->event==='login_success'?'la-success':'') }}">{{ $eventLabels[$row->event] ?? $row->event }}</span></td>
<td><strong>{{ $row->user_name ?: ($row->email_hint ?: 'Onbekende gebruiker') }}</strong>
@if($row->user_email)<small>{{ $row->user_email }}</small>@endif</td>
<td>{{ $row->school_name ?: '—' }}</td>
<td>{{ $row->ip_address ?: '—' }}</td>
<td class="la-ua" title="{{ $row->user_agent }}">{{ \Illuminate\Support\Str::limit($row->user_agent ?: '—', 65) }}</td>
</tr>
@empty<tr><td colspan="6" style="text-align:center;padding:35px;color:#718096">Nog geen aanmeldingen geregistreerd met deze filters.</td></tr>
@endforelse
</tbody></table></div>
{{ $events->links('vendor.pagination.artelynx') }}
</div>
@endsection
@push('styles')
<style>
.la-filters{display:flex;align-items:end;gap:11px;flex-wrap:wrap;margin:0 0 18px}
.la-filters label{display:flex;flex:1 1 180px;flex-direction:column;gap:6px;font-size:11px;font-weight:700;color:#475d73}
.la-filters input,.la-filters select{height:42px;border:1px solid #d6e1e8;border-radius:8px;padding:0 11px;font:inherit;color:#142b45}
.la-filters .btn{height:42px}
.la-note{font-size:12px;color:#718096;margin:0 0 20px;line-height:1.7}
.la-scroll{overflow-x:auto}.la-table{width:100%;border-collapse:collapse}
.la-table th,.la-table td{padding:13px 11px;border-bottom:1px solid #e6eef2;text-align:left;vertical-align:middle;font-size:12px}
.la-table th{text-transform:uppercase;font-size:10px;color:#718096;white-space:nowrap}
.la-table small{display:block;font-size:10px;color:#718096;margin-top:4px}
.la-table td:first-child{white-space:nowrap}.la-ua{max-width:260px;overflow-wrap:anywhere;color:#6a7890}
.la-pill{display:inline-block;padding:5px 9px;border-radius:16px;background:#eef3f7;color:#5b6b80;white-space:nowrap;font-weight:700;font-size:10px}
.la-pill.la-success{background:#e6f5ee;color:#297c58}
.la-pill.la-danger{background:#fff0f0;color:#b04545}
</style>
@endpush
