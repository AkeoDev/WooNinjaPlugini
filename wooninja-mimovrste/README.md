# WooNinja - Mimovrste XML

**Verzija:** 2.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

Enostaven izvoz produktov iz WooCommerce trgovine v format XML za povezavo s spletno trgovino [Mimovrste.com](https://www.mimovrste.com). Vticnik generira XML datoteko, ki omogoca prenos podatkov o produktih na Mimovrste.com.

## Funkcionalnosti

- Izvoz produktov v XML formatu, prilagojenem zahtevam Mimovrste.com
- Avtomatski ali rocni izvoz produktov
- Nastavitve dostave
- Upravljanje licence

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Nalozite vticnik v mapo `wp-content/plugins/wooninja-mimovrste` na vasem strezniku ali ga namestite neposredno prek WordPress vmesnika za vticnike.
2. V WordPress administraciji pojdite na **Vticniki** in aktivirajte vticnik **WooNinja - Mimovrste XML**.
3. Vnesite licencni kljuc (glejte nastavitve spodaj).

## Nastavitve

Nastavitve vticnika najdete v WordPress administraciji:

**WP Admin > WooNinja - Mimovrste XML** (glavni meni)

- **Nastavitve izvoza** -- moznost izbire med avtomatskim in rocnim izvozom, nastavitve dostave.
- **Licenca** (podmeni) -- vnos in aktivacija licencnega kljuca.

## Dnevnik sprememb

### 2.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

- **1.2** -- Popravek pri shranjevanju atributov.
- **1.1** -- Dodana opcija avtomatskega generiranja XML datoteke, popravki v kodi.
- **1.0** -- Prva verzija.

## Licenca

Ta vticnik je izdan pod licenco [GPLv2 ali novejso](https://www.gnu.org/licenses/gpl-2.0.html).

## Zavrnitev odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli garancij. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika. Uradna podpora, vzdrzevanje ali posodobitve niso zagotovljene. Uporabljate ga na lastno odgovornost.
