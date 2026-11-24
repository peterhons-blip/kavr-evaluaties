# AGENTS.md - Artelynx evaluatieplatform

**Bijgewerkt: 24 november 2026 (leerlingenbeheer).** Deze instructies zijn leidend voor Codex/AI-ontwikkelwerk in `peterhons-blip/kavr-evaluaties`. Lees ook `PROJECT_CONTEXT.md` en `docs/GEBRUIKERS_EN_RECHTEN.md`. De laatstgenoemde documentatie kan oudere featurebranch-commando's bevatten: **werk vanuit de actuele `main`**.

## Product, stack en bestaande code

- Publieke productnaam: **Artelynx**; slogan *Elk talent in ontwikkeling.* Niet teruggaan naar EvolvIT of de interne repositorynaam als UI-merk.
- Laravel 13, PHP **minimaal 8.4.1**, MySQL/MariaDB, Blade, CSS en JavaScript. Windows/XAMPP lokaal en Windows-server live.
- Geen onnodige extra dependencies of Node-build introduceren. Gebruik bestaande componenten/lay-outs en de Artelynx-huisstijl.
- Favicon: `public/favicon.ico`; logo doorgaans `public/images/artelynx-logo.webp`.
- Eén globaal account per persoon in `users`; meerdere scholen via `school_user`. Schoolrollen in `school_roles`; individuele uitzonderingen in `school_user_permissions`.
- Bestaande modules: platform-/schoolbeheer, accounts, DKO3-**personeels**import, rechtenmatrix, individueel rechtenbeheer, profiel/wachtwoord, e-mailinstellingen/-log, audit en **leerlingenadministratie met veilige DKO3-leerlingenimport**.
- Leerlingendossiers en import zijn beschikbaar; **vakken, lesgroepen, inschrijvingen, leerdoelen, ICJ/KCJ en PDF** zijn nog niet gerealiseerd. De DKO3-leerlingenexport bevat alleen stamnummer en namen.

## Autorisatie is verplicht bij elke wijziging

1. Gebruik `App\Support\SchoolAccess::allows($user, $school, $permission)` voor schoolgebonden functierechten; de rechtenlijst staat in `App\Support\SchoolRoleCatalog`.
2. Vereis: actief globaal account, actieve school, actieve **en niet via DKO3 verborgen** `school_user`-koppeling, juiste schoolrol, dan individuele uitzondering (`allow`/`deny`) bovenop het rolrecht. `inherit` betekent: geen override opgeslagen.
3. Gebruik voor het schooldashboard de bestaande `active.school`-middleware en de huidige actieve school uit de sessie; verifieer expliciet de school van elk via URL/modelbinding opgevraagd object.
4. Platformroutes zitten achter `auth` + `platform.admin`; `is_platform_admin` geeft geen automatisch leesrecht op vertrouwelijke school-/leerlingevaluaties.
5. `users.view`, `users.manage`, `roles.manage` zijn actief gebruikt. Rechten voor leerlingen, groepen, leerdoelen, evaluaties en rapporten zijn voorlopig deels vooruitgeplande **codes**, geen voldoende bescherming zonder concrete school- en objecttoewijzing.
6. Voor nieuwe evaluatieroutes is een rolrecht **niet genoeg**: controleer ook de specifieke lesgroep/leerling, docent-/jurytoewijzing, school en eventuele expliciete directiebevoegdheid.
7. Controleer bevoegdheid bij GET én bij POST/PUT/PATCH/DELETE. UI-elementen verbergen is geen toegangscontrole.
8. Vermijd cross-school lekken bij zoeken, exporteren, downloaden, achtergrondtaken en directe `/{id}`-routes.

## Bekende bevinding uit veiligheidsreview 24 november 2026

`SchoolAccess` weigerde verborgen DKO3-schoolkoppelingen al, maar `SchoolSelectionController` en `EnsureActiveSchool` controleerden `dko3_hidden_at` niet. Op `main` zijn beide schoolselectieroutes en de dashboardmiddleware aangescherpt met `wherePivotNull('dko3_hidden_at')`. Er staan twee nieuwe regressietests in `tests/Feature/PermissionsAndProfileTest.php`.

**Deze review was statische code-inspectie, geen volledige beveiligingsaudit.** Draai de tests en controleer CI voordat je een vrijgave als geverifieerd bestempelt. Voeg bij nieuwe objecttypen altijd cross-school en objecttoewijzingstests toe.

## Database- en privacyregels

