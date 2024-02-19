# firstchapter
FirstChapter: 
Currently, my main focus is on the PHP language and the application of learned functionalities - CRUD, Login, Routes. Once I exhaust my knowledge in this language, I plan to continue with JavaScript, refactor functions, and, if possible, manually format using Bootstrap at the very end.

Long-term plan:
Once I completely finish this 'chapter,' I will create an entirely different project with an active API source.
The setup and configuration of docker-phpmyadmin-mysql are not mine. I utilize it from a completed course to ensure smooth development.
In the future, there are plans to complete additional courses: advanced PHP - Docker - Node.js - JS framework - PHP framework learning and working with them.

Code Documentation - Junior Journal

As a junior, during development, I first try to rely on my own knowledge, building it piece by piece, and then, when looking at the big picture, I use ChatGPT3.5 to find directions on how to make it more efficient, solve it differently, look up documentation for new features, and explore other online resources. Similarly, I follow this process when dealing with potential issues.

(index.php)
Routes Handler (Step-by-Step)
Retrieve the method used on the previous page = Request method received by the index page -> $_SERVER['REQUEST_METHOD']
Which page the user wants to jump to = Requested page from the request -> $_SERVER['REQUEST_URI']
Create the page map as routes (with a fallback for page not found) -> ?? "notFoundHandler"
PHP is able to call a function where the function name is a string, and this is the basis of the procedure -> $handlerFunction()
  
How Handler works
We will need a compiler that is built with the actual page from the template with a prebuilt page -> compileTemplate
Compiler collect params like form data, sql data and other state data and give to the prebuilded page 
and it give back the whole page as string

Login system (auth.php)
(loginProcessHandler) Called when it get POST data from /login page. To make sure, let's check if the data is appropriate. Using early return to make the process more faster. Do we get a data? Do we get data from API? The username is valid? Password is valid? If all yes, then make a session and create a session-cookie with the userId. We can verify at any time whether the user is still logged in (isLoggedIn) and authorized(isAuth) to view the page. When the user want to logout(logoutHandler) the process get the actual cookies params and with that, set the expires time to 0 so it will delete that data.  

auth.php
loginProcessHandler
At first, I utilized the following code snippet for the inclusion of user data in the database. However, upon subsequent review, I opted for a more efficient, simpler, and more functional approach
$user_index = null;
foreach ($users as $index => $user) {
    if ($user['username'] === $username) {  
        $user_index = $index; 
        break;
    }    
}
Better:
//list, as array, only usernames
$usernameList = array_column($users, 'username');
//search on array, one specific data
$userIndex = array_search($username, $usernameList );


