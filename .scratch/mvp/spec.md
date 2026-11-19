# MVP: dziennik nastawów

Status: resolved

## Problem Statement

Robię w domu wino i piwo (miody pitne traktuję jak wino). Przebieg produkcji (co i kiedy dodałem, kiedy zlałem, co trzeba sprawdzić za kilka dni) ląduje dziś w różnych miejscach albo nigdzie. Brakuje mi jednego miejsca w domowej sieci, w którym szybko, także z telefonu, dopiszę, co się wydarzyło przy danym nastawie, zobaczę jego historię we właściwej kolejności i zostawię sobie notatkę na przyszłość.

## Solution

Prosta aplikacja webowa działająca w sieci domowej (bez logowania, dostęp tylko z LAN), która jest dziennikiem nastawów:

- Ekran startowy pokazuje listę **nastawów** z nazwą, typem, datą rozpoczęcia i datą ostatniego wpisu.
- Strona nastawu pokazuje jego **dziennik**, czyli **wpisy** (data z godziną i notatka), zawsze w porządku chronologicznym, nawet gdy wpis dodano wstecz.
- **Wpis przyszły** (późniejszy niż teraz) jest wyróżniony wizualnie, więc ta sama lista służy za dziennik i prosty plan.
- Na górze strony nastawu jest formularz szybkiego wpisu: data i godzina ustawione domyślnie na „teraz”, kursor od razu w polu notatki.
- Nastawy i wpisy można dodawać, edytować i usuwać. Usunięcie wymaga potwierdzenia i jest miękkie, więc dane zostają w bazie, ale znikają z aplikacji.

## User Stories

### Nastawy

1. Jako domowy winiarz chcę dodać nastaw z nazwą i typem, żeby zacząć prowadzić jego dziennik.
2. Jako domowy winiarz chcę wybrać typ nastawu z listy (wino, piwo), żeby od razu było widać, czym jest dany nastaw.
3. Jako domowy winiarz chcę zapisać miód pitny jako typ „wino”, żeby nie czekać na osobny typ.
4. Jako domowy winiarz chcę podać opcjonalną datę rozpoczęcia nastawu (sam dzień), żeby wiedzieć, kiedy wystartował.
5. Jako domowy winiarz chcę dodać nastaw bez daty rozpoczęcia, żeby móc zapisać coś, czego daty startu nie znam lub jeszcze nie ustaliłem.
6. Jako domowy winiarz chcę dodać nastaw z datą rozpoczęcia w przeszłości, żeby wpisać nastaw zrobiony wcześniej bez aplikacji.
7. Jako domowy winiarz chcę dodać opcjonalny opis nastawu (wieloliniowy tekst), żeby zapisać przepis, składniki czy pomysł.
8. Jako domowy winiarz chcę, żeby opis zachowywał podziały linii, żeby dało się go czytać.
9. Jako domowy winiarz chcę, żeby nazwa nastawu była wymagana, żeby nie powstawały nastawy bez nazwy.
10. Jako domowy winiarz chcę móc nadać dwóm nastawom tę samą nazwę, żeby co roku robić „Wino z aronii” bez wymyślania nowych nazw.
11. Jako domowy winiarz chcę edytować nazwę, typ, datę rozpoczęcia i opis nastawu, żeby poprawić pomyłkę.
12. Jako domowy winiarz chcę usunąć nastaw po potwierdzeniu, żeby pozbyć się pomyłkowego lub testowego nastawu, ale nie zrobić tego przypadkiem.
13. Jako domowy winiarz chcę, żeby usunięty nastaw razem z wpisami zniknął z aplikacji, ale fizycznie został w bazie, żebym w razie pomyłki mógł go ręcznie odzyskać z bazy.

### Lista nastawów (ekran startowy)

14. Jako domowy winiarz chcę po wejściu do aplikacji od razu widzieć listę nastawów, żeby szybko przejść do właściwego.
15. Jako domowy winiarz chcę widzieć przy każdym nastawie nazwę, typ, datę rozpoczęcia i datę ostatniego wpisu, żeby na pierwszy rzut oka wiedzieć, co się dzieje.
16. Jako domowy winiarz chcę, żeby „data ostatniego wpisu” pokazywała ostatni wpis do teraz (bez wpisów przyszłych), żeby oznaczała ostatnią faktyczną aktywność.
17. Jako domowy winiarz chcę, żeby nastaw bez wpisów lub tylko z wpisami przyszłymi miał pustą datę ostatniego wpisu, żeby nie było mylących wartości.
18. Jako domowy winiarz chcę, żeby lista była posortowana od najnowszych według daty rozpoczęcia, a nastaw bez daty rozpoczęcia był sortowany według daty utworzenia, żeby świeże nastawy były na górze.
19. Jako domowy winiarz chcę przy pustej liście zobaczyć zachętę do dodania pierwszego nastawu, żeby wiedzieć, od czego zacząć.
20. Jako domowy winiarz chcę, żeby usunięte nastawy nie pojawiały się na liście.

