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