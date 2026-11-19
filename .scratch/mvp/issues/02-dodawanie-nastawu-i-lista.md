# 02: Dodawanie nastawu i lista nastawów

**What to build:** Mogę dodać **nastaw** (nazwa, typ, opcjonalna data rozpoczęcia, opcjonalny opis) i zobaczyć go na ekranie startowym. Lista pokazuje nazwę, typ i datę rozpoczęcia, posortowana od najnowszych. Szczegóły w `.scratch/mvp/spec.md`.

**Blocked by:** 01 (Szkielet aplikacji i pusta lista nastawów)

**Status:** ready-for-agent

- [ ] Typ nastawu to wyliczenie w kodzie z wartościami wino i piwo oraz polskimi etykietami; dodanie nowego typu wymaga zmiany w jednym miejscu.
- [ ] Formularz nastawu: nazwa (wymagana, niepusta, może się powtarzać), typ (wybór z listy), data rozpoczęcia (sama data, opcjonalna, także wstecz), opis (opcjonalny, wieloliniowy).
- [ ] Błąd walidacji wyświetla się przy polu (odpowiedź 422).
- [ ] Nastaw zapisuje datę utworzenia z zegara.
- [ ] Ekran startowy pokazuje każdy nastaw z nazwą, etykietą typu i datą rozpoczęcia (`dd.mm.rrrr`, puste gdy brak).
- [ ] Sortowanie listy: malejąco po dacie rozpoczęcia, a dla nastawów bez daty rozpoczęcia po dacie utworzenia.
- [ ] Test scenariusza 1: zakładając pustą listę, gdy dodam „Wino z aronii”, typ wino, z dzisiejszą datą rozpoczęcia, wtedy nastaw pojawia się na liście.
- [ ] Testy: nastaw bez daty rozpoczęcia da się dodać; pusta nazwa jest odrzucana; dwa nastawy o tej samej nazwie mogą istnieć; kolejność listy z datami i bez.
