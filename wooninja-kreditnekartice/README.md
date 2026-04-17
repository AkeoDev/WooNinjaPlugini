# WooNinja - Bankart

**Verzija:** 3.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

WordPress/WooCommerce vticnik za sprejemanje placil preko Bankart payment gatewaya. Podpira preauth in capture nacina placila.

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+
- SSL certifikat (HTTPS)

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Prenesite vticnik in ga nalozite v mapo `wp-content/plugins/`.
2. V WordPress administraciji pojdite na **Vticniki** in aktivirajte vticnik **WooNinja - Bankart**.
3. Nastavite placilni gateway v nastavitvah WooCommerce.

## Nastavitve

Nastavitve vticnika najdete na:

**WooCommerce > Nastavitve > Placila > Bankart**

## Licenca

Ta vticnik je izdan pod licenco GPLv2 ali novejso.

## Izjava o odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli jamstev. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika.

## Spremembe

### 3.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

### 2.0.9
- Change in claim

### 2.0.8
- Installments format

### 2.0.7
- Removed IP based whitelist for callback

### 2.0.6
- Bug with capture

### 2.0.5
- Fixed bankart language
- Added back link for failed transactions
- Fixed url on custom checkout url

### 2.0.4
- Minor bug fix. OK status

### 2.0.3
- Minor bug fix. language and order ID

### 2.0.2
- Minor bug fix. Instaments and preauthorize

### 2.0.1
- Minor bug fix

### 2.0.0
- Redesigned modul and prepared for new Gateway
- Changing the policy of versions. 2.x is minor change, x.0 is large change.
- Currently plugin is as previous. Additional functions will follow in short future.

### 1.3.3
- Added option to select number of instalments (6, 12, 24)

### 1.3.2
- Updated admin notices

### 1.3.1
- Validity check

### 1.3.0
- Updated access

### 1.2.9
- Modified payment gateway description print on checkout

### 1.2.8
- Versioning correction

### 1.2.7
- Filter upload permissions, auto generate db entry and resource file

### 1.2.6
- Implemented function exists for upload filter and MIME type CGN

### 1.2.5
- Added resource upload option, regenerate after update, updated licensing

### 1.2.4
- Removal of automatic Capture and reviewed code, modified class instances

### 1.2.2
- Updated code to get order ID from value stored in var trackid when user clicks Cancel payment on Bankart hosted site.

### 1.1.9
- Avtomatsko zajemanje plačil (CAPTURE) če je nastavljena vrsta transakcije na Authorization

### 1.1.8
- Popravek napake ob vrnitvi statusa plačila.

### 1.1.7
- Popravek verzija za update - WooNinja.si platforma.

### 1.1.6
- Odstranjen avtomatski capture v Authorization načinu delovanja.

### 1.1.5
- Dodan manjkajoč error v primeru praznega PayId parametra

### 1.1.4
- Preverjanje ali je omogočena funkcija zip_open na gostovanju

### 1.1.3
- Preverjanje aktivnosti WooCommerce

### 1.1.2
- Popravki

### 1.1.1
- Odstranitev statusov

### 1.1.0
- Dodatno preverjanje v primeru spodletelega nakupa

### 1.0.9
- Preusmeritev v primeru spodletelega nakupa

### 1.0.8
- Manjši popravki

### 1.0.7
- Popravek definiranja objektov v PHP

### 1.0.6
- Manjši popravki

### 1.0.5
- Zamenjava povezave iz optininja.com v wooninja.si

### 1.0.4
- Manjši popravki

### 1.0.3
- Manjši popravki

### 1.0.2
- Dodana nastavitev za poročanje o neuspelih transakcijah
- Preverjanje licence

### 1.0.1
- Validacija različnih situacij

### 1.0
- Prva verzija
