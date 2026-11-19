# PROJECT_CONTEXT.md
# Artelynx – Evaluatieplatform voor kunstacademies

**Productnaam:** Artelynx  
**Slogan:** Elk talent in ontwikkeling.  
**Projectstatus:** In ontwikkeling  
**Startproject:** Kunstacademie Voeren-Riemst  
**Architectuur:** Multischool / multi-tenant  
**Versiebeheer:** GitHub – `peterhons-blip/kavr-evaluaties`

## 1. Projectdoel

Het ontwikkelen van een modern, webgebaseerd evaluatieplatform voor kunstacademies en andere onderwijsinstellingen.

De applicatie vervangt de bestaande Excel-evaluatiefiches door een centrale omgeving waarin leerkrachten leerdoelen, jaarplannen, evaluaties en feedback kunnen beheren.

De eerste implementatie is bedoeld voor Kunstacademie Voeren-Riemst.

De applicatie moet vanaf het begin geschikt zijn voor meerdere scholen.

## 2. Technologie

- **Backend:** Laravel 13
- **PHP:** Minimaal 8.4
- **Databank:** MySQL/MariaDB
- **Frontend:** Laravel Blade, CSS
- **Ontwikkelomgeving:** Windows, XAMPP, VS Code
- **Versiebeheer:** GitHub
- **PDF-export:** Nog te implementeren
- **Productieserver:** Windows Server, installatie nog voor te bereiden

Lokale ontwikkeling:

- Projectmap: `C:\xampp\htdocs\kavr-evaluaties`
- URL: `http://localhost:8001`
- Databank: `kavr_evaluaties_dev`

De lokale PHP-versie kan verschillen per laptop. Compatibiliteit met de gekozen Laravel-versie moet behouden blijven.

## 3. Multischoolarchitectuur

Het platform moet meerdere scholen ondersteunen via één centrale applicatie.

Elke school beschikt over eigen:

- Leerlingen
- Leerkrachten en gebruikersrechten
- Lesgroepen
- Vakken
- Schooljaren en evaluatieperioden
- Leerdoelen
- Evaluaties
- Rapporten
- Huisstijl en instellingen

Een gebruiker kan aan meerdere scholen gekoppeld zijn, met eventueel verschillende rollen per school.

De toegang tot gegevens van andere scholen moet strikt worden verhinderd.

### Voorziene tabellen

- `schools`
- `school_user`
- `school_settings`

Schoolgebonden gegevens worden via een `school_id` gekoppeld aan de juiste school.

Deze architectuur is gepland, maar nog niet geïmplementeerd.

## 4. Gebruikersbeheer

Voorziene rollen:

- Platformadministrator
- Schooladministrator
- Directeur
- Administratief medewerker
- Leerkracht

Een gebruiker kan binnen verschillende scholen verschillende rollen hebben.

### Gebruikersgegevens

De bestaande tabel `users` is uitgebreid met:

- `first_name`
- `last_name`
- `is_active`
- `must_change_password`
- `last_login_at`

Het oorspronkelijke veld `name` bestaat voorlopig nog.

### Geplande authenticatiefuncties

- Account aanmaken door bevoegde administrator
- Uitnodiging via e-mail
- Wachtwoord instellen bij eerste activatie
- E-mailadres verifiëren
- Wachtwoord vergeten en herstellen
- Verplichte wachtwoordwijziging indien nodig
- Gebruikersaccounts activeren en blokkeren

Deze functionaliteiten zijn nog niet volledig geïmplementeerd.

## 5. Onderwijsstructuur

De applicatie ondersteunt:

- Verschillende schooljaren
- Meerdere evaluatieperioden
- Verschillende graden en leerjaren
- Vakken en instrumenten
- Leerkrachten die meerdere vakken geven
- Leerlingen die meerdere vakken volgen
- Individuele lessen
- Klassikale lessen
- Lesgroepen met leerlingen uit verschillende leerjaren

Leerlingen worden centraal beheerd en via inschrijvingen gekoppeld aan lesgroepen en vakken.

De methode voor het importeren van leerlingen en lesgroepen moet nog worden bepaald.

## 6. Evaluatiesysteem

De applicatie ondersteunt twee hoofdvormen:

### ICJ – Individueel competentiegericht jaarplan

- Evaluaties per leerling
- Individuele leerdoelen
- Evaluaties en feedback per periode
- Meerdere evaluatiemomenten
- Historiek over verschillende schooljaren

### KCJ – Klassikaal competentiegericht jaarplan

- Jaarplan op lesgroepniveau
- Gezamenlijke leerdoelen
- Klassikale evaluaties
- Individuele feedback per leerling
- Meerdere evaluatiemomenten

ICJ en KCJ worden gebouwd op één gedeeld evaluatiesysteem.

### Evaluatiemethoden

De beoordelingsmethode moet configureerbaar zijn per vak of lessituatie.

Mogelijke methoden:

- Beschrijvende feedback
- Competentiegericht beoordelen
- Punten
- Rubrics
- Combinaties van bovenstaande

De exacte beoordelingsschalen moeten nog worden vastgelegd.

## 7. Competenties en leerdoelen

Het systeem krijgt een centrale competentiebibliotheek.

