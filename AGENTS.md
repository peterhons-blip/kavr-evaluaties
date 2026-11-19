# AGENTS.md – Artelynx Evaluatieplatform

## 1. Projectcontext

De officiële merknaam van de applicatie is **Artelynx** (voorheen EvolvIT). Gebruik Artelynx in alle zichtbare productteksten, branding en paginatitels. Bestaande technische namen, databanktabelnamen en repositorypaden hoeven niet hernoemd te worden.

Dit project is een webgebaseerd evaluatieplatform voor Kunstacademie Voeren-Riemst, ontworpen om later meerdere scholen te ondersteunen.

Lees vóór belangrijke wijzigingen altijd `PROJECT_CONTEXT.md`.

De bestaande Excel-gebaseerde ICJ- en KCJ-evaluatiefiches worden vervangen door een centrale webapplicatie.

## 2. Technologie

- Laravel 13
- PHP 8.4 of hoger, met compatibiliteit voor PHP 8.4
- MySQL/MariaDB
- Laravel Blade
- HTML, CSS en JavaScript
- GitHub voor versiebeheer
- Windows en XAMPP als ontwikkelomgeving

Gebruik zoveel mogelijk bestaande Laravel-functionaliteit en vermijd onnodige externe pakketten.

## 3. Multischoolarchitectuur

**Dit is een verplichte architectuureis.**

De applicatie moet vanaf het begin meerdere scholen kunnen ondersteunen.

### Belangrijke regels

- Elke school krijgt een uniek `id` in de tabel `schools`.
- Schoolgebonden gegevens moeten gekoppeld zijn aan de juiste school.
- Een gebruiker kan bij meerdere scholen horen.
- Gebruikers kunnen per school verschillende rollen hebben.
- Controleer schooltoegang bij iedere schoolgebonden actie.
- Vertrouw nooit uitsluitend op een `school_id` uit een formulier of URL.
- Een gebruiker mag nooit gegevens van een andere school kunnen bekijken of wijzigen zonder expliciete bevoegdheid.
- Voorzie schoolgebonden instellingen, vakken, leerdoelen en evaluaties.

Gebruik Laravel Policies, middleware en consistente databasequery's om de toegangscontrole af te dwingen.

Een globale platformadministrator krijgt niet automatisch toegang tot vertrouwelijke leerlingenevaluaties.

## 4. Databankregels

Gebruik uitsluitend Laravel-migraties voor wijzigingen aan de databankstructuur.

- Gebruik foreign keys voor relaties.
- Voeg relevante indexen toe.
- Gebruik duidelijke tabel- en kolomnamen.
- Maak geen dubbele gegevens aan.
- Voorzie timestamps.
- Gebruik waar nodig soft deletes.
- Verwijder nooit zomaar bestaande gegevens.
- Gebruik nooit `migrate:fresh` op een bestaande databank zonder expliciete toestemming.

Wijzig migraties die al uitgevoerd zijn niet achteraf. Maak voor aanpassingen een nieuwe migratie.

## 5. Gebruikers en authenticatie

Gebruik het bestaande Laravel-authenticatiesysteem.

De tabel `users` bevat onder andere:

- `name` (tijdelijk behouden)
- `first_name`
- `last_name`
- `email`
- `password`
- `email_verified_at`
- `is_active`
- `must_change_password`
- `last_login_at`

Gebruik voortaan `first_name` en `last_name` voor de weergave van de naam.

Gebruik nooit leesbare wachtwoorden in de databank.

Voorzie bij verdere ontwikkeling:

- Accountuitnodigingen
- Wachtwoordinstelling bij eerste activatie
- E-mailverificatie
- Wachtwoordherstel
- Accountblokkering
- Schoolgebonden rollen en rechten

De aanwezigheid van een veld of interface betekent niet dat de bijbehorende beveiliging al geïmplementeerd is.

## 6. Rollen en rechten

Voorziene rollen:

- `platform_admin`
- `school_admin`
- `director`
- `administration`
- `teacher`

Rollen binnen een school moeten gekoppeld zijn aan de combinatie van gebruiker en school.

Controleer rechten altijd op de server, niet alleen door knoppen of menu's te verbergen.

## 7. Onderwijsstructuur

De applicatie moet ondersteunen:

- Schooljaren en evaluatieperioden
- Graden en leerjaren
- Verschillende vakken en instrumenten
- Meerdere leerkrachten per vak of lesgroep
- Meerdere vakken per leerling
- Individuele en klassikale lessen
- Lesgroepen met leerlingen uit verschillende leerjaren

Leerlingen worden gekoppeld aan vakken en lesgroepen via inschrijvingen.

## 8. Evaluatiesysteem

Ondersteun zowel ICJ als KCJ.

### ICJ

Individuele jaarplannen en evaluaties per leerling.

### KCJ

Klassikale jaarplannen en evaluaties per lesgroep, met mogelijkheid tot individuele feedback.

Beide systemen moeten gebruikmaken van een gedeeld evaluatiemodel.

Ondersteun meerdere evaluatiemomenten per periode.

Evaluatiemethoden moeten configureerbaar zijn per vak of lessituatie.

