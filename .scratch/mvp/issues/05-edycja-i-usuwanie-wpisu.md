# 05: Edycja i usuwanie wpisu

**What to build:** Mogę poprawić czas i notatkę wpisu w miejscu, bez przechodzenia na inną stronę. Po zapisie wpis przesuwa się na właściwe miejsce w dzienniku. Mogę też usunąć wpis po potwierdzeniu. Usunięcie jest miękkie, czyli wpis znika z aplikacji, ale zostaje w bazie. Szczegóły w `.scratch/mvp/spec.md`.

**Blocked by:** 03 (Strona nastawu i szybki wpis), 04 (Wpisy przyszłe i data ostatniego wpisu na liście)

**Status:** resolved

- [x] „Edytuj” przy wpisie zamienia go na formularz w miejscu (Turbo Frame); zapis odświeża dziennik tak, by wpis trafił na właściwą pozycję.
- [x] Zmiana samej daty w formularzu edycji nie zmienia godziny.
- [x] Walidacja edycji jak przy dodawaniu (wymagany czas i niepusta notatka, odpowiedź 422).
- [x] Usunięcie wpisu wymaga potwierdzenia w przeglądarce i idzie przez POST z tokenem CSRF.
- [x] Usunięcie jest miękkie: rekord zostaje w bazie ze znacznikiem usunięcia; w UI nie ma przywracania.
- [x] Usunięty wpis nie pojawia się w dzienniku i nie liczy się do daty ostatniego wpisu na liście nastawów.
- [x] Edycja lub usunięcie usuniętego wpisu zwraca 404.
- [x] Test scenariusza 5: gdy edytuję lub usunę wpis, wtedy dziennik się aktualizuje, a usunięty wpis znika.
- [x] Testy: edycja daty przesuwa wpis w dzienniku; usunięty wpis nie wpływa na datę ostatniego wpisu; usunięcie bez poprawnego tokenu CSRF jest odrzucane.

## Comments

Zrealizowane na gałęzi integracyjnej `mvp` (wraz z poprawkami z code review, commit 672a295).
