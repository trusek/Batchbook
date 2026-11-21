# Środowisko deweloperskie: pułapki

Polecenia uruchamiania i testów są w [`README.md`](../../README.md). Tu są tylko rzeczy, których nie widać w konfiguracji.

## Zanim zgłosisz pracę jako gotową

`composer check` musi przechodzić ([README: Sprawdzenia przed commitem](../../README.md#sprawdzenia-przed-commitem)). Hook pre-commit działa tylko w kopii, w której ustawiono `core.hooksPath`. Świeża kopia robocza (np. worktree agenta) go nie ma, więc zainstaluj hook albo uruchom `check` ręcznie.

## Docker na Windows

- **Uruchamiaj Dockera z PowerShella, nie z Git Basha.** `docker compose run` z bind mountem zawiesza się, gdy wywołuje go Git Bash. Z PowerShella działa. Wyjątek: hook pre-commit wywołuje `docker compose run -T` (bez TTY) z Git Basha i to działało przy wdrażaniu #10. Czy `-T` wystarcza zawsze, sprawdza #13.
- **Każda kopia robocza ma osobny projekt Compose.** Nazwa projektu to nazwa katalogu, więc kontenery i wolumeny kopii (np. worktree agenta) nie kolidują. Serwer dev drugiej kopii uruchom na wolnym porcie: `$env:HTTP_PORT=8081; docker compose up`.

## Pliki z PowerShella mają BOM

PowerShell 5.1 z `-Encoding utf8` (`Set-Content`, `Out-File`) zapisuje UTF-8 z BOM. PHP z BOM kończy się błędem „Namespace declaration statement has to be the very first statement”. `composer check` zgłasza BOM w `*.php`, `*.twig` i `*.yaml`. Pliki zapisuj narzędziem Write/Edit. Jeśli BOM już jest, usuń go w Git Bashu:

```sh
sed -i '1s/^\xEF\xBB\xBF//' plik.php
```

## Świeża kopia robocza

`vendor/` i `assets/vendor/` nie są w repo. Entrypoint instaluje zależności tylko przy starcie serwera (`docker compose up`), a nie przy `docker compose run … bin/phpunit`. W nowej kopii zacznij więc od `composer install` (polecenie w README).

- Testy pokazują `lang="en"` albo brakuje w nich przycisków: nie ma `assets/vendor/`. Uruchom `docker compose run --rm app php bin/console importmap:install`.
- `post-install-cmd` zgłasza „DirectoryLoader not found”: wyczyść `var/cache/*` i powtórz `composer install`. Skrypt przerywa się na `cache:clear`, więc `importmap:install` też się wtedy nie wykonał.

## Baza i migracje

- Baza testowa powstaje wyłącznie z migracji (`tests/bootstrap.php`), nie ze schematu encji. Każda zmiana encji wymaga migracji, inaczej testy działają na starym schemacie.
- Przed `make:migration` zmigruj bazę dev do najnowszej wersji (`doctrine:migrations:migrate`). Inaczej wygenerowana migracja powtórzy zmiany z poprzednich.

## Symfony i Twig

- **CSRF:** ręcznie wpisany `csrf_token('<id>')` jest bezstanowy tylko wtedy, gdy `<id>` jest na liście `framework.csrf_protection.stateless_token_ids` (`config/packages/csrf.yaml`). Nowy identyfikator dopisz do tej listy.
- **Data w Twigu:** litera `T` w formacie wymaga podwójnego backslasha: `date('Y-m-d\\TH:i')`. Z pojedynczym wychodzi `2026-11-24CET18:00`. `composer check` to zgłasza.

## CSS

Reguła `:focus-visible` stoi na końcu `assets/styles/app.css`. Przyciski z `all: unset` kasują obrys fokusu, więc reguła musi być po nich. Nowe style dopisuj przed nią.
