<?php
// I could also expand and create the table structure in schema.sql, but this was perfect practice like this.
/** Similar to APIcUrlCall within APIFunction, I thought about creating a dynamically functioning function here as well.
 * However, I didn't implement it, the main reason being that in this case, there is not such a need for varied calls.
 * The necessary functions are closely related for each call (INSERT INTO VALUE (one by one) - bindParams (one by one)).
 * It's not impossible that I will create it later on.
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
        logDB("User query error:", $th);
        $pdo = null;
        return null;
    }
}

function SQLGetUserById( $id ){
    try {
        //init
        $pdo = getConnection();

        //query
        $sql = "SELECT * FROM `users` WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        //param setup for query
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        //execute
        $stmt->execute();

        //result progress - only ONE data we need FETCH not fetchAll
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        //if the result not one
        if (!$user) {
            throw new Exception("User doesn't exits.");
        }        

        //result handler
        logDB($user);
        $pdo = null;
        return $user;
    } catch (\Throwable $th) {
        logDB("User error:", $th);
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
        logDB($th);
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
        logDB($id."- user deleted");
        $pdo = null;
        return $user;
    } catch (\Throwable $th) {
        logDB("User delete error:", $th);
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
        logDB("User uploaded:", $user);
        $pdo = null;
        return $user;
    } catch (\Throwable $th) {
        logDB("New User upload error:", $th);
        $pdo = null;
        return null;
    }
}

// users Prepare section
function checkSQL(){
    /**
     * Table exists? , Data was valid?
     * checkUsersTable(True) - checkUsersData(True) = Early exit.
     * checkUsersTable(True) - checkUsersData(false) = upload all adata
     * checkUsersTable(False) - checkUsersData(null) = in checkUsersTable() the empty table will created. 
     * Last both case needs 'uploadDataBatchExe'
     * Make sure about auto_increment 'addAutoIncr'
     * It wont duplicate the data, by "id", log out every uploaded data.
     */
    $pdo = getConnection();
    try {
        if (checkUsersTable($pdo)) {
            if (!checkUsersData($pdo)) {
                uploadDataBatchExe($pdo);
            }
         } else {
            uploadDataBatchExe($pdo);
            addAutoIncr($pdo);
         }
    } catch (Exception $e) {
        logDB("Hiba: ", $e->getMessage());
    }finally{
        $pdo = NULL;
    }
}

function addAutoIncr($pdo){
    /**
     * If the table has been created without auto_increment,
     * it is so that there won't be any problem with IDs during subsequent data uploads,
     * so afterwards, the table will be modified so that for further form uploads,
     * the ID will be continuous.
     */
    try {
     //ALTER TABLE `users` MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY;
     $stmt = $pdo->prepare("ALTER TABLE `users` MODIFY COLUMN id INT AUTO_INCREMENT");
     $stmt->execute();
    } catch (\Throwable $th) {
     logDB($th);
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
     * checks based on the key whether this record already exists in the given table.
     */
    $sql = 'INSERT INTO users(id,email,username,pwd,phone) 
        VALUES(:id,:email,:username,:pwd,:phone) 
        ON DUPLICATE KEY UPDATE id = :id';

    /** Transaction
     * Its usage, considering that multiple transactions are executed, it's worth observing if they run properly.
     * beginTransaction to start, commit to close, and rollback for error handling, to revert.
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
            logDB("Data uploaded:",$idLog);
        } 
    } catch (PDOException $e) {
        $pdo->rollBack();
        logDB("<br>Data upload error: ", $e->getMessage());
    }
}

function userCount(){
    //Original source of data API call and gives back the count of them
    $usersAPI = json_decode(file_get_contents("https://fakestoreapi.com/users"),true);
    return count($usersAPI);
}

function checkUsersData($pdo){
    /**
     * Get the number of data, what the users table have
     * Get the number of data of API source and compare and check if the SQL data is uptodate
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
            logDB("Data mismatch");
            return false;
        }
    } catch (PDOException $e) {
        logDB("User check error: ",$e->getMessage());
        exit;
    }
}

function checkUsersTable($pdo){
    /**
     * We need to make sure that the table exists.
     * If it exists, it returns with a PDOException, and we proceed based on the fixed error code.
     * If everything runs, then it will be created, but we know that our table will be empty.
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
            logDB("Table created");
            return false;
        } catch (PDOException $e) {
            $errorCode = $e->errorInfo[0];
            //if the table was already exist - CREATE TABLE errorCode: '42S01' 
            if ($errorCode === '42S01') {
                return true;
            } else {
                logDB("Table create error: ", $e->getMessage());
                exit;
            }
        }
    } catch (PDOException $e) {
        logDB("Table check error: ",$e->getMessage());
        exit;
    }
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