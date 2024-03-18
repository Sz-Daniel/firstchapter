# firstchapter 
Currently, my main focus is on the PHP language and the application of learned functionalities - MySQL, CRUD, Login, Routes. Once I exhaust my knowledge in this language, I plan to continue with JavaScript, refactor functions, and, if possible, manually format using Bootstrap at the very end.

### Long-term plan:
Once I completely finish this 'chapter,' I will create an entirely different project with an active API source.
The setup and configuration of docker-phpmyadmin-mysql are not mine. I utilize it from a completed course to ensure smooth development.
In the future, there are plans to complete additional courses: advanced PHP - Docker - Node.js - JS framework - PHP framework learning and working with them.

# Code Documentation - Junior Journal - JJ

As a junior, during development, I first try to rely on my own knowledge, building it piece by piece, and then, when looking at the big picture, I use ChatGPT3.5 to find directions on how to make it more efficient, solve it differently, look up documentation for new features, and explore other online resources. Similarly, I follow this process when dealing with potential issues. Elsődlegesen a tényleges megoldásom van kifejtve, **JJ** részekben pedig a mélyebb utánanézésekori magyarázatok, alternatív megoldások. 

Sok esetben szándékosan nem egységesek a megoldások, technológiai és függvények használata. Szerettem volna hogyha látszódik hogyan fejlődtek a megoldásaim, technológiák használata. pld homeHandler-ben egyszerű file_get_contents függvénnyel kérem le az adatokat, APIFunctionben pedig egy dinamikus, bármilyen helyzetben használható API hívásra használható cURL folyamat van megalkotva, amit a legtöbb esetben alternatívaként commentben oda is van illesztve.

[index.php](index.php "Goto index")
[auth.php](auth.php "Goto auth")
[SQLFunctions.php](SQLFunctions.php "Goto SQLFunctions")
[APIFunctions.php](APIFunctions.php "Goto APIFunctions")

## index.php
### Routes Handler (Step-by-Step)
Retrieve the method used on the previous page = Request method received by the index page -> $_SERVER['REQUEST_METHOD']
Which page the user wants to jump to = Requested page from the request -> $_SERVER['REQUEST_URI']
Create the page map as routes (with a fallback for page not found) -> ?? "notFoundHandler"
PHP is able to call a function where the function name is a string, and this is the basis of the procedure -> $handlerFunction()
  
### How Handler works
We will need a compiler that is built with the actual page from the template with a prebuilt page -> compileTemplate
Compiler collect params like form data, sql data and other state data and give to the prebuilded page 
and it give back the whole page as string

### logJS
#### Első Verzió
JS esetében kényelmes megoldás volt a fejlesztés közben a console.log használata, állapotok kiírása, eredmények és paraméterek kiiratása, ezért PHP függvényen belüli script meghívásakor, bármilyen és bármennyi paraméter meghívható, amik elemenként fog végrehajtódni és a PHP var_dump kiiratása illetve esetleges hibakód kezelése is consolba kerül kiiratásra. 
---
function logJS(...$dataArray){
    //Params: if I want to Give a string before, or multiply data to log in a same time, then I have to use '...' 
    foreach ($dataArray as  $data) {
        if ($data instanceof Exception) {
            //In Exception case, we get the Error message
            echo '<script>console.log('.json_encode($data->getMessage()).');</script>';
        }else {
            //or just we want to check some data, sometimes easier to read then var_dump
            echo '<script>console.log('.json_encode($data).');</script>';
        }
    }
}
---
**Második Verzió**
Header location problematika miatt hosszútávon nem érdemes használni az első verziót, ugyanis itt is munkafelület kiiratása történik így a header: location nem tud érvényesülni azután.
SQL táblát hoztam létre ennek kezelésére:
id - bizonyos szinten szükségtelen, csak formalitás
response - console logba mit iratnék ki
date - időpontra -> mp szinten meghatározva az eseményt a könnyebb nyomonkövetés miatt 

Tekintve hogy eredetileg Javascript függvény használata miatt lett JS elnevezés, egyenlőre még nem lesz átnevezve, majd a teljes refactoráláskor.

## auth.php
### Login system 
(loginProcessHandler) Called when it get POST data from /login page. To make sure, let's check if the data is appropriate. Using early return to make the process more faster. Do we get a data? Do we get data from API? The username is valid? Password is valid? If all yes, then make a session and create a session-cookie with the userId. We can verify at any time whether the user is still logged in (isLoggedIn) and authorized(isAuth) to view the page. When the user want to logout(logoutHandler) the process get the actual cookies params and with that, set the expires time to 0 so it will delete that data.  

