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

## Personeelsbeheer: wachtwoordstatus en resetlink (november 2026)

- Schoolpersoneelslijst: aparte uitgelijnde kolommen schoolrol, schoolstatus (pivot), globale accountstatus en wachtwoordstatus; wachtwoordstatus `Nog in te stellen` (`must_change_password`), `Ingesteld` (`password_set_at` aanwezig), anders `Onbekend`.
- Schoolfilters: naam/mail, rol, schoolstatus, wachtwoordstatus, 20/50/100 rijen. Bestaande schooladmin-wijzigingen lopen via één eigen schoolkoppeling per gebruiker; andere academies blijven onaangeraakt.
- Knop `Instellink mailen`/`Resetlink mailen` roept beveiligde, gelimiteerde POST `school.members.setup-link` aan en hergebruikt Laravel's wachtwoordbroker. Geen token of ruwe reset-URL tonen aan beheerders. Alleen `users.manage`, actieve accounts/koppelingen, schoolgebonden gebruikers; audit op `school.member.setup_link_sent`.
- Nieuwe tests staan in `SchoolScopedManagementTest.php`; voer deze lokaal uit. Geen migratie vereist.

## Schoolpersoneel: volledige bewerkpagina en compact overzicht

- Schoolpersoneelsbeheer heeft een aparte detailpagina `/school/gebruikers/{user}/bewerken` met voornaam, familienaam, e-mail, telefoon, eigen schoolrol en schoolactivering.
- Gedeelde profielvelden zijn accountbreed; voor medewerkers aan meerdere scholen vergt een wijziging expliciete bevestiging. Platformadministrators moeten voor hun accountprofiel naar Platformbeheer. Eigen schoolkoppeling mag via het overzicht worden gewijzigd.
- Wachtwoorden kunnen uitsluitend via een beveiligde reset-/instellink worden gewijzigd; geen leesbaar wachtwoord of token in schoolbeheer.
- De drie samenvattingstegels boven het personeelsledenoverzicht zijn verwijderd op verzoek. De lijst heeft nu ook een knop 'Bewerken' per rij.
- Toegangschecks (schoolgebonden en schoolbeheerrechten) zijn opgenomen in `SchoolScopedManagementTest.php`. Geen migratie nodig.

## Onderwijsstructuur en schooljaarinschrijvingen (25 november 2026)

- Nieuwe schoolgebonden tabellen via migratie `2026_11_25_220000_create_dko_education_structure_tables.php`: `education_domains`, `education_subjects`, `student_school_years`, `student_subject_enrollments`. Data wordt nooit stilzwijgend verwijderd; domeinen/vakken krijgen een actieve status. Een leerling blijft één leerlingdossier per school/stamnummer houden, met inschrijvingen per jaar en vak.
- `/school/onderwijsaanbod` en `/platform/onderwijsaanbod?school_id=...`: domeinen en vakken toevoegen, bewerken en inactief zetten. Platformbeheer kan een academie kiezen; de schoolomgeving vereist `groups.view` of `school.settings` voor lezen en `groups.manage` of `school.settings` voor wijzigen. Alle URL-objecten op school_id gecontroleerd. Regressietests: `EducationCatalogTest`.
- De leerlingenidentiteitsimport heeft een expliciete schooljaarkeuze (bv. `2026-2027`) en schrijft records in `student_school_years` zonder andere jaren te verwijderen of te deactiveren. Historisch oudere clients zonder schooljaar blijven technisch ondersteund; de browserform is verplicht. Het leerlingenoverzicht toont en filtert schooljaren; `students.is_active` is nu duidelijk als *dossierstatus* gelabeld, niet als loginstatus. Leerlingen kunnen niet inloggen.
- De import van domeinen/opleidingen en de lijst van vakinschrijvingen per leerling staan nog open: inspecteer eerst de echte XLSX/CSV-headers. Niet op naam matchen wanneer er een stamnummer beschikbaar is. Lege of afwezige rijen mogen niet automatisch historische inschrijvingen beëindigen. Voor een latere volledige jaarsynchronisatie aparte preview met expliciete toestemming en waarschuwingen implementeren.
- Het schema omvat `grade` en `study_year` op vakinschrijvingen, maar koppelingen via werkelijke DKO3-bestanden zijn **nog niet geïmplementeerd**. Leerkrachttoewijzingen/lesgroepen, opleidingen, ICJ/KCJ, competenties, historische schooljaarafsluitingen en PDF/DKO3-export volgen nog.
- **Verplichte uitrol:** eerst `php artisan migrate`, dan cache wissen en tests uitvoeren, lokaal én bij productie-uitrol (op productie `migrate --force` na backup). Geen `migrate:fresh` op dataomgevingen.

