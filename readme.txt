=== Cursussen ===
Contributors: sodriveacademie
Tags: courses, cursus, shortcode, custom post type
Requires at least: 6.3
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Beheer cursussen in WordPress en toon ze met een shortcode of publieke REST API.

== Description ==

Cursussen registreert een custom post type voor cursusinformatie met velden voor startdatum, opleidingstype, tijden, bijeenkomsten, inschrijfstatus en beschikbare plekken. Gebruik de shortcode `[toon_cursussen]` om een responsive cursusoverzicht te tonen.

== Installation ==

1. Upload de pluginmap naar `/wp-content/plugins/` of installeer de zip via WordPress.
2. Activeer de plugin.
3. Voeg cursussen toe via het menu Cursussen.
4. Plaats `[toon_cursussen]` op een pagina.

== Shortcode ==

`[toon_cursussen]`

Optionele attributen:

* `categorie` - filter op categorie-slug.
* `aantal` - maximaal aantal cursussen, standaard 50, maximaal 100.
* `inschrijven_url` - URL van de inschrijfpagina, standaard `/inschrijven/`.
* `layout` - weergavevariant: `responsive`, `desktop` of `mobile`.

Voorbeeld:

`[toon_cursussen categorie="bhv" aantal="20" inschrijven_url="/inschrijven/"]`

== REST API ==

Publieke endpoints:

* `/wp-json/cursussen/v1/all?page=1&per_page=20`
* `/wp-json/cursussen/v1/filter?categorie=bhv&page=1&per_page=20`

De endpoints geven alleen gepubliceerde cursusdata terug. Gebruik deze endpoints alleen wanneer gepubliceerde cursusdata publiek beschikbaar mag zijn.

== Frequently Asked Questions ==

= Worden cursusdata verwijderd bij uninstall? =

Standaard niet. Data wordt alleen verwijderd wanneer de optie `cursussen_plugin_delete_data_on_uninstall` op true staat.

== Changelog ==

= 1.4.2 =
* Capped shortcode query output at 100 items for safer frontend performance.
* Removed the frontend jQuery dependency and rewrote the accordion script in vanilla JavaScript.
* Registered plugin data types before uninstall cleanup for more reliable post and term removal.
* Cleaned up readme release metadata and shortcode documentation.

= 1.4.1 =
* Security hardening for meta saving.
* Scoped frontend assets and accessible responsive accordion.
* Added activation/deactivation rewrite flush.
* Added paginated REST API output.
* Removed hardcoded test signup URL.
* Improved release metadata.

= 1.3 =
* Initial public plugin structure.

== Settings ==

Ga naar Cursussen > Instellingen om te bepalen of cursusdata bij uninstall verwijderd mag worden. Standaard blijven cursusberichten bewaard.

== Shortcode layout ==

Het attribuut `layout` ondersteunt `responsive`, `desktop` en `mobile`.

Voorbeelden:

`[toon_cursussen layout="desktop"]`
`[toon_cursussen layout="mobile"]`
