<?php
function loginHandler(){
    echo render("wrapper.phtml",[
        'content' => render('login.phtml'),
    ]);    
}

function loginProcessHandler(){
    /**
     * Needs to check in the whole db first
     * then step-by-step verif the data
     * build up the session for userdata as session-cookie
     * which is the key to keep signed in the user
     */

    // POST data check
    $username = $_POST['username'] ?? "";
    $password = $_POST['password'] ?? "";  

    //Get all user's data
    $url ="https://fakestoreapi.com/users";
    $users = json_decode(file_get_contents($url),true);

    /**
     * Search in db by username
     * If the username exists, store the index for deepcheck
     */
    $user_index = null;
    foreach ($users as $index => $user) {
        if ($user['username'] === $username) {  
            $user_index = $index; 
            break;
        }    
    }

    /**Early Return
     * If the stored user_index not exists go back to Login page with error
     * If the username not exists go back to Login page with error
     */
    if(!isset($user_index)){
        header('Location: /login?info=invalidCredentials');
        exit;
    }

    if(!$users[$user_index]['password']===$password){
        header('Location: /login?info=invalidCredentials');
        exit;    
    }

    /**
     * create a session then 
     * create a session-cookie for user data 
     * then goes back 
     */
    //create a session
    session_start();

    //with active session create a 'userId' cookie
    $_SESSION['userId'] = $users[$user_index]['id'];

    header('Location: /');
    exit;
}

function isLoggedIn(){
    /**Early Return
     * Check every detail on session-cookie
     */
    //Check, if the browser have any session cookie
    if (!isset($_COOKIE[session_name()])) {
        return false;
    }

    //make sure to have an active session 
    if (!isset($_SESSION)) {
        session_start();
    }
    
    //check, have a "userId" session-cookie
    if (!isset($_SESSION['userId'])){
        return false;
    }

    //merge the url with the neccessery data for an API call
    $userId = $_SESSION['userId'] ?? '';
    $url = 'https://fakestoreapi.com/users/'.$userId;
    $user = json_decode(file_get_contents($url),true);

    //check the user still in the database
    if (!$user) {
        return false;
    }

    //win
    return true;
} 

function isAuth(){
    /**
     * Make sure is the user able to see the page
     */
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;  
    }

    header('Location: /');
    exit;
}

function logoutHandler(){

    session_start();
    $params = session_get_cookie_params();

    setcookie(session_name(), '', 0, $params['path'],$params['domain'],$params['secure'],$params['httponly'] );

    session_destroy();

    header ('Location: /');
}
?>