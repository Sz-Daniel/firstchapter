<?php
function globalVarVerif($var){
    // In the future, data verification will be simpler with the use of this.
    return (isset($var) && !empty($var));
}

function getConnection(){
    try {
        //Set the pdo
        $pdo = new PDO ( 
            'mysql:host='.$_SERVER['DB_HOST'].';dbname='.$_SERVER['DB_NAME'],
            $_SERVER['DB_USER'],
            $_SERVER['DB_PASSWORD']
        );
        //Set Error handling
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        logDB("PDO Connection error:", $e);
        return null;
    }
}

function logDB(...$dataArray)
{
    /**
     * Used for logging error return value data.
     * Each data provided in the parameters will be uploaded as a record in the 'log' database,
     * accompanied by an ID-date for identification.
     * It handles exceptions separately, but they are still uploaded.
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
