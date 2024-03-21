<?php 
// /users GET 
function usersHandler(){
    checkSQL();
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

// /users/delete?userId= GET
function deleteUserHandler(){
    //datacheck
    if (isset($_GET['userId']) && !empty($_GET['userId'])){
        $id =(int)$_GET['userId'];

        $resp = SQLDeleteUserById($id);

        if(!$resp){
            header('Location: /users?info=userDelFail');
        }else{
            header('Location: /users?info=userDel');
        }
    
    }
}

// /users?editId= POST
function editUserHandler(){
    $user = array(
        //from hidden field with id value
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