### loginProcessHandler
At first, I utilized the following code snippet for the inclusion of user data in the database. However, upon subsequent review, I opted for a more efficient, simpler, and more functional approach
$user_index = null;
foreach ($users as $index => $user) {
    if ($user['username'] === $username) {  
        $user_index = $index; 
        break;
    }    
}
Better version:
//list, as array, only usernames
$usernameList = array_column($users, 'username');
//search on array, one specific data
$userIndex = array_search($username, $usernameList );

## SQLFunctions.php
Készítettem egy alternatív folyamatot, API helyett MySQL-t használtam a felhasználói adatok feldolgozásához.

### checkSQL
Kiegészítő eljárásra van szükség, ellenőrizni, hogy adatbázis tábla és adatok készen álljanak.
Le kell ellenőrizni hogy a tábla létezik, ha nem akkor hozzuk létre. Több módon próbáltam meg.

#### Table
CREATE TABLE eljárással és hibakezeléssel kiszűrhető hogy létezett e előtte a tábla (error code: 42S01)
Amennyiben a tábla már létezett előtte le kell ellenőrizni hogy a tábla adatmennyisége egyezik-e az API forrás adataival. Amennyiben a feltételeknek megfelel, kiléptetjük az ellenőrzésből, early returnnal.
Abban az esetben ha a táblát ekkor hozzuk létre, akkor a táblát fel kell tölteni, illetve ha nem azonos a tábla és frissíteni kell, vagy hiányos, szintén fel kell tölteni. Itt még id is feltöltésre kerül majd sikeres feltöltés után 'addAutoIncr' -el AUTO incrementbe rakjuk az id-t így id megfelelő módon fog folytatódni és nem ütközik a régi adatokkal.

**JJ**
"IF NOT EXISTS" kifejezést használva próbáltam eljárni, viszont lefutásakor nincs adat hogy létezett-e előtte a tábla, szándékomban áll átlátni a folyamatokat. Keresés után rátaláltam "SELECT 1 FROM information_schema.tables WHERE table_schema = database() AND table_name = ?" eljárásra ami megfelelő egy előzetes ellenőrzésre tökéletesnek bizonyult. Viszont amint létrehoztam a CREATE TABLE eljárást, egyértelmű lett hogy a tábla nem fog létrejönni ha már létezett, ilyen módon. Így a hibakezelés által, lekérdezem a megfelelő hibakódot, akkor egyértelmű lesz hogy volt-e előtte vagy sem.

#### Data
SQL data upload, uploadDataBatchExe. Ezesetben az adott insert lekérdezést egyessével paramétereztetjük és hajtjuk végre, és kezeljük le az eredményt. Hibakezelésen túl szükséges transition (begin transition - commit) keretbe foglalása ami, hibás lefutás esetén visszaállítja(rollback) az esemény előtti állapotra, elkerülve a félbehagyott módosítási problémákat.

**JJ**
Több módot találtam, hasonló funkció kiépítésére, azért válaszottam ezt a módszert mert egyszerűbb, magabiztosabban használom és ezesetben, 10 record, hatékonysági szempontok nem teljesen szükségesek. Nagyobb adat esetében már megfontolandóbb lenne a többi.
Bulk insert - ahol több egy INSERT nél több VALUES van hozzá csatolva. 
"multi_query" - mysqli typusnál használható, jelenleg PDO-t használok. Ott előre megadható több SQL összefűzötten majd futtatáskor eredmény kezelésenkét lépked tovább a rendszer.

## APIFunction.php
cURL - eddig file_get_content alap függvényt használtam API kezelésre, megpróbáltam a cURL -t ami többször is felmerült tanulásom során, viszont szerettem volna egyidőben egy nyelvet/libaryt elsajátítatani fókuszáltan.
Kezdetben próbáltam egy egyszerű adathalmaz kiiratást pld a users-t. Amint a kiiratás sikerült, módosítani akartam, bővítgetni tesztelni. Kezdetben belefutottam abba a hibába hogy CURLOPT_RETURNTRANSFER nem került beállításra és okozott némi galibát a kimeneten. Elsődlegesen célirányosan funkciónként hoztam létre a függvényeket, majd API methodusonként, aztán mikor a végére értem és átláttam a működést, egyszerűen összetudtam rakni 1 függvényt amivel bármilyen API -t letudok kezelni. Nem írtam át a kódban mindenhol, viszont API kérésekhez alternatívaként kommentbe odaraktam.