### Wpisy

21. Jako domowy winiarz chcę dodać do nastawu wpis z datą, godziną i notatką, żeby zapisać, co zrobiłem.
22. Jako domowy winiarz chcę, żeby formularz nowego wpisu miał domyślnie bieżącą datę i godzinę, żeby nie wpisywać ich ręcznie.
23. Jako domowy winiarz chcę, żeby po otwarciu nastawu kursor był od razu w polu notatki, żeby dodać wpis w kilka sekund.
24. Jako domowy winiarz chcę, żeby formularz szybkiego wpisu był na górze strony nastawu, żeby nie musieć przewijać.
25. Jako domowy winiarz chcę, żeby notatka była wymagana, żeby nie powstawały puste wpisy.
26. Jako domowy winiarz chcę, żeby notatka zachowywała podziały linii, żeby dłuższe wpisy były czytelne.
27. Jako domowy winiarz chcę dodać wpis z datą wsteczną, żeby uzupełnić coś, co zrobiłem wcześniej.
28. Jako domowy winiarz chcę dodawać wpisy do nastawu bez daty rozpoczęcia, żeby brak daty niczego nie blokował.
29. Jako domowy winiarz chcę dodać wpis z datą w przyszłości (np. „sprawdzić Blg” za 3 dni), żeby zostawić sobie plan.
30. Jako domowy winiarz chcę edytować datę, godzinę i notatkę wpisu w miejscu, bez przechodzenia na inną stronę, żeby szybko poprawić pomyłkę.
31. Jako domowy winiarz chcę, żeby zmiana samej daty wpisu nie zmieniała jego godziny, żeby nie musieć jej poprawiać.
32. Jako domowy winiarz chcę usunąć wpis po potwierdzeniu, żeby nie skasować go przypadkiem.
33. Jako domowy winiarz chcę, żeby usunięty wpis zniknął z dziennika i nie liczył się do daty ostatniego wpisu na liście nastawów.

### Kolejność i wpisy przyszłe

34. Jako domowy winiarz chcę widzieć wpisy posortowane malejąco po dacie i godzinie (najpóźniejsze na górze), żeby przyszłe plany i świeże wydarzenia były pod ręką.
35. Jako domowy winiarz chcę, żeby wpis dodany wstecz trafił od razu we właściwe miejsce chronologiczne, żeby dziennik zawsze był uporządkowany.
36. Jako domowy winiarz chcę, żeby wpis po edycji daty przesunął się we właściwe miejsce.
37. Jako domowy winiarz chcę, żeby wpisy z identyczną datą i godziną układały się według kolejności dodania, żeby kolejność była przewidywalna.
38. Jako domowy winiarz chcę, żeby wpis z datą i godziną późniejszą niż teraz był wyróżniony wizualnie jako przyszły, żeby odróżnić plan od historii.
39. Jako domowy winiarz chcę, żeby wpis na dziś na późniejszą godzinę też był wyróżniony jako przyszły, dopóki ta godzina nie minie.
40. Jako domowy winiarz chcę, żeby wpis przestał być wyróżniony, gdy jego czas minie, bez żadnej akcji z mojej strony.
41. Jako domowy winiarz chcę widzieć przy każdym wpisie datę i godzinę w formacie `dd.mm.rrrr HH:MM`, żeby kolejność była jasna.

### Ogólne

42. Jako domowy winiarz chcę korzystać z aplikacji w przeglądarce na komputerze i telefonie w domowej sieci, bez logowania, żeby nic mnie nie spowalniało.
43. Jako domowy winiarz chcę mieć interfejs po polsku, żeby korzystać z niego naturalnie.
44. Jako domowy winiarz chcę, żeby czas był zawsze czasem lokalnym (Europe/Warsaw), żeby wpis o 22:00 został wpisem o 22:00 także po zmianie czasu.
45. Jako domowy winiarz chcę uruchomić aplikację jednym poleceniem Docker Compose, żeby później tak samo postawić ją na domowym serwerze.
46. Jako opiekun aplikacji chcę, żeby nowy typ nastawu dodawało się w jednym miejscu w kodzie, żeby łatwo rozszerzyć listę.

## Implementation Decisions

