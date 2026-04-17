# WooNinja - WooCommerce vtičniki

Zbirka WooCommerce vtičnikov za slovensko in regionalno tržišče. Razvija [Humanfrog d.o.o.](https://wooninja.si)

Vtičniki so as-is in ne nudimo podpore.

## Vtičniki

### Plačilni sistemi

| Vtičnik | Opis | Verzija |
|---------|------|---------|
| [wooninja-kreditnekartice](wooninja-kreditnekartice/) | Bankart plačilni gateway | 3.0.0 |
| [wooninja-kreditnekartice-diners](wooninja-kreditnekartice-diners/) | Diners plačilni gateway | 3.0.0 |
| [wooninja-nestpay](wooninja-nestpay/) | NestPay plačilni gateway | 2.0.0 |
| [wooninja-nestpay-diners](wooninja-nestpay-diners/) | NestPay Diners plačilni gateway | 2.0.0 |
| [wooninja-leanpay](wooninja-leanpay/) | LeanPay plačilo na obroke | 1.0.0 |
| [wooninja-moneta](wooninja-moneta/) | VALU Moneta plačilni sistem | 2.0.0 |

### Izvoz na cenovna primerjalnike

| Vtičnik | Opis | Verzija |
|---------|------|---------|
| [wooninja-ceneje](wooninja-ceneje/) | XML izvoz za Ceneje.si | 2.0.0 |
| [wooninja-mimovrste](wooninja-mimovrste/) | XML izvoz za Mimovrste.com | 2.0.0 |
| [wooninja-jeftinije](wooninja-jeftinije/) | CSV izvoz za Jeftinje.hr | 2.0.0 |
| [wooninja-idealnoba](wooninja-idealnoba/) | XML izvoz za Idealno.ba | 1.0.0 |
| [wooninja-idealnors](wooninja-idealnors/) | XML izvoz za Idealno.rs | 1.0.0 |

### Dostava in logistika

| Vtičnik | Opis | Verzija |
|---------|------|---------|
| [wooninja-dpdapi](wooninja-dpdapi/) | DPD WebLabel API integracija | 2.0.0 |
| [wooninja-espremnica](wooninja-espremnica/) | eSpremnica - Posta Slovenije | 2.0.0 |

### Racunovodstvo

| Vtičnik | Opis | Verzija |
|---------|------|---------|
| [wooninja-minimax](wooninja-minimax/) | Minimax integracija za racune | 3.0.0 |

### Pomozni vtičniki

| Vtičnik | Opis | Verzija |
|---------|------|---------|
| [wooninja-lowestprice](wooninja-lowestprice/) | Najnizja cena - PID/Omnibus direktiva | 2.0.0 |
| [wooninja-upnnalog](wooninja-upnnalog/) | UPN placilni nalogi s QR kodo | 4.0.0 |
| [wooninja-orderforms](wooninja-orderforms/) | Dinamicne narocilnice | 2.0.0 |
| [wooninja-smsapi](wooninja-smsapi/) | SMS obvestila preko SMSapi.si | 2.0.0 |

## Zahteve

- WordPress 5.6+
- WooCommerce 5.0+
- PHP 7.4+
- SSL certifikat za placilne sisteme

Testirano do WordPress 7.0 in WooCommerce 10.7.

## Namestitev

1. Prenesite mapo izbranega vticnika
2. Nalozite jo v `wp-content/plugins/`
3. Aktivirajte vticnik v WordPress admin panelu
4. Nastavite vticnik v WordPress admin panelu

## Licenca

Vsi vticniki so licencirani pod GPLv2 or later.

## Avtor

[Humanfrog d.o.o.](https://wooninja.si)
