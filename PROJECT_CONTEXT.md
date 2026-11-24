# Artelynx - PROJECT_CONTEXT.md

**Bijgewerkt:** 24 november 2026 (leerlingenmodule)  
**Product:** Artelynx - *Elk talent in ontwikkeling.*  
**Eerste school:** Kunstacademie Voeren-Riemst (KA-VR)  
**Repository:** `peterhons-blip/kavr-evaluaties`; actieve integratiebranch `main`  
**Status:** actieve ontwikkeling; platform- en gebruikersbasis plus een eerste schoolgebonden leerlingenadministratie met DKO3-import bestaan. Vakken, lesgroepen, inschrijvingen en evaluaties volgen nog.

> Dit document beschrijft de huidige, in de repository nagekeken stand; het vervangt de verouderde planning uit 13-19 november. Raadpleeg daarnaast `AGENTS.md` en `docs/GEBRUIKERS_EN_RECHTEN.md`. Laat in twijfelgevallen de bestaande code en tests beslissen, niet oudere statusnotities.

## 1. Doel en functionele grenzen

Artelynx vervangt de Excel-gebaseerde ICJ- (individueel competentiegericht jaarplan) en KCJ-fiches (klassikaal competentiegericht jaarplan) in het deeltijds kunstonderwijs. Eén platform bedient meerdere academies, met strikt gescheiden schoolgegevens. Personeel kan bij meerdere scholen horen, telkens met een afzonderlijke rol en actieve koppeling.

Beoogde onderwijsmodules: vakken en instrumenten, opleidingen/graden, leerlingen, inschrijvingen, lesgroepen, competenties en leerdoelen, ICJ/KCJ-evaluaties, feedback, periode-/jaaroverzichten en PDF-rapporten geschikt voor DKO3. Deze modules zijn niet automatisch aanwezig omdat er al rechten voor bestaan.

## 2. Stack en werkomgevingen

- Laravel 13, PHP **8.4.1 of hoger** (let op: XAMPP heeft op sommige ontwikkellaptops nog PHP 8.2 op het PATH), Composer, MySQL/MariaDB.
- Blade, HTML/CSS en beperkt JavaScript; geen Node/NPM-vereiste in het huidige traject.
- Windows/XAMPP voor ontwikkeling; Windows-webserver voor live. Gebruik PHP 8.4+ voor **zowel** CLI/Composer **als** de webserver.
- Lokale werkmap: `C:\xampp\htdocs\kavr-evaluaties`; doorgaans `http://localhost:8001`; ontwikkel-DB `kavr_evaluaties_dev`.
- GitHub `main` bevat de geïntegreerde actuele wijzigingen. `.env` en lokale databanken zijn niet onderdeel van Git.
- Branding: Artelynx, logo `public/images/artelynx-logo.webp` waar aanwezig, favicon `public/favicon.ico`. Kleuren: donkerblauw `#142b45`, turquoise `#59aaa5`, goud `#e5a65b`, roze `#c97f9a`.

## 3. Reeds geïmplementeerd - platformbasis

- Centrale authenticatie, login, wachtwoordreset/instellinks, sessies, accountstatus en eigen profiel/wachtwoordwijziging.
- Multischoolbeheer, schoolselectie, actieve school in sessie, schoolinstellingen, vestigingsplaatsen en configureerbare standaard-/schoolspecifieke loginlinks.
- Centrale personeelspagina met aanmaken/bewerken, schoolkoppelingen, actieve status, rolinformatie, zoeken/filteren/sorteren, bulkacties en Nederlandstalige paginering.
- Leerlingenadministratie per school: toevoegen/bewerken, zoeken/status/paginering, CSV/XLSX-DKO3-import met eenmalig voorbeeld en expliciete bevestiging. DKO3-stamnummer is schoolgebonden uniek en blijft tekst (voorloopnullen). Import voegt toe / werkt namen bij; ontbrekende leerlingen en inactieve status blijven behouden.
- DKO3-personeelsimport met preview, veilige gedeeltelijke import als standaard, schoolgebonden metadata, rollen en bescherming tegen ongewenste deactivering van platformbeheerders bij volledige sync. Dit is **personeelsimport**, geen reeds voltooid leerlingenimportproces.
- Platformrollen- en rechtenmatrix, per-school rolbeheer, individuele toelaten/weigeren/overnemen-afwijkingen en beheerhistoriek.
- Platforminstellingen, SMTP-testfunctie en uitgaande e-maillog met onderwerp voor nieuw geregistreerde mails. Het onderwerp kan privacygevoelig zijn; geen mailinhoud, wachtwoorden of resetlinks loggen.
- GitHub Actions-workflow `.github/workflows/artelynx-tests.yml` voor syntax-, Blade-, route- en featuretests bij push naar `main` en pull requests.

