# pifast-preview

Förhandsvisning av Pifast AB:s nya webbplats.

Sidan är byggd som en enda PHP-mall (`index.php`) vars texter och två
bakgrundsbilder hämtas från en MySQL-databas. Ägaren kan logga in på
`/admin/login.php`, klicka på **Redigera sidan** och ändra texter/bilder
direkt på den publika sidan.

## Vad som är redigerbart

Alla synliga texter (rubriker, brödtext, knappetiketter, checklista,
kontaktuppgifter, footer) samt hero-bilden och "om oss"-bilden. Telefon,
e-post och bankgiro är gemensamma fält som återanvänds på flera ställen på
sidan (t.ex. i både topbaren och kontaktkortet) - att ändra ett av dem
uppdaterar alla förekomster efter en sidladdning.

**Inte** självbetjänat i v1 (kräver FTP + kodändring): antal tjänstekort/
checklistrader, ikoner, länkmål (`#tjanster`, `tel:`/`mailto:`-format, meny),
lösenordsbyte, flera adminkonton.

## Driftsättning på one.com

1. Skapa en MySQL-databas i one.coms kontrollpanel. Notera den **exakta**
   host-strängen den visar (t.ex. `mysqlXX.one.com`) - anta inte `localhost`.
2. Öppna phpMyAdmin för databasen → fliken SQL → klistra in innehållet i
   `schema.sql` och kör det en gång. Ladda aldrig upp `schema.sql` till
   servern.
3. Sätt PHP-version till en aktuell version (8.1 eller 8.2) i kontrollpanelen.
4. Generera ett lösenordshash lokalt:
   ```
   php -r "echo password_hash('ditt-losenord', PASSWORD_DEFAULT);"
   ```
5. Kopiera `config.sample.php` till `config.php` och fyll i databasuppgifterna
   från steg 1 samt hashen från steg 4.
6. Placera `config.php` en nivå **ovanför** `public_html` om one.coms
   filhanterare tillåter det (då kan filen aldrig nås via webbläsaren,
   oavsett `.htaccess`). Går det inte: lägg den i webroot bredvid
   `index.php` - `.htaccess`-skyddet fungerar då som fallback.
7. Ladda upp hela projektet (utom `schema.sql` och `config.sample.php`,
   de behövs inte på servern) via FTP/one.coms filhanterare, inklusive de
   dolda `.htaccess`-filerna i `/uploads` och `/storage`.
8. Vanliga FTP-rättigheter (755 för mappar, 644 för filer) räcker. Chmod inte
   777 - PHP kör som ert eget kontos användare och kan redan skriva i
   `/uploads` och `/storage`.
9. Aktivera one.coms gratis SSL och se till att http omdirigeras till https
   (krävs för säkra sessionscookies).
10. Gå till `/admin/login.php`, logga in, testa att ändra en text och byta en
    bild. Logga sedan ut och kontrollera att den publika sidan ser likadan ut
    och att ingen admin-UI syns för en utloggad besökare.

## Lokal utveckling

Kräver PHP 8.1+ med `pdo_mysql` och `gd`, samt en lokal MySQL-databas seedad
med `schema.sql`. Kör t.ex.:

```
php -S localhost:8000
```

och peka en lokal `config.php` mot din lokala databas.
