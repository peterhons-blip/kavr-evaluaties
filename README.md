# Artelynx – Evaluatieplatform

### Kunstacademie Voeren-Riemst

Een modern, webgebaseerd evaluatieplatform voor kunstacademies en andere onderwijsinstellingen.

De applicatie vervangt de bestaande Excel-evaluatiefiches door één centrale omgeving voor het beheren van leerlingen, lesgroepen, leerdoelen, evaluaties en rapporten.

Het platform wordt ontwikkeld voor **Kunstacademie Voeren-Riemst**, maar is vanaf het begin ontworpen om meerdere scholen te ondersteunen.

## Functionaliteiten

Het evaluatieplatform wordt ontwikkeld met ondersteuning voor:

- Individuele evaluaties (ICJ)
- Klassikale evaluaties (KCJ)
- Competentiegericht evalueren
- Verschillende evaluatiemethoden per vak
- Meerdere evaluatiemomenten per periode
- Individuele en klassikale feedback
- Historiek over meerdere schooljaren
- Leerlingen, leerkrachten, vakken en lesgroepen
- Gebruikersrollen en toegangsrechten
- PDF-export voor administratieve verwerking in DKO3
- Multischoolondersteuning

**Status:** Het project is in actieve ontwikkeling. Een groot deel van deze functionaliteiten is nog gepland en niet beschikbaar.

## Technologie

| Onderdeel | Technologie |
|---|---|
| Backend | Laravel 13 |
| Programmeertaal | PHP 8.4+ |
| Databank | MySQL / MariaDB |
| Frontend | Blade, HTML, CSS, JavaScript |
| Ontwikkelomgeving | Windows / XAMPP |
| Editor | Visual Studio Code |
| Versiebeheer | Git / GitHub |

## Lokale installatie

### 1. Repository clonen

```bash
git clone https://github.com/peterhons-blip/kavr-evaluaties.git
cd kavr-evaluaties
```

### 2. PHP-afhankelijkheden installeren

Gebruik PHP 8.4 of hoger.

```bash
composer install
```

### 3. Omgevingsbestand aanmaken

Kopieer `.env.example` naar `.env`.

Configureer de databank:

```dotenv
APP_NAME="KA-VR Evaluaties"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kavr_evaluaties_dev
DB_USERNAME=root
DB_PASSWORD=
```

De databasegebruiker en het wachtwoord zijn voorbeelden voor een lokale XAMPP-installatie.

### 4. Applicatiesleutel aanmaken

```bash
php artisan key:generate
```

### 5. Databank voorbereiden

Maak de databank `kavr_evaluaties_dev` aan in MySQL/MariaDB.

Voer daarna uit:

```bash
php artisan migrate
```

### 6. Applicatie starten

```bash
php artisan serve --port=8001
```

Open de applicatie via:

http://localhost:8001/login

Op Windows kan het nodig zijn om het volledige pad naar PHP op te geven, bijvoorbeeld `C:\php84\php.exe`, afhankelijk van de lokale installatie.

Gebruikersaccounts worden voorlopig handmatig aangemaakt. Publieke registratie is niet beschikbaar.

## Projectstructuur

```text
app/              Laravel-modellen en controllers
database/         Migraties en toekomstige seeders
resources/views/  Blade-templates en interface
routes/           Webroutes
public/           Publiek toegankelijke bestanden
tests/            Automatische tests
```

## Multischoolarchitectuur

Het platform wordt ontworpen volgens een multi-tenant architectuur.

Iedere school krijgt eigen leerlingen, lesgroepen, vakken, evaluaties, instellingen en gebruikersrechten.

Eenzelfde gebruiker kan aan meerdere scholen verbonden zijn.

De beveiliging moet ervoor zorgen dat gegevens van verschillende scholen strikt gescheiden blijven.

Deze architectuur is momenteel in voorbereiding.

## Projectdocumentatie

Voor meer informatie:

- [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md) – Projectdoelstellingen, architectuur, databank en ontwikkelplanning.
- [AGENTS.md](AGENTS.md) – Programmeerafspraken en instructies voor AI-assistenten.

## Beveiliging en privacy

De applicatie zal persoonsgegevens van leerlingen verwerken.

Daarom zijn authenticatie, autorisatie, privacybescherming en veilige gegevensopslag belangrijke uitgangspunten.

Het project is momenteel niet klaar voor gebruik met echte leerlinggegevens in een publieke productieomgeving.

## Ontwikkeling

**Versiebeheer:** GitHub

**Repository:** https://github.com/peterhons-blip/kavr-evaluaties

**Huidige ontwikkelfase:** basisarchitectuur, gebruikersbeheer en multischoolondersteuning.

## Licentie

De software wordt ontwikkeld als maatwerkproject. Er is momenteel geen publieke open-sourcelicentie toegekend.

Alle rechten voorbehouden, behoudens afzonderlijke contractuele afspraken.