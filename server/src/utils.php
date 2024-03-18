<?php
function logDB(...$dataArray)
{
    /**
     * Error-visszatérési érték-adatok logolására használatos.
     * Minden paraméterben megadott adat egy-egy recordként feltöltésre kerül a 'log' adatbázisba
     * id-dátum szerint mellékelve az azaonosításhoz
     * Külön kezeli az Exceptionöket de ugyan úgy feltöltésre kerülnek.
    */
    //Params: if I want to Give a string before, or multiply data to log in a same time, then I have to use '...' 
    $pdo  = getConnection();
    $stmt = $pdo->prepare("INSERT INTO log(response) VALUES(:response)");
    foreach ($dataArray as  $data) {
        if ($data instanceof Exception) {
            $response = json_encode($data->getMessage());
            $stmt->bindParam(':response',$response);
        }else {
            $response = json_encode($data);
            $stmt->bindParam(':response',$response);
        }
    }
    $stmt->execute();
    $pdo = null;
}
