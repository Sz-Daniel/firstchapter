<?php

//resourcesHandler,commentHandler
require './posts.php';

//loginHandler,loginProcessHandler,isLoggedIn,isAuth,logoutHandler
require './auth.php';

require './SQLFunctions.php';
require './APIFunctions.php';

//Routes Map
$routes = [
    "GET" => [
        //User functions
        '/login' => 'loginHandler',
        '/logout' => 'logoutHandler',
        '/register' => 'registerHandler',

        //Nav bar
        '/' => 'homeHandler',
        '/resources' => 'resourcesHandler',
        '/scretch' => 'scretchHandler',
        '/users' => 'usersHandler',

        //Page functions with params
        //From main page -> http://localhost:8080/comments?postId=?
        '/comments' => 'commentHandler',
        //From http://localhost:8080/users/delete?userId=?
        '/users/delete' => 'deleteUserHandler',
    ],
    "POST" => [
        '/resources' => 'resourcesHandler',
        '/login' => 'loginHandler',
        '/users/edit' => 'editUserHandler',
        '/register' => 'registerHandler',
    ]
];

//Get the method
$method = $_SERVER["REQUEST_METHOD"];

//Get the URI -> Path
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

//Page map and Got data checking
$handlerFunction = $routes[$method][$path] ?? "notFoundHandler";

//Double check - As function exists
$safeHandlerFunction = function_exists($handlerFunction) ? $handlerFunction : "notFoundHandler";

//Handler call
$safeHandlerFunction();

function render($path, $params=[])
{
    ob_start();
    require __DIR__.'/views/'.$path;
    return ob_get_clean();
}

function apiGetCall($param)
{
    $url = "https://jsonplaceholder.typicode.com/".$param;
    return json_decode(file_get_contents($url),true);
}

function homeHandler()
{

    $posts = json_decode(file_get_contents("https://jsonplaceholder.typicode.com/posts"),true);
    //$posts = APIcUrlCall("https://jsonplaceholder.typicode.com/posts");

    echo render("wrapper.phtml",[
        'content' => render("postLists.phtml",[
            'posts' => $posts
        ])
    ]);
}

function scretchHandler()
{
    /**
     * Place to trying new functions
     */

     echo render("wrapper.phtml",[
        'content' => render("scretch.phtml",[
        ])
    ]);
}

function usersHandler()
{
    $id = '';
    if (isset($_GET['editId']) && !empty($_GET['editId'])){
        $id =(int)$_GET['editId'];
    }

    $users = SQLGetUsers();
    //$users = APIGetUsers();
    //$users= APIcUrlCall("https://fakestoreapi.com/users"); 
    
    echo render("wrapper.phtml",[
        'content' => render("users.phtml",[
            'users' => $users,
            'editId' => $id
        ])
    ]);
}

function deleteUserHandler()
{
    //datacheck
    if (isset($_GET['userId']) && !empty($_GET['userId'])){
        $id =(int)$_GET['userId'];

        $resp = SQLDeleteUserById($id);

        if(!$resp){
            header('Location: /users?info=userDelFail');
        }else{
            header('Location: /users?info=userDel');
        }
    
    }else{
        logJS("userId not set or empty");
        header('Location: /users?info=errorUserDelete');
    }
}

function editUserHandler()
{
    $user = array(
        'id' => (int)$_POST['id'],
        'email' => $_POST['email'],
        'username' => $_POST['username'],
        'pwd' => $_POST['password'],
        'phone' => $_POST['phone'],
    );
    $resp = SQLEditUserById($user);
    
    if(!$resp){
        header('Location: /users?info=userEditFail');
    }else{
        header('Location: /users?info=userEdited');
    }
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

function logJS(...$dataArray)
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