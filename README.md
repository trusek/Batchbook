# Dziennik nastawów

Domowy dziennik produkcji wina i piwa. Symfony 7.4 + Doctrine (SQLite), Twig, Symfony UX Turbo/Stimulus, Pico.css przez AssetMapper (bez Node'a), jeden kontener FrankenPHP.

Słownictwo domeny: [`GLOSSARY.md`](GLOSSARY.md).

## Wymagania

Tylko Docker z Docker Compose. PHP i Composer działają w kontenerze.

## Uruchomienie (development)

```sh
docker compose up --build
```

Aplikacja jest pod <http://localhost:8080> (inny port: `HTTP_PORT=8081 docker compose up`).
Przy pierwszym starcie kontener instaluje zależności (`vendor/`) i wykonuje migracje.

`compose.override.yaml` jest wczytywany automatycznie: montuje kod z hosta i uruchamia aplikację w środowisku `dev`.
Baza SQLite leży w `var/data/` na wolumenie Dockera `db_data`, więc przetrwa przebudowę obrazu.

Polecenia w kontenerze:

```sh
docker compose run --rm app composer install
docker compose run --rm app php bin/console doctrine:migrations:migrate
docker compose run --rm app php bin/console make:migration
```

## Testy

```sh
docker compose run --rm app bin/phpunit
```

Testy są funkcjonalne (`WebTestCase`, przez HTTP) i dziedziczą po `tests/AppWebTestCase.php`:
zegar jest zamrożony (`MockClock`), baza testowa (`var/data/test.db`) powstaje z migracji na początku przebiegu,
a każdy test startuje na pustej bazie (rollback transakcji przez DAMA DoctrineTestBundle).

## Uruchomienie docelowe (serwer)

```sh
APP_SECRET=<stały-losowy-ciąg> docker compose -f compose.yaml up -d --build
```

Obraz `prod` zawiera kod i zależności; przy starcie wykonuje migracje. Dane są na wolumenie `db_data`.
