<?php


function homeHandler()
{

    $posts = json_decode(file_get_contents("https://jsonplaceholder.typicode.com/posts"),true);
    //$posts = APIcUrlCall("https://jsonplaceholder.typicode.com/posts");

    echo render("wrapper.phtml",[
        'content' => render("/feed/postLists.phtml",[
            'posts' => $posts
        ])
    ]);
}

function commentHandler()
{
    /**
     *  GET postId from postList.phtml href="/comments?postId=$post['id']?>"
     *  apiGetCall is a simple php file_get_content result function with the 
     *  embedded url
     */
    //Original post data call
    $postId = $_GET['postId'] ?? '';
    $queryPost = '/posts/'.$postId;
    $post = apiGetCall($queryPost);

    //Author info
    $authorId = $post['userId'];
    $queryAuthor = '/users/'.$authorId;
    $user = apiGetCall($queryAuthor);

    //Post comments
    $queryComments = '/posts/'.$postId.'/comments';
    $comments = apiGetCall($queryComments);

    //http://localhost:8080/comments?postId=? -> comments.phtml
    echo render("wrapper.phtml",[
        'content' => render('comments.phtml',[
            'post' => $post,
            'user' => $user,
            'comments' => $comments,
        ]),
    ]);  
}