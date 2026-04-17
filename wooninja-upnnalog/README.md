# WooNinja - UPN Nalog

**Verzija:** 4.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

Samodejno generiranje UPN placilnih nalogov za WooCommerce narocila. Vticnik podpira QR kodo in PDF generiranje za enostavno placevanje.

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Nalaganje vticnika izvedite preko WordPress administracije: **Vticniki > Dodaj novega > Nalozi vticnik**.
2. Aktivirajte vticnik v meniju **Vticniki**.
3. Po aktivaciji nastavite bancne racune in ostale podatke.

## Nastavitve

Nastavitve vticnika so dostopne v WordPress administraciji:

- **WP Admin > UPN Nalog** -- glavni meni vticnika
- Nastavitve bancnih racunov (custom post type za upravljanje vecih TRR)
- Izbira TRR na posameznem produktu
- Upravljanje licence

## Licenca

Ta vticnik je licenciran pod licenco GPLv2 ali novejso.
Podrobnosti: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

## Zavrnitev odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli garancij, izrecnih ali implicitnih. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika. Uradna podpora, vzdrzevanje ali posodobitve niso zagotovljene. Uporabljate ga na lastno odgovornost.

## Spremembe

### 4.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

### 1.4
- Changes to QR code generation

### 1.3.5
- uPay integration

### 1.3.4
- Create shortcode UPN link for Email

### 1.3.3
- PHP 8.0 compatibility

### 1.3.2
- Fixed Sklic

### 1.3.1
- Bug with post code

### 1.3.0
- Added option to delete PDF and QR codes on status change

### 1.2.3
- Removed # from Namen due to Abanka bug

### 1.2.1
- Font fix

### 1.2.0
- Added QR code

### 1.1.8
- Error with update

### 1.1.7
- Changed the name due to licencing engine issues with slovenian chars

### 1.1.6
- Updated admin notices

### 1.1.5
- Validity check

### 1.1.3
- Canceled indexing

### 1.1.2
- Modified download text in email template, download link only sent when order is on hold

### 1.1.1
- Updated output on UPN from račun to predračun

### 1.1.0
- Added download folder by default

### 1.0.9
- Updated licensing

### 1.0.8
- Updated icon for download and cursor pointer for download

### 1.0.7
- Removed cached path for plugin to work properly

### 1.0.6
- Hotfix

### 1.0.5
- Dodan pdf generation, pdf na voljo za prenos po nakupu na zahvalni strani ali v emailu.

### 1.0.4
- Zamenjava povezave iz optininja.com v wooninja.si

### 1.0.3
- Sprememba imena modula v UPN Nalog - položnica - Woocommerce.

### 1.0.2
- Obvestilo o statusu licence

### 1.0.1
- Odstranjene ikone iz modula

### 1.0
- Prva verzija
