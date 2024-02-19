<?php
//prototype functions for trying something
require './upgrade.php';

//resourcesHandler,commentHandler
require './posts.php';

//loginHandler,loginProcessHandler,isLoggedIn,isAuth,logoutHandler
require './auth.php';

//Routes Map
$routes = [
    "GET" => [
        '/' => 'homeHandler',
        '/resources' => 'resourcesHandler',
        '/login' => 'loginHandler',
        '/logout' => 'logoutHandler',
        '/comments' => 'commentHandler',

    ],
    "POST" => [

        '/resources' => 'resourcesHandler',
        '/login' => 'loginProcessHandler',
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

function render($path, $params=[]){
    ob_start();
    require __DIR__.'/views/'.$path;
    return ob_get_clean();
};

function apiGetCall($source , $param = "users/1"){
    /** Dinamic API call, GET method, source controlled
     *  First param for schoose which API source needed
     *      0 - https://fakestoreapi.com/
     *      1 - https://jsonplaceholder.typicode.com/
     *  Second param for subpage + query
     *  "users/1" is exists both cases
     *  return with result as assoc array
     */
    switch ($source) {
        case 0:
            $url = "https://fakestoreapi.com/".$param;
            break;
        case 1:
            $url = "https://jsonplaceholder.typicode.com/".$param;
            break;
    }
    return json_decode(file_get_contents($url),true);
}

function homeHandler(){

    $posts = json_decode(file_get_contents("https://jsonplaceholder.typicode.com/posts"),true);
    echo render("wrapper.phtml",[
        'content' => render("postLists.phtml",[
            'posts' => $posts
        ])
    ]);
};

