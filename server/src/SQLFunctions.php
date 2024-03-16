<?php
/** APIFunctionön belüli APIcUrlCall hoz hasonlóan gondolkodtam hogy itt is létrehozzak egy dinamikusan működő függvényt, 
 * viszont nem készítem el, fő indok hogy ezesetben nincs szükség annyira változatos hívásokra
 * hívásonként többszörösen összefüggők a szükséges függvények (INSERT INTO VALUE(egyessével) - bindParams(egyessével) )
 * Nem lehetetlen hogy a későbbiekben létre fogom hozni 
 */

function SQLGetUsers(){
    try {
        $pdo = getConnection();

        $sql = "SELECT * FROM `users`";
        $stmt = $pdo->prepare($sql);

        $stmt->execute();
        $userList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $userList;

    } catch (\Throwable $th) {
        logJS("User query error:", $th);
        $pdo = null;
        return null;
    }
}

function SQLGetUserById( $id ){
    try {
        $pdo = getConnection();

        $sql = "SELECT * FROM `users` WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            throw new Exception("User doesn't exits.");
        }        

        logJS($user);
        $pdo = null;
        return $user;
    } catch (\Throwable $th) {
        logJS("User error:", $th);
        $pdo = null;
        return null;    
    } 
}

function SQLEditUserById( $data = [] ){
    try {
        $pdo = getConnection();

        $sql = "UPDATE `users` 
        SET `email`=:email, `username`=:username, `pwd`=:pwd, `phone`=:phone 
        WHERE `id` = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id',$data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':email',$data['email'],PDO::PARAM_STR);
        $stmt->bindParam(':username',$data['username'],PDO::PARAM_STR);
        $stmt->bindParam(':pwd',$data['pwd'],PDO::PARAM_STR);
        $stmt->bindParam(':phone',$data['phone'],PDO::PARAM_STR);

        //Only one usable result we could have, how many rows were affected (1)
        $stmt->execute();
        $user = $stmt->rowCount();
        if (!$user) {
            throw new Exception("User doesn't exits.");
        }        
 
        $pdo = null;
        return $user;
    } catch (\Throwable $th) {
        logJS($th);
        $pdo = null;
        return null;
    }
}

function SQLDeleteUserById( $id ){

    try {
        $pdo = getConnection();

        $sql = "DELETE FROM `users` 
        WHERE `id` = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id',$id,PDO::PARAM_INT);

        //Only one usable result we could have, how many rows were affected (1)
        $stmt->execute();
        $user = $stmt->rowCount();
        if (!$user) {
            throw new Exception("User doesn't exits.");
        }        
        logJS($id."- user deleted");
        $pdo = null;
        return $user;
    } catch (\Throwable $th) {
        logJS("User delete error:", $th);
        $pdo = null;
        return null;
    }
}

function SQLCreateUser( $user=[] ){
    try {
        $pdo = getConnection();

        $sql = "INSERT INTO users(email,username,pwd,phone) 
        VALUES(:email,:username,:pwd,:phone)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':email',$user['email'],PDO::PARAM_STR);
        $stmt->bindParam(':username',$user['username'],PDO::PARAM_STR);
        $stmt->bindParam(':pwd',$user['pwd'],PDO::PARAM_STR);
        $stmt->bindParam(':phone',$user['phone'],PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->rowCount();
        if (!$user) {
            throw new Exception("User upload fail");
        }        
        logJS("User uploaded:", $user);
        $pdo = null;
        return $user;
    } catch (\Throwable $th) {
        logJS("New User upload error:", $th);
        $pdo = null;
        return null;
    }
}

// users Prepare section
function checkSQL(){
    /**
     * Table exists? , Data was valid?
     * True - True = Early exit.
     * True - False = uploadBulkData
     * False - null = in checkUsersTable() the empty table will created. 
     * Last both case needs 'uploadBulkData'
     * It wont duplicate the data, by "id", log out every uploaded data.
     */
    $pdo = getConnection();
    try {
        if (checkUsersTable($pdo)) {
            if (checkUsersData($pdo)) {
            logJS("Table, data check was successfully");
            }
         }
         uploadDataBatchExe($pdo);
         addAutoIncr($pdo);
    } catch (Exception $e) {
        logJS("Hiba: ", $e->getMessage());
    }finally{
        $pdo = NULL;
    }
}

