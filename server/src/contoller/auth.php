<?php
function loginHandler(){
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $loginData = array(    
                'username' => htmlspecialchars($_POST['username']) ?? "",
                'password' => $_POST['password'] ?? ""
            );
            loginProcessHandler($loginData);
            break;
        case 'GET':
            echo render("wrapper.phtml",[
                'content' => render('/views/auth/login.phtml'),
            ]);
            break;
    }    
}

function loginProcessHandler($param)
{
    try {
        /**
     * Needs to check in the whole db first
     * then step-by-step verif the data
     * build up the session for userdata as session-cookie
     * which is the key to keep signed in the user
     */

    // POST data check
    $username = $param['username'];
    $password = $param['password'];
    if (empty($username)||empty($password)) {
        throw new Exception('Input empty');
    }

    //Get all user's data
    $url ="https://fakestoreapi.com/users";
    $users = json_decode(file_get_contents($url),true);
    //$users = APIcUrlCall($url);
    if ($users === null) {
        throw new Exception('Users API problem');
    }
    /**
     * Search in db by username
     * If the username exists, store the index for deepcheck
     * new verion, prev in README
     */
    //list, as array, only usernames
    $usernameList = array_column($users, 'username');
    //search on array, one specific data
    $userIndex = array_search($username, $usernameList );

    /**Early Return
     * If the stored user_index not exists go back to Login page with error
     * If the username not exists go back to Login page with error
     */
    if(!$userIndex){
        throw new Exception('Users doesnt exist');
    }
    //in hesh case I would use password_verify(), without knowing what kind of hesh they used for password store
    //User database stores password as string, not using hash, I cannot change that
    if(!$users[$userIndex]['password']===$password){
        throw new Exception('Password not match'); 
    }

    /**
     * create a session then 
     * create a session-cookie for user data 
     * then goes back 
     */
    if (!isset($_SESSION)) {
        session_start();
    }

    //with active session create a 'userId' cookie
    $_SESSION['userId'] = $users[$userIndex]['id'];

    header('Location: /');
    exit;
    } catch (\Throwable $th) {
        logJS("login Process error: ".$th);
        header('Location: /login?info=invalidCredentials');
        exit;
    }
}

function isLoggedIn()
{
    try {
        /**Early Return
         * Check every detail on session-cookie
         */
        //Check, if the browser have any cookie
        if (!isset($_COOKIE[session_name()])) {
            throw new Exception('Browser dosent have cookie'); 
        }

        //make sure to have an active session 
        if (!isset($_SESSION)) {
            session_start();
        }
        
        //check, have a "userId" session-cookie
        if (!isset($_SESSION['userId'])){
            throw new Exception('Browser dosent have cookie'); 
        }

        //merge the url with the neccessery data for an API call
        $userId = $_SESSION['userId'] ?? '';
        $url = 'https://fakestoreapi.com/users/'.$userId;
        $user = json_decode(file_get_contents($url),true);

        //$user = APIcUrlCall($url);

        //check the user still in the database
        if (!$user) {
            throw new Exception('User not in db'); 
        }

        return true;
    } catch (\Throwable $th) {
        logJS("isLogged error: ".$th);
        return false;
    }
} 

function isAuth()
{
    /**
     * Make sure is the user able to see the page
     * or go back to main page
     */
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;  
    }

    header('Location: /');
    exit;
}

function logoutHandler()
{

    if (!isset($_SESSION)) {
        session_start();
    }
 
    $params = session_get_cookie_params();
    setcookie(session_name(), '', 0, $params['path'],$params['domain'],$params['secure'],$params['httponly'] );
    
    session_destroy();

    header ('Location: /');
}

function registerHandler()
{
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':

            $user = array(
                'email' => $_POST['email'],
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'phone' => $_POST['phone'],
            );
            $result = APICreateUser($user);
            //$result = APIcUrlCall("https://fakestoreapi.com/users/","POST",$user);

            /**API statikusan kezeli le a regisztrációt, valódi regisztráció nem történik,
             * viszont a felhasználók száma 10 és amennyiben sikeres az utolsó ID azaz 11essel tér vissza
             */
            if($result['id'] === 11 ){
                header('Location: /?info=registerSuccess');
            }else{
                header('Location: /?info=registerFailed');
            };
            break;
        case 'GET':
            echo render("wrapper.phtml",[
                'content' => render("register.phtml")
            ]);
            break;
    }
}