- Gebruik uitsluitend nieuwe Laravel-migraties voor schemawijzigingen; wijzig geen reeds uitgevoerde migraties. Gebruik foreign keys, indexen en timestamps.
- Nooit `migrate:fresh` of `migrate:reset` tegen een bestaande dataomgeving zonder expliciete toestemming.
- Bewaar geheimen uitsluitend in veilige configuratie; commit geen `.env`, sleutels, tokens, wachtwoorden, resetlinks, leerlinggegevens of databaseexports.
- Auditeer gevoelige beheerhandelingen via bestaande `AccessAudit`; geen vertrouwelijke inhoud in auditvelden.
- E-maillog registreert nieuw verzonden mails met (beperkt) onderwerp, zonder links/mailinhoud. Onderwerpen kunnen persoonsgegevens bevatten; stel geen leerlinginformatie voor in onderwerpregels.
- Verwijder nooit stilzwijgend gebruikers, schoolkoppelingen of historische evaluaties.
- Scheid schooljaar- en periodegegevens en bewaak historische snapshots van leerdoelen/evaluatiecriteria.

## Ontwikkelprocedure voor Codex

1. Lees de drie projectdocumenten en controleer de huidige Git-branch/status.
2. Lees eerst de betrokken routes, middleware, controllers, modellen, migraties, views en tests. Ga niet uit van oude planbestanden.
3. Formuleer kort het doel en de impact; maak kleine, gerichte wijzigingen en behoud bestaande werking.
4. Voeg tests toe voor toegestaan/geweigerd, actieve/inactieve/verborgen schoolkoppelingen, school A versus B, en URL-objecten van andere scholen.
5. Controleer met PHP 8.4.1+: `composer install`, `php artisan optimize:clear`, `php artisan test`. Waar nodig `php artisan view:cache` en `php artisan route:list`.
6. De workflow `.github/workflows/artelynx-tests.yml` wordt getriggerd op push naar `main` en PR's. Een commit is **geen** bewijs van een geslaagde workflow.
7. Rapporteer welke bestanden gewijzigd zijn, welke tests daadwerkelijk liepen, wat niet getest kon worden, en welke handmatige stappen nodig zijn.
8. Bij expliciete instructie mag naar `main` worden geschreven; anders geen onverwachte pushes of productie-uitrol. De gebruiker voert live `git pull` zelf uit.
9. Na wezenlijke architectuurwijzigingen deze documenten bijwerken, zodat oude 'nog te bouwen'-teksten niet opnieuw als werkelijkheid worden gelezen.

## Huidige functionele planning (geen opdracht om nu uit te voeren)

Onderwijsstructuur (vakken/graden/leerlingen/lesgroepen/inschrijvingen), DKO3-leerlingimport, competentiebibliotheek, ICJ/KCJ-evaluaties, PDF-rapporten en DKO3-export. Bepaal beoordelingsschalen en technische exportvereisten pas met bevestigde input. Behoud de verplichte multischoolisolatie.

## Lokale en live werkomgeving

- Lokaal doorgaans `C:\xampp\htdocs\kavr-evaluaties`, `http://localhost:8001`, DB `kavr_evaluaties_dev`.
- PHP op PATH kan nog `C:\xampp\php\php.exe` (8.2) zijn; kies expliciet PHP 8.4+ voordat Composer of Artisan draait.
- Broncode via GitHub `main`, databanken en `.env` zijn omgevingsspecifiek.
- Geen productietests die bestaande databankrecords verwijderen of wijzigen.

## DKO3-leerlingenmodule (toegevoegd november 2026)

- `students`: uniek `(school_id, dko3_stamnummer)`, stamnummer altijd als tekst bewaren. Eenzelfde persoon kan in een andere academie een ander stamnummer hebben; nooit op naam samenvoegen.
- Schoolroutes `/school/leerlingen` vereisen `active.school` én server-side `students.view` of `students.manage`. Wijzigen en importeren uitsluitend `students.manage`. Platformroutes `/platform/leerlingen` zitten achter `platform.admin`.
- `App\Support\Dko3StudentSpreadsheet` leest `stamnummer`, `naam`, `voornaam` uit XLSX/CSV. Fouten/dubbels stoppen de volledige import; preview en bevestiging zijn verplicht.
- Preview-token is aan de sessie, uploader en school gekoppeld en verloopt na een uur. Geen ruwe export of leerlinggegevens committen naar GitHub of loggen in audits.
- Import voegt toe/bijwerkt namen en verwijdert/deactiveert nooit afwezige leerlingen. Manuele inactieve status blijft behouden.
- Regressietests: `tests/Feature/Dko3StudentAdministrationTest.php` en `tests/Feature/StudentModelTest.php`. Nieuwe koppelingen met vakken en lessen mogen geen naammatching zonder ambiguïteitscontrole uitvoeren.

