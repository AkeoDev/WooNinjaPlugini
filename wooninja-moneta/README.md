# WooNinja - VALU Moneta

**Verzija:** 2.0.0
**Avtor:** [Humanfrog d.o.o.](https://wooninja.si)

## Opis

WordPress/WooCommerce vticnik za sprejemanje placil preko VALU Moneta sistema. Vticnik uporablja React-based checkout widget za izvedbo placila.

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+
- SSL certifikat (HTTPS)

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Prenesite vticnik in ga nalozite v mapo `wp-content/plugins/`.
2. V WordPress administraciji pojdite na **Vticniki** in aktivirajte vticnik **WooNinja - VALU Moneta**.
3. Nastavite placilni gateway v nastavitvah WooCommerce.

## Nastavitve

Nastavitve vticnika najdete na:

**WooCommerce > Nastavitve > Placila > VALU Moneta**

## Licenca

Ta vticnik je izdan pod licenco GPLv2 ali novejso.

## Izjava o odgovornosti

Ta vticnik je na voljo "tak kot je", brez kakrsnihkoli jamstev. Humanfrog d.o.o. ne prevzema odgovornosti za morebitne napake, tezave ali skodo, ki bi nastala z uporabo tega vticnika.

## Spremembe

### 2.0.0
- Odprtokodna izdaja (open source)
- Varnostne izboljšave in čiščenje kode
- Posodobljeno na WordPress coding standards

### 1.2.5
- Preverjanje licenc

### 1.2.4
- Zamenjava imena na Valu

### 1.2.3
- Posodobitve sistema Moneta in Valu ter menjava načina obdelave davkov

### 1.2.2
- Posodobitev sistema zaokroževanja

### 1.2.1
- Posodobitev opisa varno plačilo

### 1.2.0
- Modul preimenovan v Valu in rešitev težave z licenco

### 1.1.7
- dodan nov testni IP naslov za Moneto

### 1.1.5
- popravek testnega URL naslova za Moneto

### 1.1.4
- Dopolnitev seznama Monetinih javnih IP-jev

### 1.1.3
- Odstranjena povezava na naročilu

### 1.1.2
- Preverjanje aktivnosti WooCommerce

### 1.1.1
- Odstranitev statusov

### 1.1.0
- Failed status

### 1.0.9
- Manjši popravki

### 1.0.8
- Popravek cene poštnine

### 1.0.7
- Zamenjava povezave iz optininja.com v wooninja.si

### 1.0.6
- Manjši popravki

### 1.0.5
- Popravek zaključevanja naročila - status

### 1.0.4
- Manjši popravki

### 1.0.3
- Polje za izbiro statusa ob uspešnem plačilu
- Obvestilo v primeru napačne licence

### 1.0.2
- Popravki davčnih stopenj

### 1.0.1
- Dodan nov tip transakcije - "Nakup"
- Manjši popravki

### 1.0
- Prva verzija