## 4. Huidige gegevens- en rechtenarchitectuur

- `users`: één globaal account per persoon; `is_platform_admin` is een afzonderlijk platformrecht.
- `schools`: school/tenant; `school_user`: gebruikerskoppeling met `role`, `is_active` en DKO3-schoolmetadata.
- `school_roles`: schoolgebonden roldefinities en functierechten.
- `school_user_permissions`: expliciete individuele uitzonderingen `allowed` (toestaan/weigeren) per school, gebruiker en recht.
- `students` bevat één leerlingrecord per school en stamnummer, naamvelden en actieve status. Exports bevatten nog geen vak-/inschrijvingskoppelingen.
- `school_settings`, schoollocaties, `school_years`, `evaluation_periods`, `access_audit_events` en de gebruikelijke Laravel-tabellen zijn aanwezig. Verifieer de actuele migraties voordat nieuwe tabellen worden gemaakt.
- Standaardrolcodes: `school_admin`, `director`, `administration`, `coordinator`, `teacher`, `jury`, `viewer`; daarnaast maatwerkrollen.
- Catalogus: `App\Support\SchoolRoleCatalog`. Rechtencontrole: `App\Support\SchoolAccess::allows($user, $school, $permission)`.
- Rechten in de matrix voor `students.*`, `groups.*`, `goals.manage`, `evaluations.*`, `reports.*` en deels `school.settings` zijn vooruitlopende autorisatiecodes, geen bewijs van werkende modules.

**Verplichte autorisatievolgorde:** actief account, actieve school, actieve en niet via DKO3 verborgen schoolkoppeling, bestaande schoolrol, eventuele individuele uitzondering, anders rolrecht. Platformbeheer geeft **niet automatisch** inzage in schoolinhoud. Een `evaluations.view`-recht alleen mag toekomstige leerkrachten niet alle evaluaties van een school tonen: dwing ook leerling-/lesgroeptoewijzing of expliciete directiebevoegdheid af.

## 5. Beveiligingscontrole van 24 november 2026

Gerichte **broncodecontrole** uitgevoerd op `SchoolAccess`, platformmiddleware, actieve-schoolmiddleware, schoolselectie, rollen-/personeelscontrollers, rechtenmatrix, User-model en bestaande autorisatietests. Dit is **geen volledige penetratietest** en geen bewijs dat alle routes of productieconfiguratie veilig zijn.

- Bevestigd in code: platformroutes vereisen `platform.admin`; schoolrollen zijn gebonden aan schoolcontext; individuele overrides hebben enkel effect bij een geldige, actieve schoolkoppeling; wijzigacties controleren server-side bevoegdheid.
- Afwijking gevonden: schoolselectie en `EnsureActiveSchool` hielden geen rekening met `dko3_hidden_at`, terwijl `SchoolAccess` verborgen koppelingen al weigerde.
- Correctie op `main`: schoolselectie (lijst én POST) en dashboardmiddleware gebruiken nu `wherePivotNull('dko3_hidden_at')`. Twee regressietests toegevoegd in `tests/Feature/PermissionsAndProfileTest.php`.
- Nog te **verifiëren door de testworkflow of lokaal**: alle tests na deze wijziging. Een GitHub-commit op zichzelf is geen geslaagde testrun.
- Verdere controles bij toekomstige onderwijsmodules: cross-school toegang met URL-ID's, objecttoewijzing per docent/jury, directe POST/PUT/DELETE-requests, rolwisseling tijdens actieve sessie en individuele toestaan/weigeren/overnemen-afwijkingen.
- Opvallend: de tekst in `docs/GEBRUIKERS_EN_RECHTEN.md` bevat nog een instructie voor een oude featurebranch; gebruik `main` en deze bijgewerkte projectcontext.

