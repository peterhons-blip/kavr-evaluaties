@extends('layouts.platform')
@section('title','E-maillog')
@section('page-title','E-maillog')
@section('content')
<div class="page-header">
    <h1>E-maillog</h1>
    <p>Alle door Artelynx verwerkte uitgaande e-mails, inclusief wachtwoordmails, instellinks en testmails. Alleen platformbeheerders hebben toegang.</p>
</div>
<div class="ml-actions">
    <a href="{{ route('platform.audit.index') }}" class="btn btn-secondary">← Beheerhistoriek</a>
    <a href="{{ route('platform.settings.edit') }}" class="btn">SMTP-instellingen</a>
</div>
<div class="card">
    <form method="GET" action="{{ route('platform.audit.mail') }}" class="ml-filter">
        <label for="ml-status">Resultaat</label>
        <select name="status" id="ml-status" onchange="this.form.submit()">
            <option value="" @selected($status === '')>Alle e-mails</option>
            <option value="failed" @selected($status === 'failed')>Mislukt</option>
            <option value="success" @selected($status === 'success')>Geslaagd</option>
        </select>
        <noscript><button class="btn" type="submit">Filteren</button></noscript>
    </form>
    <div class="ml-table-wrap">
        <table class="ml-table">
            <thead><tr>
                <th>Datum</th><th>Type</th><th>Onderwerp</th><th>Uitgevoerd door</th><th>Ontvanger</th>
                <th>Modus</th><th>Resultaat</th><th>Diagnose / advies</th>
            </tr></thead>
            <tbody>
                @forelse($events as $event)
                    @php
                        $details = json_decode($event->details ?? '{}', true) ?: [];
                        $diagnosis = $details['diagnosis'] ?? null;
                        $success = (bool)($details['success'] ?? false);
                    @endphp
                    <tr>
                        <td class="ml-nowrap">{{ \Illuminate\Support\Carbon::parse($event->created_at)->timezone('Europe/Brussels')->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $event->action === 'platform.mail.test' ? 'SMTP-test' : 'Verzonden e-mail' }}</td>
                        <td>{{ $details['subject'] ?? 'Niet geregistreerd (oud bericht)' }}</td>
                        <td>{{ $event->actor_name ?? 'Automatisch / gebruiker' }}</td>
                        <td>{{ $details['recipient'] ?? 'Niet opgeslagen (oude test)' }}</td>
                        <td>{{ ($details['driver'] ?? 'onbekend') === 'smtp' ? 'SMTP' : 'Logmodus' }}</td>
                        <td>
                            <span class="ml-pill {{ $success ? 'ml-ok' : 'ml-error' }}">
                                {{ $success ? 'Verwerkt' : 'Mislukt' }}
                            </span>
                        </td>
                        <td>
                            @if(is_array($diagnosis))
                                <strong>{{ $diagnosis['explanation'] ?? 'Verzending mislukt.' }}</strong>
                                <div class="ml-advice">{{ $diagnosis['advice'] ?? '' }}</div>
                                <small>Code: {{ $diagnosis['code'] ?? 'unknown' }}</small>
                            @elseif($success)
                                {{ ($details['result'] ?? '') === 'logged_only' ? 'Alleen gelogd; geen echte e-mail verzonden.' : 'Door de mailserver aanvaard; ontvangst niet bevestigd.' }}
                            @else
                                <span class="ml-advice">Oude test zonder detaildiagnose. Probeer opnieuw met de nieuwste versie.</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ml-empty">Er zijn nog geen uitgaande e-mails geregistreerd.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="ml-pages">{{ $events->links('vendor.pagination.artelynx') }}</div>
</div>
<p class="ml-footnote">Dit overzicht bewaart alleen een beperkt onderwerp zonder URL's, en geen e-mailinhoud, ruwe SMTP-responses, wachtwoorden of resetlinks. Deze lijst registreert uitgaande mails vanaf deze update. Een status 'Verwerkt' betekent dat de e-mail aan het ingestelde transport is aangeboden, niet dat hij daadwerkelijk werd afgeleverd. Historische mails verschijnen niet met terugwerkende kracht.</p>
@endsection
@push('styles')
<style>
.ml-actions{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.ml-filter{display:flex;align-items:center;gap:12px;margin-bottom:19px;font-size:12px;font-weight:700}
.ml-filter select{padding:10px 12px;border:1px solid #d5dfe7;border-radius:8px;font:inherit}
.ml-table-wrap{overflow-x:auto}
.ml-table{width:100%;border-collapse:collapse}
.ml-table td,.ml-table th{padding:14px 11px;text-align:left;vertical-align:top;border-bottom:1px solid #e1e8ee;font-size:12px}
.ml-table th{font-size:11px;text-transform:uppercase;color:#718096;white-space:nowrap}
.ml-table td{line-height:1.7}
.ml-nowrap{white-space:nowrap}
.ml-advice{margin-top:5px;color:#677d8c}
.ml-table small{display:block;color:#9aaabb}
.ml-pill{display:inline-block;padding:6px 10px;border-radius:99px;font-weight:800;font-size:11px}
.ml-ok{background:#e8f7ee;color:#25845b}
.ml-error{background:#fceeee;color:#b94747}
.ml-empty{text-align:center;color:#718096;padding:30px!important}
.ml-pages{margin-top:20px}
.ml-footnote{font-size:11px;color:#718096;line-height:1.7;max-width:1000px}
</style>
@endpush
