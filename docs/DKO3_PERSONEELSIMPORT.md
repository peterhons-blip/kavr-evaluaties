# Artelynx – DKO3-personeelsimport

## Doel

Importeer personeelsleden uit een **volledige** DKO3-export in één gekozen school. Gebruikers blijven globale accounts, maar schoolrollen en schoolactivering zijn afzonderlijk per school. Leerlingen vallen uitdrukkelijk buiten deze module.

## Stappen

1. Platformbeheer → **Personeelsleden** → **Importeren uit DKO3**.
2. Kies de school.
3. Upload een CSV of XLSX van maximaal 5 MB; op dit ogenblik worden de velden **voornaam, familienaam en e-mailadres** verwacht.
4. Bekijk de preview met aantallen nieuwe, bestaande en weggevallen leerkrachten. Controleer vooral de lijst met personeelsleden die gedeactiveerd worden.
5. Bevestig dat het bestand **alle huidige leerkrachten** van de gekozen school bevat.
6. Klik **Import definitief uitvoeren**.

**Er worden tijdens import geen e-mails verzonden.** 

## Verwerking

- **Nieuw e-mailadres**: nieuw account met een onbekend willekeurig wachtwoord, `must_change_password=true`, en een leerkrachtkoppeling met de geselecteerde school.
- **Bestaand e-mailadres**: voornaam en familienaam bijwerken, maar huidig wachtwoord, accountstatus en toegang tot andere scholen ongemoeid laten. Bestaande rol in dezelfde school blijft intact (bijvoorbeeld directeur).
- **Bestaande leerkracht ontbreekt**: in de gekozen school wordt de teacher-koppeling gedeactiveerd en met `dko3_hidden_at` verborgen. De persoon verschijnt ook bij filter *Inactief* niet meer. Een tweede schoolkoppeling blijft geldig. Historiek blijft bewaard.
- **Komt terug in een volgende DKO3-import**: verborgen schoolkoppeling wordt terug geactiveerd en zichtbaar.
- Foutieve of dubbele e-mailadressen blokkeren de hele import; geen halve synchronisaties.
- De import vindt plaats in een databanktransactie en schrijft een auditgebeurtenis met aantallen.
- Voorbeeldbestanden staan tijdelijk in private opslag en worden na bevestiging verwijderd.

De **globale accountstatus** is iets anders dan de **wachtwoordstatus**. Een actief account zonder ingesteld wachtwoord kan niet aanmelden zolang de eigenaar het geheime beginwachtwoord niet kent. Een nieuwe medewerker kan het wachtwoord via een beveiligde instellink instellen.

## Instellinks

In het personeelsledenoverzicht kun je filteren op school en **Wachtwoord = Nog instellen**. Selecteer vervolgens personeelsleden (alle huidige paginarijen met het checkboxje in de tabel) en kies **Wachtwoordinstellinks versturen**. Bulkverzending gebeurt pas na expliciete bevestiging, en maximaal 100 geselecteerden per actie. Controleer eerst SMTP en test de route met `MAIL_MAILER=log` voordat echte mails worden verstuurd.

Historische accounts hebben soms `password_set_at = null` en `must_change_password = false`. Deze krijgen terecht **Onbekend (oud account)**, niet ten onrechte **Ingesteld**. Bij een succesvolle wachtwoordreset wordt `password_set_at` ingevuld.

## Uitrol

Maak altijd eerst een back-up van de ontwikkelingsdatabank en voer daarna uit:

```powershell
git switch feature/school-users-roles
git pull --ff-only origin feature/school-users-roles
C:\php84\php.exe artisan migrate
C:\php84\php.exe artisan optimize:clear
C:\php84\php.exe artisan test
```

Voor het lezen van XLSX is de PHP-extensie **zip** nodig. Controleer met `C:\php84\php.exe -m`.

## Nog te valideren op het originele DKO3-bestand

De precieze exportkolommen en eventuele bijzondere rijen moeten nog met een echt DKO3-bestand worden gevalideerd. Pas de aliasmapping in `app/Support/Dko3TeacherSpreadsheet.php` aan voordat je voor het eerst een volledige live-sync bevestigt als de export afwijkt.

Importeren vanuit DKO3 heeft geen invloed op leerlingaccounts: die functionaliteit volgt in een afzonderlijke module.
