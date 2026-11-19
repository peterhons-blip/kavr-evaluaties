# Artelynx – gebruikers- en rechtenbeheer

## Architectuur

- Eén globaal account per persoon in `users` (uniek e-mailadres).
- Eén koppeling per gebruiker en school in `school_user`, met een schoolgebonden rolcode en afzonderlijke actieve status.
- Per school eigen definities in `school_roles`, met een JSON-lijst van toegestane functierechten.
- `SchoolAccess::allows($user, $school, $permission)` controleert steeds accountstatus, schoolstatus, koppelingstatus, rol binnen diezelfde school en de toegewezen toestemming.
- `is_platform_admin` is een afzonderlijk globaal recht. Het verleent niet automatisch inzage in vertrouwelijke evaluaties.

## Standaardrollen

- `school_admin`: schoolbeheer
- `director`: directeur
- `administration`: administratie
- `coordinator`: vakcoördinator
- `teacher`: leerkracht
- `jury`: jurylid
- `viewer`: alleen lezen

Definities en rechten in `app/Support/SchoolRoleCatalog.php`. Rollen kunnen per school apart worden aangepast; er zijn ook maatwerkrollen mogelijk. Toegewezen rolcodes blijven ongewijzigd, omdat `school_user.role` hiernaar verwijst.

## Rechten

`users.view`, `users.manage`, `roles.manage` zijn al gekoppeld aan concrete routes en schermen. De rechten `school.settings`, `students.*`, `groups.*`, `goals.manage`, `evaluations.*` en `reports.*` zijn **vooruitgepland**; zij verlenen nog geen toegang tot niet-bestaande modules.

Wanneer lesgroepen en evaluaties worden gebouwd, MOET naast school_id en het rolrecht ook een lesgroep-/leerlingtoewijzing of expliciete directiebevoegdheid gecontroleerd worden. **Een leerkracht met `evaluations.view` mag niet alle evaluaties van een school zien.**

## Waar beheer je dit?

- Platform → Gebruikers: globaal overzicht, accounts aanmaken/bewerken, schoolrollen, status en instellinks.
- Platform → Scholen → Gebruikers: gebruikers van een specifieke school.
- Platform → Scholen → Rollen & rechten: afzonderlijke rolmatrix voor die school.
- Platform → Beheerhistoriek: wijzigingen aan accounts, koppelingen en rollen.
- Schooldashboard → Gebruikers beheren: gebruikers van eigen school, alleen met `users.view` of `users.manage`.
- Schooldashboard → Rollen & rechten: eigen schoolrollen, alleen met `roles.manage`.

Schoolbeheerders kunnen nieuwe accounts aanmaken, bestaande accounts koppelen en rollen wijzigen zonder toegang tot gebruikers van andere scholen.

## Wachtwoorden en e-mail

Voor een nieuw account kan een beheerder het wachtwoord leeg laten. Artelynx genereert dan een onbekend, willekeurig wachtwoord. Daarna kan een beveiligde instellink via de wachtwoordbroker van Laravel worden verstuurd. Op de loginpagina staat ook een herstelroute.

**Voor lokaal testen:**

```dotenv
MAIL_MAILER=log
```

In deze modus komt de resetlink in `storage/logs/laravel.log` terecht. Deel dit bestand of de link nooit via Git. Stel vóór echt gebruik betrouwbare SMTP-configuratie in, met een bestaand afzenderadres. Zet het account actief om aanmelden mogelijk te maken. Nieuwe accounts die vanuit de schoolomgeving worden gemaakt, worden actief aangemaakt maar hebben totdat de instellink is gebruikt een onbekend wachtwoord.

De applicatie heeft op dit moment nog geen verplichte MFA of geavanceerde uitnodigingsstatus-flow. Die worden vóór echte productie-ingebruikname apart beoordeeld.

## Beveiligingsregels

1. Een gedeactiveerd account kan niet aanmelden en verliest toegang via een bestaande schoolsessie.
2. Een gedeactiveerde school of schoolkoppeling verleent geen toegang.
3. Een schoolbeheerder kan geen gebruikers of rollen van andere scholen wijzigen.
4. Schoolbeheerders krijgen nooit via een schoolrol `is_platform_admin`.
5. Een beheerder kan zijn eigen effectieve beheertoegang niet eenvoudig uitschakelen in de schoolschermen.
6. Toegewezen schoolrollen kunnen niet worden verwijderd, en rolcodes van reeds toegewezen rollen blijven gelijk.
7. Wijzigingen worden geregistreerd in `access_audit_events` zonder wachtwoorden of tokens.
8. Er is bewust geen harde verwijderfunctie voor gebruikers: deactiveren bewaart historische relaties.

## Lokale installatie en controle

Maak eerst een back-up van de ontwikkelingsdatabank.

```powershell
git fetch origin
git switch --track origin/feature/school-users-roles
C:\php84\php.exe artisan migrate
C:\php84\php.exe artisan optimize:clear
C:\php84\php.exe artisan route:list
C:\php84\php.exe artisan test
```

Open `/platform/users`, `/platform/schools/{id}/roles`, en via een schoolomgeving `/school/gebruikers` en `/school/rollen`. Test met twee verschillende scholen en twee niet-beheerders. Laat voorlopig geen echte leerlingenevaluaties toe.

## Acceptatietests

- Maak een gebruiker aan zonder initieel wachtwoord; controleer dat geen wachtwoord wordt weergegeven.
- Geef dezelfde gebruiker verschillende rollen in twee scholen.
- Verwijder een schoollidmaatschap en verifieer dat de andere school werkt.
- Schakel een account uit en verifieer dat een bestaande sessie geen schooldashboard meer kan openen.
- Voeg een maatwerkrol toe in school A en controleer dat deze niet in school B verschijnt.
- Laat een schoolbeheerder een gebruiker in zijn eigen school koppelen, maar niet in school B.
- Verander een rolrecht in school A en verifieer dat de rol in school B niet mee verandert.
- Controleer de beheerhistoriek.
- Test instellinks met `MAIL_MAILER=log` en daarna met echte SMTP vóór productie.

De testworkflow `.github/workflows/artelynx-tests.yml` voert PHP-syntaxcontrole, Blade-compilatie, routecontrole en Laravel-featuretests uit op pull requests.
