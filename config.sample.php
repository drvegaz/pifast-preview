<?php
declare(strict_types=1);

// Kopiera den här filen till config.php och fyll i era riktiga uppgifter.
// Placera helst config.php en nivå OVANFÖR public_html på one.com (om
// filhanteraren tillåter det) - då är den läsbar för PHP men aldrig nåbar
// via webbläsaren. Annars: lägg den i webroot bredvid index.php, då gäller
// .htaccess-skyddet som fallback.

// Databasuppgifter från one.coms kontrollpanel. Kopiera host-strängen exakt
// som den visas där - anta INTE att den heter "localhost".
define('DB_HOST', 'mysqlXX.one.com');
// define('DB_PORT', 3306); // avkommentera bara om one.com anger en annan port än standard
define('DB_NAME', 'databasnamn');
define('DB_USER', 'databasanvandare');
define('DB_PASS', 'databaslosenord');

// Generera en lösenordshash lokalt med:
//   php -r "echo password_hash('ditt-losenord', PASSWORD_DEFAULT);"
// och klistra in resultatet nedan. Radera aldrig denna rad utan att ersätta
// den med en riktig hash.
define('ADMIN_PASSWORD_HASH', '$2y$10$replaceThisWithARealBcryptHash');
