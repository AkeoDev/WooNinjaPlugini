# WooNinja - Jeftinije

**Verzija:** 2.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

Enostaven izvoz podatkov o produktih iz WooCommerce trgovine v formatu, prilagojenem za povezavo s hrvaskim primerjalnikom cen [Jeftinije.hr](https://www.jeftinije.hr). Vticnik generira izvozno datoteko, ki jo Jeftinije.hr uporabi za prikaz vasih produktov.

## Funkcionalnosti

- Izvoz produktov v formatu, prilagojenem zahtevam Jeftinije.hr
- Izbira kategorij produktov za izvoz
- Izbira vira opisa produkta (kratek ali dolg opis)
- Vklop/izklop izvoza atributov produktov
- Upravljanje licence

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Nalozite vticnik v mapo `wp-content/plugins/wooninja-jeftinije` na vasem strezniku ali ga namestite neposredno prek WordPress vmesnika za vticnike.
2. V WordPress administraciji pojdite na **Vticniki** in aktivirajte vticnik **WooNinja - Jeftinije**.
3. Vnesite licencni kljuc (glejte nastavitve spodaj).

## Nastavitve

Nastavitve vticnika najdete v WordPress administraciji:

**WP Admin > Jeftinije.hr** (glavni meni)

- **Nastavitve izvoza** -- izbira kategorij za izvoz, izbira vira opisa produkta (kratek/dolg opis), vklop/izklop atributov.
- **Licenca** (podmeni) -- vnos in aktivacija licencnega kljuca.

## Licenca

Ta vticnik je izdan pod licenco [GPLv2 ali novejso](https://www.gnu.org/licenses/gpl-2.0.html).

## Zavrnitev odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli garancij. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika. Uradna podpora, vzdrzevanje ali posodobitve niso zagotovljene. Uporabljate ga na lastno odgovornost.

## Spremembe

### 2.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

### 1.1
- Preverjanje licenc

### 1.0.9
- Polne cene produktov
- Izvoz kategorij preko ID

### 1.0.8
- Izvoz enostavnih produktov
- Upravljanje izvoza dodatnih fotografij produkta

### 1.0.7
- Popravek izvoza atributov
- Dodan "OR" operator pri izvozu kategorij

### 1.0.6
- Zamenjava povezave iz optininja.com v wooninja.si

### 1.0.5
- Popravek UTM source parametra

### 1.0.4
- Preverjanje pravic imenika exports

### 1.0.3
- Preverjanje licence

### 1.0.2
- Popravek izvoza posameznih kategorij v svojo datoteko

### 1.0.1
- Izboljšave izvoza in dodatne funkcije

### 1.0
- Osnovna verzija izvoza
