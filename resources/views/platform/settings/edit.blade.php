@extends('layouts.platform')
@section('title','Platforminstellingen')
@section('page-title','Platforminstellingen')

@section('content')
<div class="page-header">
    <h1>Platforminstellingen</h1>
    <p>Beheer de algemene platformgegevens en de uitgaande e-mail voor alle aangesloten scholen.</p>
</div>

@if(session('success'))
    <div class="ps-notice ps-success" role="status">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="ps-notice ps-error" role="alert">
        <strong>Niet uitgevoerd:</strong>
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<form action="{{ route('platform.settings.update') }}" method="POST" class="ps-stack">
    @csrf @method('PUT')
    <section class="card ps-card">
        <div class="ps-section-title"><div class="ps-icon">⚙</div><div>
            <h2>Algemeen</h2>
            <p>Basisgegevens van het volledige Artelynx-platform, niet van één school.</p>
        </div></div>
        <div class="ps-grid">
            <label class="ps-field"><span>Support-e-mailadres</span>
                <input type="email" name="support_email" maxlength="255" placeholder="support@voorbeeld.be" value="{{ old('support_email',$settings->support_email) }}">
                <small>Contactadres voor vragen over het platform.</small>
            </label>
            <label class="ps-field"><span>Standaardtijdzone</span>
                <select name="default_timezone">
                    <option value="Europe/Brussels" @selected(old('default_timezone',$settings->default_timezone) === 'Europe/Brussels')>Europa / Brussel</option>
                    <option value="UTC" @selected(old('default_timezone',$settings->default_timezone) === 'UTC')>UTC</option>
                </select>
            </label>
        </div>
    </section>

    <section class="card ps-card">
        <div class="ps-section-title"><div class="ps-icon">✉</div><div>
            <h2>Uitgaande e-mail (SMTP)</h2>
            <p>Wordt gebruikt voor wachtwoordinstellinks en toekomstige platformberichten.</p>
        </div></div>
        <div class="ps-grid">
            <label class="ps-field"><span>Verzendmodus</span>
                <select name="mail_driver" id="ps-driver">
                    <option value="log" @selected(old('mail_driver',$settings->mail_driver) === 'log')>Logmodus – geen echte e-mails</option>
                    <option value="smtp" @selected(old('mail_driver',$settings->mail_driver) === 'smtp')>SMTP – echte e-mails versturen</option>
                </select>
                <small>Gebruik eerst logmodus bij lokale tests.</small>
            </label>
            <div class="ps-hint"><strong>Google SMTP</strong><p>Voor Google Workspace of Gmail kun je <code>smtp.gmail.com</code> op poort <code>587</code> met STARTTLS gebruiken. Meestal is een app-wachtwoord of door de beheerder toegelaten SMTP-authenticatie nodig.</p></div>
            <label class="ps-field"><span>SMTP-server</span>
                <input name="mail_host" type="text" maxlength="255" autocomplete="off" value="{{ old('mail_host',$settings->mail_host ?: 'smtp.gmail.com') }}" placeholder="smtp.gmail.com">
            </label>
            <label class="ps-field"><span>SMTP-poort</span>
                <input name="mail_port" type="number" min="1" max="65535" value="{{ old('mail_port',$settings->mail_port ?: 587) }}">
            </label>
            <label class="ps-field"><span>Beveiliging</span>
                <select name="mail_encryption">
                    <option value="tls" @selected(old('mail_encryption',$settings->mail_encryption) === 'tls')>STARTTLS (poort 587)</option>
                    <option value="ssl" @selected(old('mail_encryption',$settings->mail_encryption) === 'ssl')>SSL/TLS (poort 465)</option>
                    <option value="none" @selected(old('mail_encryption',$settings->mail_encryption) === 'none')>Geen versleuteling (alleen testserver)</option>
                </select>
            </label>
            <label class="ps-field"><span>SMTP-gebruikersnaam</span>
                <input name="mail_username" type="text" maxlength="255" autocomplete="off" value="{{ old('mail_username',$settings->mail_username) }}" placeholder="no-reply@voorbeeld.be">
            </label>
            <label class="ps-field"><span>SMTP-app-wachtwoord</span>
                <input name="mail_password" type="password" maxlength="1024" autocomplete="new-password" placeholder="{{ $settings->mail_password ? 'Wachtwoord opgeslagen – leeg laten om te behouden' : 'Voer SMTP-app-wachtwoord in' }}">
                <small>{{ $settings->mail_password ? 'Er staat een versleuteld wachtwoord opgeslagen. Vul alleen iets in om het te vervangen.' : 'Het wachtwoord wordt versleuteld in de database opgeslagen en nooit opnieuw getoond.' }}</small>
            </label>
            <label class="ps-field"><span>Afzenderadres</span>
                <input name="mail_from_address" type="email" maxlength="255" value="{{ old('mail_from_address',$settings->mail_from_address) }}" placeholder="no-reply@voorbeeld.be">
            </label>
            <label class="ps-field"><span>Afzendernaam</span>
                <input name="mail_from_name" type="text" maxlength="255" value="{{ old('mail_from_name',$settings->mail_from_name ?: 'Artelynx') }}">
            </label>
        </div>
        <div class="ps-actions"><button class="btn" type="submit">Instellingen opslaan</button></div>
    </section>
