# 01: Szkielet aplikacji i pusta lista nastawów

**What to build:** Aplikacja Symfony uruchamiana jednym poleceniem Docker Compose. Po wejściu na stronę główną widać ekran startowy z komunikatem, że nie ma jeszcze nastawów, i linkiem do dodania nastawu (link może na razie prowadzić donikąd). Ten wycinek ustawia fundamenty pod wszystkie kolejne: stos, strefę czasową, wstrzykiwany zegar i infrastrukturę testów funkcjonalnych. Szczegóły w `.scratch/mvp/spec.md`.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [x] Symfony (webapp) z Doctrine ORM, migracjami i bazą SQLite.
- [x] Twig, Symfony UX Turbo i Stimulus, Pico.css, AssetMapper, bez Node'a.
- [x] Docker Compose z jednym kontenerem (FrankenPHP); plik SQLite na wolumenie przetrwa przebudowę obrazu; `docker compose up` stawia działającą aplikację.
- [x] `.gitattributes` wymusza końce linii LF.
- [x] Domyślna strefa czasowa aplikacji to Europe/Warsaw.
- [x] Bieżący czas pochodzi z wstrzykiwanego zegara Symfony (`ClockInterface`); w testach podmieniany na `MockClock`.
- [x] Infrastruktura `WebTestCase`: osobna baza testowa SQLite, czysty stan dla każdego testu.
- [x] Layout po polsku (`lang="pl"`), czytelny na telefonie.
- [x] Test: przy pustej bazie ekran startowy pokazuje komunikat o braku nastawów i link do dodania nastawu.
- [x] README z instrukcją uruchomienia aplikacji i testów.
