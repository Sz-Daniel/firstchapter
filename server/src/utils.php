<?php

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