## Schoolomgeving versus Platformbeheer

- Bouw dagelijkse schoolfunctionaliteit primair in de **actieve schoolomgeving**. Platformbeheer behoudt globale scholenconfiguratie en ondersteuning; maak geen gewone docentworkflow uitsluitend onder `/platform`.
- Beperk alle `/school/*`-routes met `auth`, `active.school`, `SchoolAccess::allows()` én een school_id-controle op elk benaderd model. Schoolgebonden gebruikers, vestigingen, leerlingen en instellingen zijn nooit globaal te doorzoeken.
- Schoolbeheerfuncties: personeelsleden `users.view/manage`, schoolrollen `roles.manage`, leerlingen `students.view/manage`, vestigingen en schoolinstellingen `school.settings`. De directie krijgt alleen mogelijkheden uit haar rolmatrix.
- De eigen schoolinstellingen mogen nooit SMTP, globale platformconfiguratie, andere scholen of accountrechten van een andere school wijzigen.
- Bekijk bij dashboardproblemen de query's in `School\\DashboardController`; zowel dashboardtests als toegangscontrole draaien na elke wijziging.

## Gedeelde schoolinterface en loginregistratie

- Gebruik voor elke operationele schoolpagina `@extends('layouts.school')` met `@section('content')`; vermijd eigen <html>/<body>/sidebar of oude `layouts.school-students` zodat het menu en schoollogo niet verdwijnen.
- `school` en `school.settings.logo_path` moeten altijd uitsluitend de actieve geautoriseerde school vertegenwoordigen. Wijzig geen globale platformrechten wanneer je een schoolspecifiek scherm maakt.
- Platformloginlog: `LoginAudit::record()` schrijft login_success, login_failed (alleen gemaskeerde e-mailhint), logout, school_selected naar `login_events`; nooit wachtwoord, token, ruwe mislukte login-e-mail of sessie-ID bewaren. Toegang uitsluitend via `platform.admin`.
- Nieuwe migratie vanaf 24 nov 2026: `create_login_events_table`; tests: `tests/Feature/LoginAuditTest.php` en bestaande schooldashboard- en schoolrechten-tests. Bij uitrol altijd eerst migreren en testen.

## Schoolgebonden beheerpagina's

- Maak schoolbeheer ongeveer even rijk en consistent als Platformbeheer, maar voer elke query en mutatie uit binnen de actieve school. Vermijd hergebruik van platformcontrollers zonder expliciete `SchoolAccess` en tenant-scope.
- Schoolpersoneel: zoeken, status en rol filteren; schoolrol en schoolspecifieke actieve koppeling wijzigen zonder globale accountstatus te veranderen. `dko3_hidden_at` blijft uitgesloten.
- Schoolrollen: schoolgebonden matrix via `SchoolRoleCatalog::CATEGORIES`, edits uitsluitend van rollen met dezelfde `school_id`.
- Schoolinstellingen: upload logo naar `school-logos/{school_id}` via public disk, behoud fallback, verwijder oud logo enkel na geldige opslag en met eigen school-prefix; nieuwe foto zichtbaar in schoolselectie, schoolsidebar en bovenbalk.
- Maak schoolinstellingen nooit platformbreed. Houd tests in `SchoolScopedManagementTest.php` groen; gebruik de bestaande schoollayout met bovenaan schoollogo.

## Schoolpersoneel: wachtwoordinstellingen

- Behoud afzonderlijke tabelkolommen voor schoolrol, pivot-schoolstatus, globale accountstatus, wachtwoordstatus. Verwar globale status nooit met een schoolspecifieke status.
- `must_change_password=true` => `Nog in te stellen`; anders `password_set_at` gevuld => `Ingesteld`; anders `Onbekend`. Interpreteer `Onbekend` niet automatisch als nooit aangemeld.
- Wachtwoord instel-/resetlinks worden alleen als beveiligde mail aan de gekoppelde gebruiker verzonden via Laravel Password broker; toon of log nooit raw token/URL. Controleer actieve school, schoollidmaatschap, gebruikerstatus en `users.manage`. POST met CSRF en throttle; registreer verzending in de schoolaudit.
- Test school-isolatie en wachtwoordstatus vanuit `SchoolScopedManagementTest.php`.