Mogelijke evaluatiemethoden:

- Beschrijvende evaluaties
- Competentiebeoordeling
- Punten
- Rubrics
- Combinaties

Behoud historische evaluatiegegevens en de gebruikte beoordelingscriteria.

## 9. PDF en DKO3

Voorzie toekomstige PDF-export:

- Per leerling
- Per lesgroep
- Per evaluatieperiode
- Per schooljaar waar nodig

De output moet geschikt zijn voor de administratieve verwerking in DKO3.

De exacte DKO3-vereisten moeten vóór implementatie worden bevestigd.

## 10. Frontend en huisstijl

Gebruik de bestaande loginpagina als visuele basis.

Kleuren:

- Donkerblauw: `#142b45`
- Turquoise: `#59aaa5`
- Goud: `#e5a65b`
- Roze: `#c97f9a`
- Achtergrond: `#f2f5f7`

De interface moet overzichtelijk, professioneel en responsive zijn.

Gebruik herbruikbare Blade-layouts en componenten om dubbele HTML- en CSS-code te vermijden.

## 11. Beveiliging en privacy

De applicatie verwerkt persoonsgegevens van leerlingen.

Daarom:

- Pas server-side validatie toe.
- Gebruik Laravel-authenticatie en autorisatie.
- Gebruik CSRF-bescherming.
- Gebruik veilige databasequery's via Eloquent of de querybuilder.
- Bescherm persoonsgegevens tegen ongeautoriseerde toegang.
- Log geen wachtwoorden, tokens of vertrouwelijke evaluatie-inhoud.
- Houd `.env` en andere geheimen buiten Git.
- Gebruik geen echte leerlinggegevens in openbare testomgevingen.
- Zorg dat exports uitsluitend beschikbaar zijn voor bevoegde gebruikers.
- Beperk toegang tot bestanden en rapporten per school.
- Maak veilige databankback-ups mogelijk.

## 12. Codekwaliteit

- Volg de Laravel-conventies.
- Gebruik duidelijke namen voor klassen, methoden en variabelen.
- Houd controllers overzichtelijk.
- Gebruik services voor complexere bedrijfslogica.
- Gebruik Form Requests voor uitgebreide validatie.
- Gebruik Policies voor autorisatie.
- Vermijd dubbele code.
- Schrijf tests voor belangrijke functionaliteit.
- Gebruik Nederlandse teksten in de gebruikersinterface.
- Gebruik bij voorkeur Engelse namen in de programmacode.

## 13. Werken met Codex

Bij elke programmeeropdracht:

1. Lees `PROJECT_CONTEXT.md` en dit bestand.
2. Controleer de bestaande code voordat wijzigingen worden voorgesteld.
3. Hergebruik bestaande functionaliteit waar mogelijk.
4. Respecteer de multischoolarchitectuur.
5. Maak geen ongevraagde ingrijpende wijzigingen.
6. Verwijder geen tabellen, records of bestanden zonder toestemming.
7. Leg belangrijke technische keuzes kort uit.
8. Geef aan welke bestanden aangepast zijn.
9. Controleer op syntaxfouten en voer relevante tests uit.
10. Meld duidelijk welke stappen nog handmatig nodig zijn.

Voer geen migraties uit op productie zonder expliciete opdracht.

## 14. Ontwikkelwerkwijze

De ontwikkelaar wil de code begrijpen en zelf kunnen volgen.

Werk daarom in kleine, controleerbare stappen.

Geef bij belangrijke wijzigingen uitleg over:

- Wat er gewijzigd wordt.
- Waarom de wijziging nodig is.
- Welke bestanden betrokken zijn.
- Hoe de functionaliteit getest kan worden.

Vermijd onnodige complexiteit.

## 15. GitHub en omgevingen

Repository:

`https://github.com/peterhons-blip/kavr-evaluaties`

De ontwikkeling vindt plaats op meerdere Windows-laptops.

- Gebruik Git voor broncode.
- Gebruik migraties voor databankstructuur.
- Gebruik seeders voor reproduceerbare testgegevens.
- Commit geen `.env`, wachtwoorden of echte leerlinggegevens.
- Respecteer bestaande wijzigingen van andere ontwikkelaars.
- Push niet automatisch naar productie.

De productieomgeving moet compatibel zijn met de gebruikte Laravel- en PHP-versies.

## 16. Huidige ontwikkelfase

Reeds aanwezig:

- Laravel-installatie
- MySQL-verbinding
- LoginController
- Loginpagina
- Eenvoudig dashboard
- Basisgebruikersmodel
- Schooljaren
- Evaluatieperioden
- Extra gebruikersvelden

Nog niet geïmplementeerd:

- Multischooldatabankstructuur
- Gebruikersrollen
- Accountuitnodigingen
- Verplichte wachtwoordwijziging
- Volledige e-mailverificatie
- Leerlingen en lesgroepen
- Evaluatiemodellen
- PDF-export

**Eerstvolgende prioriteit: implementeer de multischoolbasis voordat nieuwe schoolgebonden onderdelen worden ontwikkeld.**