# 03: Strona nastawu i szybki wpis

**What to build:** Klikam nastaw na liście i widzę jego stronę: dane nastawu, na górze formularz szybkiego **wpisu** z czasem domyślnie ustawionym na „teraz” i kursorem w polu notatki, a pod nim **dziennik**, czyli wpisy posortowane od najpóźniejszego. Wpis dodany wstecz od razu ląduje we właściwym miejscu. Szczegóły w `.scratch/mvp/spec.md`.

**Blocked by:** 02 (Dodawanie nastawu i lista nastawów)

**Status:** ready-for-agent

- [x] Pozycja na liście nastawów prowadzi do strony nastawu.
- [x] Strona nastawu pokazuje nazwę, typ, datę rozpoczęcia i opis (z zachowanymi podziałami linii).
- [x] Formularz wpisu: data z godziną (wymagana, dokładność do minuty) i notatka (wymagana, niepusta, wieloliniowa).
- [x] Domyślny czas w formularzu to bieżący czas z zegara zaokrąglony do minuty; pole notatki ma autofocus.
- [x] Wpis zapisuje datę utworzenia; czas wpisu to lokalny czas ścienny Europe/Warsaw.
- [x] Dziennik posortowany malejąco po czasie wpisu; przy równym czasie wyżej jest wpis dodany później.
- [x] Każdy wpis pokazuje czas w formacie `dd.mm.rrrr HH:MM` i notatkę z zachowanymi podziałami linii.
- [x] Test scenariusza 2: zakładając otwarty nastaw, gdy dodam wpis z bieżącym czasem i notatką „dodano 3 g pożywki”, wtedy wpis jest widoczny w dzienniku.
- [x] Test scenariusza 3: gdy dodam wpis z datą sprzed tygodnia, wtedy trafia we właściwe miejsce chronologicznie.
- [x] Test scenariusza 6: gdy dodam nastaw bez daty rozpoczęcia, wtedy mogę mimo to dodawać wpisy.
- [x] Testy: domyślny czas w formularzu równy czasowi zegara; pusta notatka odrzucana; kolejność wpisów o identycznym czasie.
