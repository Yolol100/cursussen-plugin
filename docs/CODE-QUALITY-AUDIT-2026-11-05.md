# Repository 7/37 — Cursussen plugin: codekwaliteitslog

Auditdatum: **5 november 2026**. Basis `main`-commit: `3c3a41d7eabd7e02f010298e5c7aab6d9c6d8052`. Wijzigingen in [draft PR #1](https://github.com/Yolol100/cursussen-plugin/pull/1), branch `quality/shortcode-instance-ids-20261105`. **Geen merge, productie- of klantsitewijziging.**

## Vastgestelde fout en gerichte reparatie

**CUR-001 — dubbele accordion-ID's (toegankelijkheid / interactie).**

Bij twee `[toon_cursussen]` shortcodes op dezelfde pagina bevatten de mobiele accordions van dezelfde cursus identieke `id`-attributen (`sda-cursus-button-ID-INDEX`, `sda-cursus-panel-ID-INDEX`). `aria-controls` en `aria-labelledby` zijn hierdoor ambigu; JavaScript gebruikt `document.getElementById()` waardoor een klik het paneel van een ander shortcodeblok kan selecteren.

- **Rood:** nieuwe `tests/wp-cli-shortcode-rest-smoke.php` creëerde uitsluitend in geïsoleerd WordPress één gepubliceerde cursus, rendert beide shortcodes en verifieert unieke button/panel-ID's. [CI #41885839848](https://github.com/Yolol100/cursussen-plugin/actions/runs/41885839848) faalde op WordPress 6.3/PHP 7.4 en WordPress 7.0/PHP 8.3 met `Multiple shortcodes share accordion button/panel IDs`.
- **Fix:** `includes/class-shortcodes.php` geeft iedere shortcode-render een volgnummer. De eerste instantie behoudt exact de oude ID's; alleen extra instanties krijgen een suffix. Beide ID's en bijbehorende ARIA-verwijzingen worden vanuit dezelfde bron opgebouwd.
- **Groen:** [CI #41886229159](https://github.com/Yolol100/cursussen-plugin/actions/runs/41886229159), vier jobs geslaagd: 2 PHP-syntaxversies en 2 echte WordPress/MySQL integratieruns. De integratietest controleert ook de publieke `cursussen/v1/all` API met synthetische gepubliceerde cursusdata.

## Online vergelijking

- [W3C techniek H93 — unieke HTML-ID's](https://www.w3.org/TR/WCAG20-HTML-TECHS/H93.html): dit voorkomt foutieve verwijzingen tussen interactieve controls. De reparatie behoudt het bestaande native button/region-patroon.
- [WordPress Shortcode API](https://developer.wordpress.org/apis/shortcode/): callbacks moeten onafhankelijk kunnen renderen op dezelfde pagina, inclusief zonder attributen.
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/): de bestaande publieke read-only route houdt `__return_true` als expliciete permission callback. De query filtert op `publish`; dit is geen bewijs van afwezigheid van onbedoelde metadata in alle combinaties.
- [10up WP Scaffold](https://github.com/10up/wp-scaffold): repository-native runtime- en bronchecks; het bestaande minimale pluginproject is niet omgebouwd naar een nieuwe architectuur.

## Andere inspecties en open punten

- 17 originele werkbestanden; PHP, JavaScript, CSS, shortcode, CPT, meta-boxes, REST en uninstallpaden bekeken. Geen bevestigde exacte dubbele bronbestanden verwijderd.
- De originele CI controleerde alleen PHP-syntaxis. Nu zijn WP6.3/PHP7.4 en WP7.0/PHP8.3 toegevoegd met echte WordPress/MySQL, pluginactivatie, shortcode-output, ARIA en de read-only REST-route.
- Asset-enqueue volgt de bestaande `Assets_Manager`; geen CSS selectors of scripts gewijzigd. Er zijn geen frontend dependencies bijgeplaatst.
- De voorgedefinieerde shortcode- en JSON-gegevens, admin meta keys, CPT/taxonomie en pluginversie `1.4.2` zijn intact.
- **Niet bewezen:** browser-/mobile- en toetsenbordgedrag, daadwerkelijke DOM-focus in meerdere widgets, REST-query met grote aantallen, caching, WordPress multisite, uninstall met/zonder opt-in op staging, privacyreview en installatie-ZIP-bronpariteit.
- **Potentiële interfacekwestie:** het cursusoverzicht toont opleidingstype, data en tijden maar gebruikt de cursusnaam vooral voor het verborgen label bij de aanmeldknop. Een inhoudelijke frontend-aanpassing is niet zonder UX/klantakkoord gedaan.
- Deze branch is niet de release. Voor een nieuwe versie eerst changelog, metadata, ZIP-bouw, browseracceptatie en menselijke review afronden.

**Laatste CI moet overeenkomen met de actuele PR-head.** Na het logbestand opnieuw controleren. Rollback: PR sluiten; `main` en bestaande sites worden niet gewijzigd.
