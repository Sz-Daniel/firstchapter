<?php
function resourcesHandler(){
    /**
     * check the type from POST
     * merge the url with params, depends on source
     * query the data and send back to the resources page
     */
    $content ="";
    if (isset($_POST['type'])) {
        $type = $_POST['type'];
    
        $url = ($type === 'https://fakestoreapi.com/users') ? $type : "https://jsonplaceholder.typicode.com/" . $type;
        
        $response = file_get_contents($url);
        $content = json_decode($response, true);
    }

    //http://localhost:8080/resources -> resources.phtml
    echo render("wrapper.phtml",[
        'content' => render('resources.phtml',[
            'content' => $content
        ]),
    ]);
}

function commentHandler(){
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

?>