In de aangeleverde voorbeeldfiches komen zes kerncompetenties voor:

1. Gedrevenheid
2. Creëren
3. Vakdeskundigheid
4. Onderzoeken
5. Samenwerken
6. Presenteren

Leerdoelen kunnen verschillen per vak, graad en opleiding.

Historische evaluaties mogen niet veranderen wanneer een bestaand leerdoel later wordt aangepast.

Daarom voorzien we versiebeheer of snapshots van gebruikte evaluatiecriteria.

## 8. PDF-export en DKO3

Evaluaties moeten kunnen worden geëxporteerd naar PDF.

Ondersteunde exports:

- Per individuele leerling
- Per klas of lesgroep
- Per evaluatieperiode
- Per schooljaar, waar nodig

De gegenereerde PDF's moeten geschikt zijn om in DKO3 te uploaden.

De exacte technische en administratieve vereisten voor deze PDF's moeten nog bevestigd worden.

## 9. Huidige databank

Reeds aangemaakte tabellen:

- `users`
- `school_years`
- `evaluation_periods`
- Laravel-standaardtabellen voor migraties, sessies, cache en jobs

De tabel `school_years` bevat onder andere naam, begin- en einddatum en actieve status.

De tabel `evaluation_periods` is gekoppeld aan `school_years`.

De overige onderwijs- en evaluatietabellen zijn nog niet geïmplementeerd.

## 10. Reeds gerealiseerd

- Laravel-project aangemaakt
- Lokale MySQL/MariaDB-databank geconfigureerd
- GitHub-repository gekoppeld
- LoginController geïmplementeerd
- Loginroutes ingesteld
- Responsive loginpagina ontworpen
- Basisauthenticatie getest
- Eerste dashboardpagina aangemaakt
- Aanmelden en afmelden getest
- User-model uitgebreid
- Voornaam en achternaam toegevoegd aan gebruikersmodel
- Eerste lokale gebruikersaccount aangemaakt

### Visuele huisstijl

Voorlopige kleuren:

- Donkerblauw: `#142b45`
- Turquoise: `#59aaa5`
- Goud: `#e5a65b`
- Roze: `#c97f9a`
- Achtergrond: `#f2f5f7`

De loginpagina vormt de visuele basis voor de rest van de applicatie.

## 11. Nog te ontwikkelen

### Prioriteit 1 – Basisarchitectuur

- Multischooltabellen
- Schoolgebonden gebruikersrollen
- Toegangscontrole per school
- Schoolselectie na aanmelden
- Beveiliging tegen toegang tot gegevens van andere scholen

### Prioriteit 2 – Gebruikersbeheer

- Accountuitnodigingen
- Wachtwoordinstelling en -herstel
- E-mailverificatie
- Gebruikersbeheer
- Rollen en rechten
- Profielbeheer

### Prioriteit 3 – Onderwijsstructuur

- Vakken
- Graden en leerjaren
- Leerlingen
- Leerkrachten
- Lesgroepen
- Inschrijvingen
- Importmogelijkheden

### Prioriteit 4 – Evaluatiefunctionaliteit

- Competentiebibliotheek
- Leerdoelen
- ICJ-jaarplannen
- KCJ-jaarplannen
- Evaluatiemomenten
- Resultaten en feedback
- Historiek

### Prioriteit 5 – Rapportering

- PDF-export
- Export per leerling
- Export per lesgroep
- Export per periode
- DKO3-compatibiliteit

## 12. Ontwikkelafspraken

- Ontwikkel in kleine, controleerbare stappen.
- Gebruik Laravel-migraties voor databankwijzigingen.
- Wijzig bestaande migraties die al uitgevoerd zijn niet zomaar.
- Verwijder geen bestaande gegevens zonder expliciete toestemming.
- Zorg dat alle schoolgebonden gegevens correct geïsoleerd zijn.
- Gebruik geen echte leerlinggegevens tijdens onveilige ontwikkeltests.
- Bewaar wachtwoorden en API-sleutels uitsluitend in veilige configuratie.
- Commit geen `.env`-bestanden naar GitHub.
- Houd de applicatie compatibel met Windows Server.
- Test functionaliteit lokaal voordat deze naar productie gaat.
- Gebruik duidelijke namen en overzichtelijke code.
- Documenteer belangrijke architectuurbeslissingen.

## 13. Werken op meerdere laptops

De broncode wordt gesynchroniseerd via GitHub.

Standaardwerkwijze:

1. `git pull`
2. Ontwikkelen en testen
3. `git add .`
4. `git commit -m "Beschrijving"`
5. `git push`

De lokale databanken worden niet automatisch via GitHub gesynchroniseerd.

Laravel-migraties zorgen voor een consistente databankstructuur.

Testgegevens kunnen later via seeders worden aangemaakt.

## 14. Eerstvolgende ontwikkelstap

1. `AGENTS.md` aanmaken.
2. Projectcontext en architectuurafspraken vastleggen in GitHub.
3. Multischoolarchitectuur implementeren.
4. Gebruikersrollen en accountactivatie ontwikkelen.
5. Dashboard uitbreiden.

**Belangrijk:** voordat nieuwe schoolgebonden tabellen worden gemaakt, moet de multischoolarchitectuur zijn uitgewerkt.