# 01: Szkielet aplikacji i pusta lista nastawów

**What to build:** Aplikacja Symfony uruchamiana jednym poleceniem Docker Compose. Po wejściu na stronę główną widać ekran startowy z komunikatem, że nie ma jeszcze nastawów, i linkiem do dodania nastawu (link może na razie prowadzić donikąd). Ten wycinek ustawia fundamenty pod wszystkie kolejne: stos, strefę czasową, wstrzykiwany zegar i infrastrukturę testów funkcjonalnych. Szczegóły w `.scratch/mvp/spec.md`.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [ ] Symfony (webapp) z Doctrine ORM, migracjami i bazą SQLite.
- [ ] Twig, Symfony UX Turbo i Stimulus, Pico.css, AssetMapper, bez Node'a.
- [ ] Docker Compose z jednym kontenerem (FrankenPHP); plik SQLite na wolumenie przetrwa przebudowę obrazu; `docker compose up` stawia działającą aplikację.
- [ ] `.gitattributes` wymusza końce linii LF.
- [ ] Domyślna strefa czasowa aplikacji to Europe/Warsaw.
- [ ] Bieżący czas pochodzi z wstrzykiwanego zegara Symfony (`ClockInterface`); w testach podmieniany na `MockClock`.
- [ ] Infrastruktura `WebTestCase`: osobna baza testowa SQLite, czysty stan dla każdego testu.
- [ ] Layout po polsku (`lang="pl"`), czytelny na telefonie.
- [ ] Test: przy pustej bazie ekran startowy pokazuje komunikat o braku nastawów i link do dodania nastawu.
- [ ] README z instrukcją uruchomienia aplikacji i testów.
