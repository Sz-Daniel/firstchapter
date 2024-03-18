<?php
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
    require __DIR__.'/../views/'.$path;
    return ob_get_clean();
}
