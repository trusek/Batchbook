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

`APP_SECRET` w `.env.dev` jest jawną wartością tylko do developmentu. Własną ustaw w `.env.dev.local` (plik jest w `.gitignore`):

```sh
echo "APP_SECRET=$(openssl rand -hex 16)" > .env.dev.local
```

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
Wyjątek: `tests/Tools/` to testy jednostkowe narzędzi z `tools/` (reguł sprawdzanych przez `composer check`).

## Sprawdzenia przed commitem

Jedno polecenie uruchamia sprawdzenia plików (`bin/check-source-files`), lintery (Twig, YAML, kontener), PHPStan (`phpstan.dist.neon`) i PHPUnit:

```sh
docker compose run --rm -T app composer check
```

Reguły projektu sprawdzane automatycznie (każda ma test w `tests/Tools/`):

- „Teraz” wyłącznie z `ClockInterface`: PHPStan zgłasza funkcje i konstruktory, które same czytają bieżący czas (`tools/PHPStan/NoImplicitNowRule.php`).
- Testy tylko przez HTTP: PHPStan zgłasza w `App\Tests` odwołania do repozytoriów, `EntityManagerInterface` i `Connection` (`tools/PHPStan/HttpOnlyTestsRule.php`).
- Pliki: BOM na początku `*.php`, `*.twig`, `*.yaml` oraz w Twigu `date('Y-m-d\TH:i')` z pojedynczym backslashem, który Twig zjada; poprawnie `date('Y-m-d\\TH:i')` (`tools/SourceFiles/SourceFileChecker.php`).

Hook pre-commit z `.githooks/` uruchamia to samo polecenie i blokuje commit, gdy coś nie przechodzi.
Sprawdza całą kopię roboczą, nie tylko pliki dodane do commita.
Zainstaluj go raz w każdej kopii roboczej:

```sh
git config core.hooksPath .githooks
```

W wyjątkowej sytuacji hook można pominąć: `git commit --no-verify`.

## Uruchomienie docelowe (serwer)

```sh
APP_SECRET=<stały-losowy-ciąg> docker compose -f compose.yaml up -d --build
```

Obraz `prod` zawiera kod i zależności; przy starcie wykonuje migracje. Dane są na wolumenie `db_data`.
