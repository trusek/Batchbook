# 07: Układ „Notatnik” (pasek boczny + pulpit + dziennik jako arkusz)

**What to build:** Zastąpić obecny wygląd aplikacji układem wybranym w prototypie UI (wariant C „Notatnik”). Prototyp, czyli kod referencyjny bez testów, jest na gałęzi `prototype/ui-variants`, w plikach `templates/prototype/ui/C/*`, `assets/styles/prototype-ui.css` (sekcje „C”) i `src/Twig/PrototypeUiExtension.php`. Wbudować go porządnie, bez przełącznika wariantów i bez funkcji `proto_*`. Pozostałe wymagania jak w `.scratch/mvp/spec.md`.

**Blocked by:** 01–06 (resolved)

**Status:** ready-for-agent

## Decyzje użytkownika (z sesji prototypowania)

- Wybrany wariant C. Warianty A i B oraz przełącznik nie trafiają do `mvp`.
- **Ekran startowy = pulpit**: dwie kolumny „Najbliższe plany” (wpisy przyszłe ze wszystkich nastawów, rosnąco) i „Ostatnia aktywność” (wpisy nie późniejsze niż teraz, malejąco), każdy wpis z nazwą nastawu jako linkiem. To świadome rozszerzenie speca. Przy braku nastawów: komunikat i link do dodania nastawu (historyjka 19).
- **Lista nastawów żyje w pasku bocznym** na każdej stronie (pulpit, strona nastawu, nowy nastaw, edycja). Kolejność jak w specu (historyjka 18), bez usuniętych. Przy każdym nastawie: nazwa, typ słowem, data rozpoczęcia, data ostatniego wpisu, opcjonalnie najbliższy plan.
- **Daty w pasku bocznym są względne**: np. „dzień 25” (od daty rozpoczęcia, dzień startu = dzień 1), „start za 3 dni”, „bez daty startu”, „wczoraj”, „3 dni temu”, „brak wpisów”, „plan za 4 dni”. Każda w `<time datetime="…" title="dd.mm.rrrr[ HH:MM]">`, żeby pełna data była dostępna. Względność liczona dniami kalendarzowymi z zegara (`ClockInterface`). W tabeli dziennika i na pulpicie zostają pełne daty `dd.mm.rrrr HH:MM`.
- **Rzadkie akcje schowane**:
  - nastaw: w nagłówku tylko ikona ołówka „Edytuj nastaw”; „Usuń nastaw” wyłącznie na dole strony edycji nastawu;
  - wpis: w wierszu tylko ołówek obok daty; „Usuń wpis” wyłącznie w formularzu edycji wpisu w miejscu (obok Zapisz / Anuluj).
- **Bez emoji.** Typ jako kolorowa kropka (ozdobna, `aria-hidden`) plus słowo; wpis przyszły jako etykieta „plan”; ołówek jako inline SVG w kolorze tekstu.
- **Dziennik jako arkusz**: kolumny Kiedy | Notatka; pierwszy wiersz to formularz szybkiego wpisu (data, notatka, „Dodaj” obok notatki); treść ograniczona do ~60rem, żeby akcje były blisko tekstu.
- **Formularz nastawu zwarty**: nazwa i typ w jednym rzędzie, wąska data, opis 4 linie, przyciski obok siebie, w ramie z paskiem bocznym.
- **WCAG 2.2 AA** (zweryfikowane w prototypie, przenieść 1:1):
  - kontrasty: obramowania pól ≥ 3:1, kolory typów dla jasnego i ciemnego motywu (wino `#9b2c4a` / `#f19ab4`, piwo `#875a00` / `#e9b949`), czas i etykieta planu w `--pico-primary-hover`;
  - widoczny `:focus-visible` (także na przyciskach z `all: unset`);
  - link „Przejdź do treści” przed paskiem bocznym;
  - etykiety pól ukryte wizualnie (`form_label` z klasą sr-only) z `aria-invalid` i `aria-describedby` przy błędach (prototyp: `templates/prototype/ui/C/_field.html.twig`);
  - ołówki z nazwą dostępną, np. „Edytuj wpis z 24.11.2026 18:00”;
  - cele kliknięcia ≥ 24×24 px;
  - typ nie tylko kolorem;
  - bez `role="table"` udającego tabelę z formularzem w środku.
- Responsywność jak w prototypie: poniżej ~800px pasek boczny nad treścią (przewijana poziomo lista), wiersze dziennika w jednej kolumnie.

## Kryteria

- [x] Wszystkie strony mają układ C; pasek boczny na każdej stronie; brak `?variant`, przełącznika i kodu `proto_*`.
- [x] Pulpit: najbliższe plany i ostatnia aktywność ze wszystkich nieusuniętych nastawów (bez usuniętych wpisów i wpisów usuniętych nastawów); pusty stan z linkiem.
- [x] Pasek boczny: kolejność i filtrowanie jak w specu; typ słowem; daty względne w `<time>` z pełną datą w `datetime`/`title`; „data ostatniego wpisu” liczona jak w specu (bez przyszłych i usuniętych).
- [x] Bez zapytań N+1 dla paska bocznego (najbliższy plan i ostatni wpis zbiorczo).
- [x] Usuwanie nastawu tylko na stronie edycji; usuwanie wpisu tylko w edycji wpisu; nadal POST + CSRF + potwierdzenie, miękko; zachowanie 404/403 bez zmian.
- [x] Wymagania dostępności powyżej.
- [x] Testy (HTTP, `WebTestCase`) przepisane pod nowy układ, a wszystkie dotychczasowe zachowania nadal pokryte; nowe testy: pulpit (plany/aktywność, bez usuniętych), daty względne w pasku bocznym przy ustawionym zegarze, usuwanie dostępne tylko w edycji, pola formularzy mają etykiety, `aria-invalid` przy błędzie.
- [x] Spec zaktualizowany w sekcji „Ekrany i interakcje” (ekran startowy = pulpit, lista w pasku bocznym, daty względne w pasku).

## Comments
