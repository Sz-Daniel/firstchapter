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

    echo render("wrapper.phtml",[
        'content' => render('resources.phtml',[
            'content' => $content
        ]),
    ]);
}

function commentHandler(){
    /** GET the post id
     * 
     */
    $url = '/comments?postId='.$_GET['postId'];
    $comments = apiGetCall(1,$url);

    $url = '/posts/'.$comments[0]['postId'];
    $post = apiGetCall(1,$url);

    $url = '/users/'.$post['userId'];
    $user = apiGetCall(1,$url);

    echo render("wrapper.phtml",[
        'content' => render('comments.phtml',[
            'comments' => $comments,
            'post' => $post,
            'user' => $user
        ]),
    ]);  
}

?>