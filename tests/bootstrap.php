<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Świeża baza testowa z migracji na każdy przebieg PHPUnit.
// Izolację między testami zapewnia DAMA DoctrineTestBundle (rollback transakcji).
$console = escapeshellarg(dirname(__DIR__).'/bin/console');
foreach ([
    'doctrine:schema:drop --full-database --force',
    'doctrine:migrations:migrate --no-interaction --allow-no-migration',
] as $command) {
    passthru(sprintf('php %s %s --env=test --quiet', $console, $command), $exitCode);
    if (0 !== $exitCode) {
        throw new RuntimeException(sprintf('Nie udało się przygotować bazy testowej: "%s".', $command));
    }
}
