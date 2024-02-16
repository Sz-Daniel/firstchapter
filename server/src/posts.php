<?php
function resourcesHandler(){
    /**
     * típus-t vizsgáljuk történt-e előzőleg típus kiválasztás
     * az alapján összeállítjuk a megfelelő url-t, föggően melyik forráshoz nyúlunk
     * lekérjük az adatokat és továbbítjuk paraméterként ahol megjelnítődik az 
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