## 6. Nog te bouwen - inhoudelijke kern

1. Onderwijsdatamodel: opleidingen, graden, vakken/instrumenten, leerjaren, inschrijvingen en lesgroepen; leerlingenbasis is al gestart. Leg schoolscoping en eigenaarstoewijzing vóór de bouw vast.
2. Leerlingenimport op basis van `alleleerlingen_2026-2027.xlsx` is beschikbaar (stamnummer, naam, voornaam); vakken-/inschrijvingenimport uit `werklijst_allevakken.xlsx` volgt later. Koppeling op naam vereist een controle bij dubbelzinnige namen.
3. Competentie-/leerdoelenbibliotheek (o.a. gedrevenheid, creëren, vakdeskundigheid, onderzoeken, samenwerken, presenteren) met historische snapshots/versies.
4. Gemeenschappelijke evaluatiekern voor ICJ en KCJ: periodes, feedback, rubrics, eventuele punten en afscherming per lesgroep/leerling.
5. PDF-rapportering per leerling, groep en periode en DKO3-geschikte output; concrete DKO3-exportvereisten nog te bevestigen.

## 7. Werkafspraken

- Lees vóór programmeren `AGENTS.md`, dit bestand en `docs/GEBRUIKERS_EN_RECHTEN.md`; inspecteer bestaande routes, modellen, migraties, controllers en tests.
- Wijzig geen bestaande uitgevoerde migraties; maak nieuwe migraties. Geen destructieve commando's (`migrate:fresh`, `migrate:reset`) tegen bestaande gegevens.
- Elke schoolgebonden query moet expliciet aan de school gekoppeld zijn; controleer **ook** rechten en individuele leerling-/lesgroeptoewijzingen. Nooit uitsluitend op verborgen knoppen vertrouwen.
- Wachtwoorden, tokens, resetlinks, mailinhoud en vertrouwelijke evaluatiegegevens nooit naar Git/auditlogs schrijven.
- Schrijf tests voor positieve én negatieve autorisatiecases, inclusief twee scholen en directe object-URL's.
- Respecteer bestaande data en API's; geen automatische productie-uitrol bij een Git-push.

## 8. Synchroniseren en controleren

PowerShell op ontwikkellaptop (zorg dat PHP >= 8.4.1 actief is):

```powershell
cd C:\xampp\htdocs\kavr-evaluaties
git status
git pull origin main
composer install
php artisan migrate
php artisan optimize:clear
php artisan test
```

Live: eerst bestaande configuratie, lopende transacties en databank beschermen; daarna vanuit de bestaande projectmap:

