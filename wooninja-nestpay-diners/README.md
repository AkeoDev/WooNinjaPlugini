# WooNinja - NestPay Diners

**Verzija:** 2.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

WordPress/WooCommerce vticnik za sprejemanje placil z Diners kartico preko NestPay sistema.

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+
- SSL certifikat (HTTPS)

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Prenesite vticnik in ga nalozite v mapo `wp-content/plugins/`.
2. V WordPress administraciji pojdite na **Vticniki** in aktivirajte vticnik **WooNinja - NestPay Diners**.
3. Nastavite placilni gateway v nastavitvah WooCommerce.

## Nastavitve

Nastavitve vticnika najdete na:

**WooCommerce > Nastavitve > Placila > NestPay Diners**

## Licenca

Ta vticnik je izdan pod licenco GPLv2 ali novejso.

## Izjava o odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli jamstev. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika.

## Spremembe

### 2.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

### 1.3.3
- Fix order notification

### 1.3.1
- Change diners logo

### 1.3.1
- Validity check
- Odstranitev ikone Visa verified secure, če je NestPay vtičnik aktiviran

### 1.3
- Fork za dodatna DINERS plačil

### 1.2.5
- Fixed bug in Installments

### 1.2.4
- Added HRK exception

### 1.2.3
- Updated admin notices

### 1.2.2
- Validity check

### 1.2.0
- Odstranitev American Express logotipa

### 1.1.9
- Posodobitev licenciranja

### 1.1.8
- Dodana podpora za prevode / različne jezike.

### 1.1.7
- Popravek verzija za update - WooNinja.si platforma.

### 1.1.6
- V primeru uspele transakcije se je poslal tudi email o neuspeli transakciji, kar zmede administratorja.

### 1.1.5
- Preverjanje aktivnosti WooCommerce

### 1.1.4
- Popravki

### 1.1.3
- Popravki

### 1.1.2
- Odstranitev statusov

### 1.1.1
- Preusmeritev v primeru spodletelega nakupa

### 1.1.0
- Manjši popravki

### 1.0.9
- Manjši popravki

### 1.0.8
- Pripenjanje podatkov transakcije administratorju

### 1.0.7
- Zamenjava povezave iz optininja.com v wooninja.si

### 1.0.6
- Manjši popravki

### 1.0.5
- Validacija prijave z API podatki

### 1.0.4
- Popravek zaključka naročila

### 1.0.3
- Možnost potrjevanja transakcije iz administracije
- Možnost vračila transakcija iz administracije
- Izboljšanje pregleda statusa transakcije
- Pošiljanje reporta pri neuspelih transakcijah

### 1.0.2
- Manjši popravki

### 1.0.1
- Možnost določanja vidnosti modula na IP naslov
- Manjši popravki

### 1.0
- Prva verzija
