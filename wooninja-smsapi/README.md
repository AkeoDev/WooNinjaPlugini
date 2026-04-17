# WooNinja - SMS API

**Verzija:** 2.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

Posiljanje SMS sporocil preko SMSapi.si. Vticnik podpira avtomatsko obvescanje kupcev o spremembi statusa narocila ter posiljanje posameznih SMS sporocil.

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Nalaganje vticnika izvedite preko WordPress administracije: **Vticniki > Dodaj novega > Nalozi vticnik**.
2. Aktivirajte vticnik v meniju **Vticniki**.
3. Po aktivaciji nastavite API podatke za SMSapi.si.

## Nastavitve

Nastavitve vticnika so dostopne v WordPress administraciji:

- **WP Admin > SMSApi** -- glavni meni vticnika
- API uporabnisko ime in geslo za SMSapi.si
- Telefonska stevilka posiljatelja
- ID posiljatelja
- Posiljanje posameznih SMS sporocil

## Licenca

Ta vticnik je licenciran pod licenco GPLv2 ali novejso.
Podrobnosti: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

## Zavrnitev odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli garancij, izrecnih ali implicitnih. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika. Uradna podpora, vzdrzevanje ali posodobitve niso zagotovljene. Uporabljate ga na lastno odgovornost.

## Spremembe

### 2.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

### 1.0.5
- Zamenjava povezave iz optininja.com v wooninja.si

### 1.0.4
- Preverjanje licence

### 1.0.3
- Prikaz napake pri pošiljanju sms sporočila

### 1.0.2
- Prikaz številke napake ob neuspešnem pošiljanju SMS-a

### 1.0.1
- Dodana opcija ID pošiljatelja

### 1.0
- Prva verzija - pošiljanje smsov, stanje kreditov, in wrapper za ostale vtičnike
