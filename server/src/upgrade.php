<?php
function tryHandler(){
    //   <a href="/posts/1/comments"> try</a>
    $testRoute ='/posts/{id}/comment';
        //Get the method
    $testUrl = '/posts/1/comments';
    echo '<br><pre>';
    var_dump($_SERVER["REQUEST_METHOD"]);
    //Get the URI -> Path
    $parsed = parse_url($_SERVER['REQUEST_URI']);
    echo '<br><pre>';
    var_dump($_SERVER['REQUEST_URI']);
    echo '<br><pre>';
    var_dump($parsed);
    
    echo '<br><pre>';
    var_dump(explode('/',$parsed['path']));
    $temp = explode('/',$parsed['path']);
    array_splice($temp,0,1);
    echo '<br><pre>';
    var_dump($temp);
    }

    function datUrl(){
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        echo '<br><pre>';
        var_dump($parsed);
        echo '<br><pre>';
        var_dump($parsed['query']);
    }

?>