```powershell
git status
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

Gebruik op productie nooit een testcommando dat de echte databank wijzigt. Deze documentatie-update vereist op zich geen migratie. De aanvullende beveiligingswijziging evenmin.

**Volgende inhoudelijke ontwikkelfase:** onderwijsstructuur. De andere infrastructuuracties zijn geen onderdeel van de huidige opdracht.

## 9. Productieadres en huidige leerlingstap

- Officieel productieadres: **https://www.artelynx.be/**. Het oudere `kunstonderwijs.atheneumsinttruiden.be` is niet langer het primaire adres.
- Invoer: de DKO3-leerlingenexport heeft de kolommen `stamnummer`, `naam`, `voornaam`. Andere velden mogen niet uit deze export worden afgeleid.
- Platformbeheer: `/platform/leerlingen?school_id=...` (alleen platformadministrator).
- Schoolbeheer: `/school/leerlingen` (actieve school, `students.view` of `students.manage`, voor wijzigen/importeren `students.manage`).
- Preview-bestanden staan tijdelijk op de private lokale storage, gekoppeld aan gebruikerssessie en school, met vervaltijd; nooit uploaden naar Git.
- Alleen voorloopnullen, schoolgebonden uniekheid en persoonsnamen importeren; geen automatische uitschakeling wegens afwezigheid in export.
- De tests in `tests/Feature/Dko3StudentAdministrationTest.php` zijn toegevoegd. **Draai ze lokaal/CI voordat je productie bijwerkt.**

## Schoolomgeving en beheer (24 november 2026)

- Platformbeheer is voor het platform als geheel: scholen, globale instellingen, toezicht en ondersteuning.
- De **eigen schoolomgeving** is de operationele werkplek van leerkrachten, administratie en directie.
- Het schooldashboard toont uitsluitend toegestane modules en schoolgebonden tellingen. Schoolgebruikers kunnen leerlingen, personeelsleden, rollen/rechten, vestigingsplaatsen en schoolinstellingen openen volgens de bestaande schoolrollen.
- Nieuwe routes `/school/instellingen` en `/school/vestigingen` controleren server-side `school.settings` en gebruiken uitsluitend `active_school_id`; records uit een andere academie leveren 404 op.
- Schoolinstellingen passen alleen de schoolspecifieke loginteksten, kleuren, taal en tijdzone aan. Platformbrede instellingen en technisch systeembeheer blijven centraal.
- Het dashboard gebruikt expliciet schoolgebonden tellingen en gekwalificeerde pivot-query's om SQL-ambiguïteit te vermijden. Regressietests: `SchoolDashboardAccessTest` en `SchoolScopedManagementTest`.
- Voor deze aanpassingen zijn geen nieuwe migraties nodig. Voer lokaal `php artisan test` uit vóór live-uitrol.

## Schoollayout en aanmeldgeschiedenis (24 november 2026)

- Alle operationele schoolpagina's gebruiken nu de gedeelde Blade-layout `resources/views/layouts/school.blade.php`; dashboard, leerlingen, personeelsleden, rollen, vestigingsplaatsen en schoolinstellingen hebben dezelfde navigatie, schoolgebonden rechten en platformstijl-topbalk.
- Linksboven Artelynx; het schoollogo staat als afzonderlijke academie-identiteit bij de naam van de actieve school in de sidebar. Het schoolkeuzescherm toont geen redundant label 'Huidige school' meer en heeft een verbeterde profiel/afmeldnavigatie.
- Platformbeheer heeft **Aanmeldgeschiedenis** op `/platform/audit/logins`, uitsluitend voor platformadmins. Login-, logout-, mislukte login- en schoolkeuze-events staan in `login_events`, met IP, browser en timestamp; school is uitsluitend bekend wanneer expliciet gekozen.
- Mislukte loginpogingen bewaren alleen een gemaskeerde e-mailhint en geen password of token. Behandel toegang tot IP-/aanmeldgegevens als vertrouwelijke platformbeheerdersinformatie. Retentiebeleid nog te bepalen.
- **Nieuwe migratie noodzakelijk**: `2026_11_24_235959_create_login_events_table.php` via `php artisan migrate`. Regressietest `tests/Feature/LoginAuditTest.php` toegevoegd; voer lokaal `php artisan test` uit.

## Uitgebreid schoolbeheer (24 november 2026)

- Schoolbeheer is functioneel en visueel dichter bij Platformbeheer gebracht: personeelslijst met rol-/statusfilters en zoekfunctie, schoolrollen met volledige rechtenmatrix en bewerkknoppen, vestigingsplaatsen met statusoverzicht, en uitgebreide schoolinstellingen met academielogo-upload en kleurenvoorbeeld.
- De schoolpagina's gebruiken consequent `layouts.school`, met in de bovenbalk het **academielogo naast de actieve schoolnaam**. Het Artelynx-logo linksboven blijft de platformidentiteit.
- Alle schoolbeheercontrollers controleren `active_school_id` en `SchoolAccess::allows()`. Een schooladmin kan geen platformbrede SMTP-/systeeminstellingen, globale accountstatussen of gegevens van andere academies wijzigen.
- Toegevoegd: regressietests voor eigen academielogo en personeelsscope in `SchoolScopedManagementTest.php`. Geen nieuwe migratie voor deze uitbreiding. Draai `php artisan test` en `php artisan view:cache` vóór uitrol.
