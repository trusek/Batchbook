# 06: Edycja i usuwanie nastawu

**What to build:** Ze strony nastawu mogę przejść do edycji jego nazwy, typu, daty rozpoczęcia i opisu. Mogę też usunąć nastaw po potwierdzeniu. Usunięcie jest miękkie: nastaw razem z wpisami znika z aplikacji, ale zostaje w bazie. Szczegóły w `.scratch/mvp/spec.md`.

**Blocked by:** 03 (Strona nastawu i szybki wpis)

**Status:** ready-for-agent

- [x] Nagłówek strony nastawu ma akcje „Edytuj” i „Usuń”.
- [x] Edycja nastawu na osobnej stronie, z tymi samymi polami i walidacją co przy dodawaniu; po zapisie powrót na stronę nastawu.
- [x] Usunięcie nastawu wymaga potwierdzenia w przeglądarce i idzie przez POST z tokenem CSRF; po usunięciu powrót na ekran startowy.
- [x] Usunięcie jest miękkie: znacznik usunięcia na nastawie, wpisy nie dostają własnego znacznika; w UI nie ma przywracania.
- [x] Usunięty nastaw nie pojawia się na liście; jego strona, edycja oraz operacje na jego wpisach zwracają 404.
- [x] Testy: edycja nastawu zmienia dane na liście i stronie; usunięty nastaw znika z listy; strona usuniętego nastawu zwraca 404; usunięcie bez poprawnego tokenu CSRF jest odrzucane.