**Stos**
- Symfony (szablon webapp), Doctrine ORM z migracjami, baza SQLite.
- Widoki w Twigu, Symfony UX: Turbo i Stimulus. Style z Pico.css, zasoby przez AssetMapper, bez Node'a i bez kroku budowania JS.
- Docker Compose z jednym kontenerem aplikacji (FrankenPHP). Plik SQLite leży na wolumenie, żeby przetrwał przebudowę obrazu. Ta sama konfiguracja służy do developmentu na Windowsie i później do wdrożenia na serwerze. Konfiguracja serwera jest poza zakresem.
- UI po polsku, wpisany wprost w szablony, bez komponentu tłumaczeń. Identyfikatory w kodzie po angielsku, zgodnie z mapowaniem w `GLOSSARY.md` (Nastaw → `Batch`, Typ nastawu → `BatchType`, Wpis → `Entry`).

**Model**
- `Batch` ma pola: nazwa (wymagana, niepusta, nieunikalna), typ (`BatchType`), data rozpoczęcia (tylko data, opcjonalna), opis (opcjonalny tekst), data utworzenia oraz znacznik miękkiego usunięcia.
- `BatchType` to wyliczenie PHP w kodzie z wartościami wino i piwo oraz polską etykietą każdej wartości. Dodanie typu sprowadza się do dopisania przypadku. W bazie zapisywana jest wartość wyliczenia.
- `Entry` ma pola: nastaw, czas wpisu (data z godziną, wymagany), notatka (wymagana, niepusta), data utworzenia oraz znacznik miękkiego usunięcia. Czas wpisu ma dokładność do minuty.
- Czas zapisywany jest jako lokalny czas ścienny w strefie Europe/Warsaw, bez przeliczania na UTC. Strefa jest ustawiona w konfiguracji aplikacji.
- „Wpis przyszły” to cecha wyliczana: czas wpisu jest późniejszy niż bieżący czas z zegara. Nie ma takiej kolumny w bazie.
- „Data ostatniego wpisu” na liście nastawów to maksymalny czas wpisu spośród nieusuniętych wpisów nastawu, które nie są późniejsze niż teraz. Jest wyliczana w zapytaniu, bez kolumny w bazie.

**Czas**
- Wszystko, co zależy od „teraz”, czyli wyróżnienie wpisów przyszłych, domyślna wartość w formularzu wpisu i data ostatniego wpisu, pobiera czas z wstrzykiwanego zegara Symfony (`ClockInterface`). Kod nigdy nie tworzy bieżącego czasu bezpośrednio.

**Miękkie usuwanie**
- Usunięcie nastawu lub wpisu ustawia znacznik usunięcia. Rekordy zostają w bazie.
- Wpisy usuniętego nastawu nie dostają własnego znacznika. Są niewidoczne, bo niewidoczny jest ich nastaw.
- Wszystkie widoki i zapytania pomijają usunięte rekordy. Wejście na adres usuniętego nastawu lub wpisu zwraca 404.
- W UI nie ma kosza, przywracania ani trwałego usuwania. Przywrócenie robi się ręcznie w bazie.
- Usunięcie wymaga potwierdzenia w przeglądarce (kontroler Stimulus albo natywne `confirm`) i idzie przez POST z tokenem CSRF.

**Ekrany i interakcje**
- Ekran startowy pokazuje listę nastawów. Każda pozycja ma nazwę, etykietę typu, datę rozpoczęcia i datę ostatniego wpisu. Sortowanie malejące po dacie rozpoczęcia, a gdy jej brak, po dacie utworzenia. Przy pustej liście wyświetla się komunikat i link do dodania nastawu.
- Nastaw dodaje się i edytuje na osobnej stronie z formularzem.
- Strona nastawu pokazuje kolejno:
  - nagłówek z danymi nastawu i akcjami edycji i usunięcia,
  - formularz szybkiego wpisu z czasem domyślnie ustawionym na „teraz” (z zegara, zaokrąglonym do minuty) i autofocusem w polu notatki,
  - dziennik.
- Dziennik jest posortowany malejąco po czasie wpisu. Przy równym czasie wpisy układa kolejność dodania, z późniejszym dodaniem wyżej. Wpisy przyszłe mają wyróżniający znacznik w HTML-u (klasę lub atrybut) oraz wyraźny styl.
- Wpis edytuje się w miejscu przez Turbo Frame. Kliknięcie „Edytuj” zamienia wpis na formularz, a zapis odświeża dziennik, żeby wpis trafił na właściwą pozycję.
- Błędy walidacji formularzy wyświetlają się przy polach. Turbo wymaga odpowiedzi 422.
- Daty i godziny w widokach mają format `dd.mm.rrrr HH:MM`, a data rozpoczęcia nastawu format `dd.mm.rrrr`.