function addAutoIncr($pdo){
    try {
     //ALTER TABLE `users` MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY;
     $stmt = $pdo->prepare("ALTER TABLE `users` MODIFY COLUMN id INT AUTO_INCREMENT");
     $stmt->execute();
    } catch (\Throwable $th) {
     logJS($th);
    }
}

function uploadDataBatchExe($pdo){
    //Get the API source data
    $usersAPI = json_decode(file_get_contents("https://fakestoreapi.com/users"),true);

    //log data about how many user was uploaded
    $idLog = [
        'count' => 0,
        'user' => []
    ];

    /** ON DUPLICATE
     * kulcs alapján ellenőrzi, ez a record létezik-e már az adott táblában.
     */
    $sql = 'INSERT INTO users(id,email,username,pwd,phone) 
        VALUES(:id,:email,:username,:pwd,:phone) 
        ON DUPLICATE KEY UPDATE id = :id';

    /** Transaction
     * Használata, tekintve hogy több tranzakció is lefut, érdemes megfigyelni hogy jól futnak-e le.
     * beginTransaction megynitni, commital zárni és hibakezelésként rollbackelni, visszavonni. 
     */
    try {
        $pdo->beginTransaction();
        $statement = $pdo->prepare($sql);

        foreach ($usersAPI as $user) {
            $statement->execute([
                ':id' => $user['id'],
                ':email' => $user['email'],
                ':username' => $user['username'],
                ':pwd' => $user['password'],
                ':phone' => $user['phone'],
            ]);
            /** ON DUPLICATE
             * rowCount() return value affected row, due ON DUPLICATE KEY UPDATE id = :id';
             * updated 1 - (bool)true, not updated 0 = (bool) false 
             * in case of true, we store that user data
             */
            if ((bool)$statement->rowCount()) {
                array_push($idLog['user'],$user);
                $idLog['count']++;
            }
        }
        $pdo->commit();
        // if any data was updated, then log out
        if($idLog['count'] > 0){
            logJS("Data uploaded:",$idLog);
        } 
    } catch (PDOException $e) {
        $pdo->rollBack();
        logJS("<br>Data upload error: ", $e->getMessage());
    }
}

function userCount(){
    //Original source of data API call and gives back the count of them
    $usersAPI = json_decode(file_get_contents("https://fakestoreapi.com/users"),true);
    return count($usersAPI);
}

function checkUsersData($pdo){
    /**
     * -Get the number of data, what the users table have
     * -Get the number of data of API source and compare and check if the SQL data is uptodate
     */
    try {
        //COUNT the number of result
        $sql = "SELECT COUNT(*) FROM `users`";
        
        $res = $pdo->query($sql);
        $columns = $res->fetchColumn();

        //original data source, number of data
        $srcCount = userCount();  

        if ($srcCount === $columns) {
            return true;
        } else {
            logJS("Data mismatch");
            return false;
        }
    } catch (PDOException $e) {
        logJS("User check error: ",$e->getMessage());
        exit;
    }
}

function checkUsersTable($pdo){

    /**
     * Biztosra kell mennünk hogy a tábla létezik
     * Amennyiben létezik PDOExceptionnal visszatér és fix hibakód alapján azonosítva továbblépünk
     * Ha minden lefut, akkor létrejön de tudjuk hogy üres a táblánk.
     */
    try {
        $sql = "CREATE TABLE `users` (
            `id` int NOT NULL ,
            `email` varchar(255) NOT NULL,
            `username` varchar(255) NOT NULL,
            `pwd` varchar(255) NOT NULL,
            `phone` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        )";

        $stmt =  $pdo->prepare($sql);
        try {
            //if the table created then it's empty, later we need to 'uploadBulkData' 
            $stmt->execute();
            logJS("Table created");
            return false;
        } catch (PDOException $e) {
            $errorCode = $e->errorInfo[0];
            //if the table was already exist - CREATE TABLE errorCode: '42S01' 
            if ($errorCode === '42S01') {
                return true;
            } else {
                logJS("Table create error: ", $e->getMessage());
                exit;
            }
        }
    } catch (PDOException $e) {
        logJS("Table check error: ",$e->getMessage());
        exit;
    }
}

function getConnection(){
    try {
        $pdo = new PDO ( 
            'mysql:host='.$_SERVER['DB_HOST'].';dbname='.$_SERVER['DB_NAME'],
            $_SERVER['DB_USER'],
            $_SERVER['DB_PASSWORD']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        logJS("PDO Connection error:", $e);
        return null;
    }
}