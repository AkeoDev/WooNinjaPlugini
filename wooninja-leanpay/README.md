# WooNinja - LeanPay

**Verzija:** 1.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

WordPress/WooCommerce vticnik za varno placevanje na obroke preko sistema LeanPay v spletni trgovini.

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+
- SSL certifikat (HTTPS)

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Prenesite vticnik in ga nalozite v mapo `wp-content/plugins/`.
2. V WordPress administraciji pojdite na **Vticniki** in aktivirajte vticnik **WooNinja - LeanPay**.
3. Nastavite placilni gateway v nastavitvah WooCommerce.

## Nastavitve

Nastavitve vticnika najdete na:

**WooCommerce > Nastavitve > Placila > LeanPay**

## Licenca

Ta vticnik je izdan pod licenco GPLv2 ali novejso.

## Izjava o odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli jamstev. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika.

## Spremembe

### 1.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

### 0.1.5
- Updated admin notices

### 0.1.4
- Validity check

### 0.1.3
- Response amount to decimal

### 0.1.2
- Min-max adjustment, administration status setting, md5 checksum

### 0.1.1
- Trigger completed status on "SUCCESS" status, new max value of 3000 for order.

### 0.1.0
- Updated licensing

### 0.0.4
- Fix compatibility with Nestpay module

### 0.0.3
- Fix tag version

### 0.0.2
- Cleaning first version

### 0.0.1
- First version
