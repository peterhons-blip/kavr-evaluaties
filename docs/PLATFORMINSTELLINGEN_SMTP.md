# Artelynx – platforminstellingen en SMTP

## Overzicht

Alleen een **platformbeheerder** kan deze globale instellingen wijzigen via **Platformbeheer → Platforminstellingen**, URL `/platform/settings`.

De pagina bevat:
- Platformnaam
- Support-e-mailadres
- Standaardtijdzone (Brussel/UTC; bestemd voor toekomstige platformfunctionaliteit)
- Uitgaande e-mail: logmodus / SMTP, SMTP-server, poort, beveiliging, account, geheim, afzenderadres en afzendernaam
- **Testmail versturen** met eigen ontvanger
- Datum en uitkomst van de laatste test (geen mailgeheimen in audit)

De bestaande wachtwoordreset- en personeelsuitnodigingsstromen gebruiken na het instellen automatisch de centrale mailconfiguratie.

## Configureren en testen

1. Update de ontwikkelbranch en voer `php artisan migrate` en `php artisan optimize:clear` uit.
2. Open `http://localhost:8001/platform/settings` als platformbeheerder.
3. Kies **Logmodus**, vul afzender in, sla op en verstuur een testbericht. Er wordt **geen echte e-mail verstuurd**; inspecteer `storage/logs/laravel.log`.
4. Kies **SMTP**, vul server, poort, beveiliging, gebruikersnaam, afzender en een **nieuw, veilig app-wachtwoord** in. Sla op.
5. Vul een testontvanger in en klik **Testmail versturen**. Bij succes: SMTP-server heeft de verzending aanvaard. Controleer vervolgens echt of het bericht toekomt (inclusief spam).
6. Test als laatste de **Wachtwoord vergeten?**-flow met één testaccount. Controleer of de absolute resetlink naar de juiste `APP_URL` verwijst.

### Google Workspace / Gmail

- Server: `smtp.gmail.com`
- Poort: `587`
- Beveiliging: `STARTTLS`
- Gebruikersnaam: toegestaan volledig mailboxadres
- Wachtwoord: door Google toegelaten **app-wachtwoord**, geen normaal accountwachtwoord
- Afzenderadres: meestal dezelfde mailbox, tenzij de account geautoriseerd is om namens een alias te verzenden

Google Workspace-beheerinstellingen of accountbeleid kunnen app-wachtwoorden blokkeren. In dat geval is een door de Workspace-beheerder goedgekeurde SMTP-oplossing nodig. **Nooit echte wachtwoorden in broncode, Git, chatberichten of documentatie zetten.**

## Beveiliging

- Het SMTP-geheim is versleuteld opgeslagen met Laravel's `encrypted` cast en `APP_KEY`.
- Het geheim wordt nooit teruggezet in het formulier en blijft bewaard als het wachtwoordveld leeg is.
- Alleen platformbeheerders hebben toegang tot deze routes.
- Het testendpoint is beperkt tot drie requests per minuut.
- Fouten gaan naar de Laravel-log; de UI geeft een neutrale melding.
- De test slaat geen wachtwoorden of volledige foutdetails op in de beheerhistoriek.
- Voor de versleutelde configuratie moet dezelfde `APP_KEY` behouden blijven zolang de database hiermee werkt. Voorzie veilige back-ups van sleutel en database.
- Echte mails worden **nooit** verzonden in logmodus.

## Opmerkingen

Deze instellingen gelden voor het **volledige platform**, dus voor alle academies. Schoolspecifieke logo's en kleuren blijven onder het beheer van de individuele school.

Een geslaagde SMTP-test bewijst dat het bericht door de mailserver is aanvaard; bezorging in de inbox moet afzonderlijk worden gecontroleerd. Bij een onbereikbare server of mislukte authenticatie toont het systeem een fout en kan de beheerder de configuratie corrigeren.