## Testing Decisions

- Dobry test sprawdza zachowanie widoczne z zewnątrz: wysyła żądanie HTTP przez formularz lub link i sprawdza wyrenderowany HTML. Nie zagląda do encji, repozytoriów ani struktury bazy. Zmiana implementacji, która nie zmienia zachowania, nie może psuć testów.
- **Testy przechodzą tylko przez HTTP.** Funkcjonalne testy Symfony (`WebTestCase`) przechodzą przez cały kernel. Nie ma testów jednostkowych encji ani serwisów.
- **Jedyną podmienianą zależnością jest zegar.** W testach zegar to `MockClock` ustawiony na stały moment, dzięki czemu „za 3 dni”, „sprzed tygodnia” i „przyszły” dają deterministyczny wynik.
- Każdy test startuje na czystej bazie SQLite (osobna baza testowa, schemat z migracji lub encji, izolacja między testami).
- Testy piszemy przed implementacją, a scenariusze akceptacyjne są ich bezpośrednim źródłem:
  1. **Nowy nastaw**: zakładając pustą listę, gdy dodam „Wino z aronii”, typ wino, z dzisiejszą datą rozpoczęcia, wtedy nastaw pojawia się na liście.
  2. **Wpis**: zakładając otwarty nastaw, gdy dodam wpis z bieżącym czasem i notatką „dodano 3 g pożywki”, wtedy wpis jest widoczny w dzienniku.
  3. **Wpis wstecz**: gdy dodam wpis z datą sprzed tygodnia, wtedy trafia we właściwe miejsce chronologicznie.
  4. **Wpis przyszły**: gdy dodam wpis z datą za 3 dni i notatką „sprawdzić Blg”, wtedy jest wyróżniony jako przyszły.
  5. **Poprawka**: gdy edytuję lub usunę wpis, wtedy dziennik się aktualizuje, a usunięty wpis znika.
  6. **Nastaw bez daty**: gdy dodam nastaw bez daty rozpoczęcia, wtedy mogę mimo to dodawać wpisy.
- Dodatkowe testy wynikające z historyjek: sortowanie listy nastawów (z datą rozpoczęcia i bez), „data ostatniego wpisu” pomijająca wpisy przyszłe i usunięte, miękkie usunięcie nastawu (znika z listy, jego strona zwraca 404), walidacja wymaganej nazwy i notatki, kolejność wpisów z identycznym czasem, wpis dziś na późniejszą godzinę jako przyszły, domyślny czas w formularzu równy czasowi zegara.
- Ręcznie, poza testami, weryfikujemy: autofocus, wygląd wyróżnienia, edycję w miejscu przez Turbo w prawdziwej przeglądarce i działanie na telefonie.
- Prior art: brak. To pierwszy kod w repozytorium i te testy wyznaczą wzorzec.

## Out of Scope

- Logowanie i dostęp spoza LAN (Cloudflare Access, tunel).
- Kroki zaplanowane, statusy kroków, zdarzenia („nastawione”, „fermentacja ruszyła”), terminy względne, widok „Do zrobienia”.
- Statusy nastawu (planowany, aktywny, zakończony) i archiwum. Lista rośnie bez ograniczeń.
- Przypomnienia na Discordzie.
- Szablony kroków.
- Pomiary (Blg/SG, temperatura, pH), wykresy, wyliczanie alkoholu.
- „Powtórz nastaw”, kolejne typy, eksport do JSON lub CSV.
- Kosz, przywracanie i trwałe usuwanie w UI.
- Kopie zapasowe bazy. Planowane po MVP, wtedy z ADR.
- Konfiguracja domowego serwera (system, Docker, sieć).
- Specjalna obsługa telefonów, np. obejście braku klawiatury przy autofocusie na iOS.

## Further Notes

- Słownictwo według `GLOSSARY.md`. W MVP nie ma bytu **Krok**. Termin jest zarezerwowany na etap planowania, w którym wykonanie kroku utworzy wpis. Wpisy przyszłe z MVP mogą wtedy zostać zmigrowane do kroków. Ta decyzja zapadnie w osobnej sesji.
- Brak backupów przy miękkim usuwaniu oznacza, że awaria dysku serwera kończy się utratą danych. To świadomie zaakceptowane ryzyko MVP.
- Docelowy serwer to Lenovo M625 (AMD E2-9000E, 4 GB RAM, SSD 128 GB). Stos powinien być lekki: jeden kontener, bez dodatkowych usług.
