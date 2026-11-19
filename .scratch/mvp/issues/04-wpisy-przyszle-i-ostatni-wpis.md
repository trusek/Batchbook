# 04: Wpisy przyszłe i data ostatniego wpisu na liście

**What to build:** **Wpis przyszły** (czas późniejszy niż teraz) jest w dzienniku wyraźnie wyróżniony, więc dziennik służy też za prosty plan. Na ekranie startowym przy każdym nastawie widać datę ostatniego wpisu, czyli ostatniej faktycznej aktywności, bez wpisów przyszłych. Szczegóły w `.scratch/mvp/spec.md`.

**Blocked by:** 03 (Strona nastawu i szybki wpis)

**Status:** ready-for-agent

- [x] Wpis z czasem późniejszym niż bieżący czas zegara ma w HTML znacznik wpisu przyszłego (klasa lub atrybut) i wyraźny styl.
- [x] Wpis na dziś na późniejszą godzinę jest przyszły, dopóki ta godzina nie minie; potem przestaje być wyróżniony bez żadnej akcji.
- [x] Ekran startowy pokazuje przy nastawie datę ostatniego wpisu (`dd.mm.rrrr HH:MM`): najpóźniejszy wpis nie późniejszy niż teraz.
- [x] Nastaw bez wpisów lub tylko z wpisami przyszłymi ma pustą datę ostatniego wpisu.
- [x] Test scenariusza 4: gdy dodam wpis z datą za 3 dni i notatką „sprawdzić Blg”, wtedy jest wyróżniony jako przyszły.
- [x] Testy: wpis dziś później niż teraz jest przyszły; po przesunięciu zegara za jego czas już nie jest; data ostatniego wpisu pomija wpisy przyszłe.