## Analyse echte DKO3-exports: schooljaar 2026-2027 (25 november 2026)

Drie door de opdrachtgever aangeleverde (niet in Git opnemen) XLSX-exports gecontroleerd:
- `alleleerlingen_2026-2027(1).xlsx`: 600 regels/600 unieke stamnummers; kolommen `stamnummer, naam, voornaam`; geen ontbrekende waarden of dubbele stamnummers.
- `alle_inschrijvingen_2026-2027.xlsx`: 647 regels, exact dezelfde 600 leerlingen, kolommen `stamnummer, naam, voornaam, domein, straatnaam, huisnummer, busnummer, postcode, gemeente`; domeinverdeling Muziek (Mu) 378, Beeld (Be) 198, Woord (Wo) 71. 46 leerlingen met meerdere domeinen, één met drie. Persoonsadres is geen vereiste voor evaluaties; importeer niet zomaar adresdetails.
- `werklijst_allevakken(1).xlsx`: 1119 regels, kolommen `leerling, vak, graad, vestigingsplaats, leerkracht`. 54 unieke vakbenamingen, 35 namen van leerkrachten, 8 vestigingsplaatsen, 19 graad-/jaarwaardes inclusief tekst zoals `C.1` en `S.2`. **Geen stamnummer of leraren-ID in deze export**. Er zijn 599 unieke weergavenamen, waarvan 598 uniek aan leerlingstamnummer kunnen worden gekoppeld en 1 dubbelzinnige naam bij 2 stamnummers (6 vakregels). Geen volledig identieke vakregels; 16 vakregels delen leerling+vak+graad+vestigingsplaats met minstens een afwijking, en 21 leerling+vak+graad-combinaties hebben meer dan één lerarennaam. Dit mag geen automatische deduplicatie of overschrijving worden.

**Besluit voor importontwerp:**
1. Bouw één jaarlijkse DKO3-importwizard die drie logisch onafhankelijke bestanden **in volgorde** accepteert: (1) leerlingenidentiteit, (2) domeininschrijvingen per gekozen schooljaar, (3) vak-/leerkracht-/vestigingsregels. Ondersteun herimport van één deelbestand zonder andere delen te verwijderen. Laat later een uniform DKO3-template met stamnummer ook in vaklijst of directe koppeling uit DKO3 toe.
2. Koppel (1) en (2) strikt op `school_id + stamnummer`; koppel (3) via een voorvertoning met exacte unieke naammatch binnen de geselecteerde school en schooljaar, en blokkering/handmatige keuze bij dubbels. Map leraren en vestigingen expliciet naar lokale records, nooit stilzwijgend op fuzzy match.
3. Domeininschrijvingen verschillen van vakinschrijvingen en van het algemene leerlingdossier. Een leerling kan in meerdere domeinen tegelijk ingeschreven zijn. Het jaar en de context blijven bij inschrijving en evaluatie horen; `students.is_active` alleen dossierstatus.
4. Voor schooljaarafsluiting, opleidingsgraad/-leerjaar, officiële lesgroepidentiteit, vakleerkracht-relaties en historische docenttoewijzingen zijn aanvullende modellen nodig. `student_subject_enrollments` heeft voorlopig `unique(student_school_year_id,education_subject_id)`; dat is mogelijk te restrictief voor meerdere leerkrachten/groepen voor hetzelfde vak. Herzie schema met afzonderlijke lesgroep-/lerarenkoppeling voordat vakkenimport productierijp is. Deel niet blind vakregels in via vak+graad+locatie: dit identificeert geen echte klas.
5. Elke stap: preflight op bestandsformaat, schooljaar, duplicaten en conflicts; preview van nieuw/bijwerken/ongewijzigd/niet te matchen; transactie + expliciete bevestiging; geen automatische verwijdering of deactivering bij afwezigheid in een deelbestand; idempotente herimport; audit van aantallen zonder persoonsgegevens.
6. Zowel /school als /platform ondersteunen dezelfde importstappen met schoolscoping; platformselectie expliciet. Zorg dat onduidelijke matches niet stilzwijgend gekozen worden en maak een rapport met te controleren regels.
