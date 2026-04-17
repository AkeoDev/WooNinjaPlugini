# WooNinja - Ceneje.si XML

**Verzija:** 2.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

Enostaven izvoz produktov iz WooCommerce trgovine v format XML za povezavo s primerjalnikom cen [Ceneje.si](https://www.ceneje.si). Vticnik samodejno generira XML datoteko, ki jo Ceneje.si uporabi za prikaz vasih produktov.

## Funkcionalnosti

- Izvoz produktov v XML formatu, prilagojenem zahtevam Ceneje.si
- Avtomatski ali rocni izvoz produktov
- Nastavitve dostave (minimalni/maksimalni dnevi dostave, cena dostave)
- Custom polje na posameznem produktu: oznaka (checkbox), ali naj se produkt izvozi na Ceneje.si
- Podpora za EAN kodo (EAN for WooCommerce)
- Upravljanje licence

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Nalozite vticnik v mapo `wp-content/plugins/wooninja-ceneje` na vasem strezniku ali ga namestite neposredno prek WordPress vmesnika za vticnike.
2. V WordPress administraciji pojdite na **Vticniki** in aktivirajte vticnik **WooNinja - Ceneje.si XML**.
3. Vnesite licencni kljuc (glejte nastavitve spodaj).

## Nastavitve

Nastavitve vticnika najdete v WordPress administraciji:

**WP Admin > WooNinja - Ceneje XML** (glavni meni)

- **Nastavitve izvoza** -- moznost izbire med avtomatskim in rocnim izvozom, nastavitve dostave.
- **Licenca** (podmeni) -- vnos in aktivacija licencnega kljuca.

Na posameznem produktu (stran za urejanje produkta) je na voljo dodatno polje (checkbox), s katerim dolocite, ali se produkt vkljuci v XML izvoz za Ceneje.si.

## Dnevnik sprememb

### 2.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

- **1.4** -- Dodan izpis za EAN kodo za plugin EAN for WooCommerce.
- **1.3** -- Dodana opcija za rocni vpis min/max dni dostave in ceno dostave.
- **1.2** -- Dodane kategorije, popravljen prikaz zaloge, popravek pri shranjevanju atributov.
- **1.1** -- Dodana opcija avtomatskega generiranja XML datoteke, popravki v kodi.
- **1.0** -- Prva verzija.

## Licenca

Ta vticnik je izdan pod licenco [GPLv2 ali novejso](https://www.gnu.org/licenses/gpl-2.0.html).

## Zavrnitev odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli garancij. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika. Uradna podpora, vzdrzevanje ali posodobitve niso zagotovljene. Uporabljate ga na lastno odgovornost.
