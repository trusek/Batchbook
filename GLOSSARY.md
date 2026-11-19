# Dziennik nastawów

Domowy dziennik produkcji wina i piwa: co, kiedy i z czym zostało nastawione oraz co się z tym później działo.

## Język

**Nastaw** (kod: `Batch`):
Jedna partia napoju w produkcji, od nastawienia do końca. Ma nazwę, typ, opcjonalną datę rozpoczęcia i opis.
_Unikaj_: partia, warka, batch (w UI)

**Typ nastawu** (kod: `BatchType`):
Rodzaj napoju w nastawie: wino albo piwo. Miód pitny, cydr i podobne zapisujemy jako wino.
_Unikaj_: kategoria, rodzaj

**Wpis** (kod: `Entry`):
Notatka z datą i godziną należąca do nastawu. Wpisy z przeszłości tworzą dziennik, a wpis z datą późniejszą niż teraz jest **wpisem przyszłym** i służy za prosty plan.
_Unikaj_: krok (w MVP), notatka, log

**Dziennik**:
Wszystkie wpisy nastawu ułożone chronologicznie.
_Unikaj_: historia, timeline

**Krok** (kod: `Step`):
Zarezerwowane na etap planowania. Oznacza zaplanowaną czynność w nastawie ze statusem (zaplanowany, wykonany, pominięty). Wykonanie kroku tworzy wpis. W MVP nie występuje.
_Unikaj_: zadanie, todo
