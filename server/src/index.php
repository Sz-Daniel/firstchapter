<?php
require './upgrade.php';
/** Routes Handler (Step-by-Step)
 *  Retrieve the method used on the previous page = Request method received by the index page -> $_SERVER['REQUEST_METHOD']
 *  Which page the user wants to jump to = Requested page from the request -> $_SERVER['REQUEST_URI']
 *  Create the page map as routes (with a fallback for page not found) -> ?? "notFoundHandler"
 *  PHP is able to call a function where the function name is a string, and this is the basis of the procedure -> $handlerFunction()
 *  
 *  How Handler works
 *  We will need a compiler that is built with the actual page from the template with a prebuilt page -> compileTemplate
 *  Compiler collect params like form data, sql data and other state data and give to the prebuilded page 
 *  and it give back the whole page as string
 */

//Routes Map
$routes = [
    "GET" => [
        '/' => 'homeHandler',
        '/resources' => 'resourcesHandler',
        '/login' => 'loginHandler',
        '/comments' => 'commentHandler',

        '/posts/1/comments' => 'tryHandler',
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



function commentHandler(){
    $url = '/comments?postId='.$_GET['postId'];
    $comments = apiGet($url);

    $url = '/posts/'.$comments[0]['postId'];
    $post = apiGet($url);

    $url = '/users/'.$post['userId'];
    $user = apiGet($url);

    echo render("wrapper.phtml",[
        'content' => render('comments.phtml',[
            'comments' => $comments,
            'post' => $post,
            'user' => $user
        ]),
    ]);  
}

function loginProcessHandler(){
    echo 'Login Process<pre>';
    var_dump($_POST);
    echo '<a href="/">Go to home page</a>';
}

function loginHandler(){
    echo render("wrapper.phtml",[
        'content' => render('login.phtml'),
    ]);    
}

function resourcesHandler(){
    echo render("wrapper.phtml",[
        'content' => render('resources.phtml'),
    ]);
}

function homeHandler(){

    $posts = json_decode(file_get_contents("https://jsonplaceholder.typicode.com/posts"),true);
    echo render("wrapper.phtml",[
        'content' => render("postLists.phtml",[
            'posts' => $posts
        ])
    ]);
};



function render($path, $params=[]){
    ob_start();
    require __DIR__.'/views/'.$path;
    return ob_get_clean();
};

function apiGet($param = "posts/1"){
    $url = "https://jsonplaceholder.typicode.com/".$param;
    return json_decode(file_get_contents($url),true);
}
 
