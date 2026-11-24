@extends('layouts.platform')
@section('title','DKO3-personeel importeren')
@section('page-title','Personeelsleden')
@section('content')
<div class="page-header"><h1>Personeelsleden importeren uit DKO3</h1><p>Importeer het personeelsbestand voor één geselecteerde school. Je krijgt eerst een overzicht van de wijzigingen.</p></div>
@if($errors->any())<div class="di-error"><strong>Import niet gestart.</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
@if(session('import_errors'))<ul>@foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
</div>@endif
<div class="card di-card">
    <form method="POST" action="{{ route('platform.users.import.preview') }}" enctype="multipart/form-data">@csrf
        <div class="di-section"><span class="di-step">1</span><div><h2>Kies de school</h2><p>Alle aanpassingen, ook het uitschakelen van verdwenen personeelsleden, gelden alleen voor deze school.</p>
            <select name="school_id" required>
                <option value="">Selecteer een school…</option>
                @foreach($schools as $school)<option value="{{ $school->id }}" @selected((string) old('school_id', request('school_id')) === (string)$school->id)>{{ $school->name }}</option>@endforeach
            </select></div>
        </div>
        <div class="di-section"><span class="di-step">2</span><div><h2>Upload de DKO3-export</h2><p>Excel (.xlsx) of CSV, maximaal 5 MB. Vereist: de DKO3-kolommen 'naam' (Achternaam, Voornaam) en 'e-mailadres account', of afzonderlijke voornaam/familienaam/e-mailkolommen.</p>
            <input name="file" type="file" accept=".xlsx,.csv" required></div>
        </div>
        <div class="di-section"><span class="di-step">3</span><div>
            <h2>Kies hoe je wilt importeren</h2>
            <label style="display:block;margin:10px 0;font-size:13px">
                <input type="radio" name="mode" value="update_only" @checked(old('mode','update_only') === 'update_only') required>
                <strong>Toevoegen en bijwerken (veilig, standaard)</strong><br>
                <small>Niemand die ontbreekt in het bestand wordt gedeactiveerd. Ideaal voor testbestanden en deelimports.</small>
            </label>
            <label style="display:block;margin:10px 0;font-size:13px">
                <input type="radio" name="mode" value="full" @checked(old('mode') === 'full')>
                <strong>Volledige synchronisatie</strong><br>
                <small>Ontbrekende leerkrachten van de gekozen school worden gedeactiveerd en verborgen. Alleen voor een volledige DKO3-export.</small>
            </label>
        </div></div>
        <div class="di-warning"><strong>Geen automatische mails</strong><p>De import verstuurt geen wachtwoordinstellinks. Nieuwe gebruikers krijgen de schoolrol leerkracht. Bestaande schoolrollen blijven behouden.</p><strong>Belangrijk bij een volledige personeelsimport</strong><p>Alleen bij <strong>Volledige synchronisatie</strong> worden leerkrachten die ontbreken in de export gedeactiveerd. De veilige standaard <strong>Toevoegen en bijwerken</strong> laat bestaande personen ongemoeid. Andere scholen en bestaande wachtwoorden blijven onaangeroerd. Upload daarom uitsluitend een volledige export van alle huidige leerkrachten van die school.</p></div>
        <div class="di-actions"><a href="{{ route('platform.users.index') }}" class="btn btn-secondary">Annuleren</a><button type="submit" class="btn">Bestand controleren →</button></div>
    </form>
</div>
@endsection
@push('styles')<style>
.di-card{max-width:860px}.di-section{display:flex;align-items:flex-start;gap:17px;padding:14px 0 25px;border-bottom:1px solid #e7eef3;margin-bottom:20px}.di-step{display:grid;place-items:center;width:35px;height:35px;flex:0 0 35px;border-radius:50%;background:#e1f3f1;color:#147f7c;font-weight:800}.di-section>div{width:100%}.di-section h2{font-size:17px;margin:0 0 5px}.di-section p{font-size:12px;color:#728198;line-height:1.7;margin:0 0 13px}.di-section select,.di-section input[type=file]{width:100%;padding:12px;border:1px solid #d7e2e9;border-radius:9px;background:white}.di-section input[type=radio]{width:auto;margin-right:8px;accent-color:#147f7c}.di-warning{padding:17px 20px;background:#fff7e9;border-radius:11px;border:1px solid #f3deb6;font-size:12px;line-height:1.7}.di-warning p{margin:6px 0 0}.di-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:23px}.di-error{background:#fff0f1;color:#a62945;border-radius:10px;padding:17px;margin-bottom:18px}
</style>@endpush