</form>

<section class="card ps-card">
    <div class="ps-section-title"><div class="ps-icon">✓</div><div>
        <h2>Testmail versturen</h2>
        <p>Sla eerst de instellingen op. De knop gebruikt de momenteel opgeslagen configuratie.</p>
    </div></div>
    <div class="ps-test-grid">
        <div>
            <form action="{{ route('platform.settings.test-mail') }}" method="POST" class="ps-test-form" onsubmit="return confirm('Testbericht verzenden naar dit e-mailadres?')">
                @csrf
                <label class="ps-field"><span>Testontvanger</span>
                    <input type="email" name="recipient" required maxlength="255" value="{{ old('recipient',auth()->user()->email) }}" placeholder="jouw@e-mailadres.be">
                </label>
                <button type="submit" class="btn btn-secondary">✉ Testmail versturen</button>
            </form>
        </div>
        <div class="ps-test-status">
            <strong>Huidige modus: {{ $settings->mail_driver === 'smtp' ? 'SMTP' : 'Logmodus' }}</strong>
            <p>In logmodus wordt het bericht uitsluitend naar <code>storage/logs/laravel.log</code> geschreven.</p>
            @if($settings->last_test_at)
                <p>Laatste test: {{ $settings->last_test_at->format('d/m/Y H:i') }} —
                    {{ $settings->last_test_result === 'success' ? 'Verwerking gelukt' : 'Mislukt' }}</p>
            @endif
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.ps-stack{display:flex;flex-direction:column;gap:20px;margin-bottom:20px}
.ps-card{max-width:1080px}
.ps-section-title{display:flex;align-items:center;gap:15px;margin-bottom:24px}
.ps-section-title h2{margin:0 0 5px;font-size:18px}
.ps-section-title p{margin:0;font-size:12px;color:#718096;line-height:1.7}
.ps-icon{display:grid;place-items:center;flex:0 0 46px;height:46px;background:#e4f4f2;color:#207f79;border-radius:12px;font-size:22px;font-weight:700}
.ps-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
.ps-field{display:flex;flex-direction:column;gap:8px;font-size:12px;min-width:0}
.ps-field>span{font-weight:800;color:#415771}
.ps-field input,.ps-field select{min-height:44px;width:100%;padding:10px 12px;border:1px solid #d5dfe7;border-radius:9px;font:inherit;font-size:13px;background:#fff;color:#142b45}
.ps-field small{font-size:11px;color:#76899b;line-height:1.55}
.ps-field input:focus,.ps-field select:focus{outline:2px solid #80ccc4;outline-offset:1px}
.ps-hint{background:#f3f8f8;border:1px solid #dceceb;padding:13px;border-radius:9px;font-size:12px;line-height:1.55}
.ps-hint p{margin:5px 0 0;color:#526e78}
.ps-actions{margin-top:24px;display:flex;justify-content:flex-end}
.ps-notice{padding:14px 17px;border-radius:9px;max-width:1080px;margin-bottom:20px;font-size:12px;line-height:1.7}
.ps-success{background:#e9f7ef;color:#277453}
.ps-error{background:#fff0f2;color:#9e3046}
.ps-test-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:24px}
.ps-test-form{display:flex;align-items:end;gap:12px;flex-wrap:wrap}
.ps-test-form label{flex:1;min-width:220px}
.ps-test-status{background:#f7fafb;border-radius:10px;padding:17px;font-size:12px;line-height:1.65}
.ps-test-status p{margin:8px 0 0;color:#718096}
@media(max-width:740px){.ps-grid,.ps-test-grid{grid-template-columns:1fr}}
</style>
@endpush
