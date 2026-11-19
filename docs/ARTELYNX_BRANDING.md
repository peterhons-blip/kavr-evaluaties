# Artelynx – merkbestanden

De door de gebruiker goedgekeurde ronde lynx-badge heeft een witte cirkel met het donkere lynxoog en kleurrijke lijnen, zonder blauwe achtergrond.

| Bestand | Gebruik |
| --- | --- |
| `public/images/branding/artelynx-lynx-original.png` | Originele PNG van de goedgekeurde ronde badge voor toekomstig gebruik en eventuele export |
| `public/images/branding/artelynx-lynx.webp` | Geoptimaliseerde versie voor dashboards, achtergronden en interactieve UI |
| `public/images/branding/artelynx-lynx-128.png` | Compacte PNG-variant voor kleine UI-onderdelen |
| `public/favicon.ico` | Browserfavicon (meerdere formaten, witte ronde badge) |

De **volledige Artelynx-woordmerklogo's** blijven beschikbaar als `public/images/artelynx-logo.webp` en `public/images/artelynx-logo-tagline.webp`.

Voor tabbladen gebruiken de Blade-layouts `<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=3">` om verouderde favicon-cache te vermijden. Het verouderde `public/artelynx-favicon.svg` wordt niet meer gekoppeld.

Bij toekomstige wijzigingen: behoud de ronde badge als afzonderlijk branding-item, naast het volledige woordmerk. Verander nooit schoolgebonden logo's in `storage/app/public/school-logos` bij een platformrebranding.

Dashboard: het schooldashboard gebruikt echte `School::withCount`-gegevens voor vestigingsplaatsen, schoolrollen en (alleen waar toegestaan) gebruikers. Leerlingen, lesgroepen en evaluaties worden als 'Binnenkort' aangeduid zolang de bijbehorende functies nog niet geïmplementeerd zijn.
