# firstchapter

Currently, my main focus is on the PHP language and the application of learned functionalities such as MySQL, CRUD, Login, and Routes, MVC. Once I exhaust my knowledge in this language, I plan to continue with JavaScript, refactor functions, and, if possible, manually format using Bootstrap at the very end.

### Long-term plan:

Once I completely finish this 'chapter,' I will create an entirely different project with an active API source. 
The setup and configuration of docker-phpmyadmin-mysql is not mine. I utilize it from a completed course to ensure smooth development. 
In the future, there are plans to complete additional courses: advanced PHP, Docker, Node.js, JS framework, PHP framework, learning, and working with them.

# Code Documentation - Junior Journal - JJ

As a junior developer, my approach to development is iterative. I initially rely on my own knowledge to build the solution piece by piece. Then, when considering the bigger picture, I use ChatGPT3.5 to seek directions on how to optimize it, explore alternative solutions, look up documentation for new features, and explore other online resources. Similarly, I follow this process when addressing potential issues.
>  **JJ**  in these sections, deeper explanations and alternative solutions are provided.
  
In many cases, I intentionally use diverse solutions, technologies, and functions. I wanted to showcase the evolution of my solutions and the use of different technologies. For example, in the `homeHandler`, I simply fetch data using the `file_get_contents` function, while in the `api.php`, I've created a dynamic cURL process for versatile API calls, which is often provided as an alternative solution in comments.

Step-by-step explanations are directly available in the code in the form of comments.

Over time, I restructured the folder hierarchy to follow an MVC approach. Within that, I attempted to organize files based on functionality. If necessary and if multiple files were related to a specific area, I grouped them into subfolders.
```
src/
│
├── controller/
│   ├── auth.php
│   ├── feed
│   │   ├── comments.phtml
│   │   └── postLists.phtml
│   ├── resource.php
│   ├── routes.php
│   ├── scretch.php
│   └── users.php
├── model/
│   ├── api.php
│   └── mysql.php
├── views/
│   ├── auth
│   │   ├── login.phtml
│   │   └── register.phtml
│   ├── feed.phtml
│   ├── resources.phtml
│   ├── scretch.phtml
│   ├── users.phtml
│   └── wrapper.phtml
├── public/
├── utils.php
└── index.php

```
#### Direct redirection:
[index.php](#index)  [utils.php](#utils)  
**Controllers** [routes.php](#routes) [auth.php](#auth)  
**Model** [mysql.php](#mysql) [api.php](#api)   



## index

The maintenance of the library is crucial, with the routes being called last, ensuring that they have access to everything.

## utils
### `logDB`
>Over time, the naming was changed from 'logJS' to 'logDB'.
#### First version

In the case of JavaScript, it was a convenient solution during development to use `console.log` for debugging purposes, printing out states, results, and parameters. Therefore, when calling the script within a PHP function, any number of parameters can be invoked, each of which will be executed individually, and the output of PHP `console.log` as well as the handling of any error codes will also be printed to the console.
```
function logJS(...$dataArray){
   //Params: if I want to Give a string before, or multiply data to log in a same time, then I have to use '...'
   foreach ($dataArray as $data) {
      if ($data instanceof Exception) {
         //In Exception case, we get the Error message
         echo '<script>console.log('.json_encode($data->getMessage()).');</script>';
      }else {
         //or just we want to check some data, sometimes easier to read then var_dump
         echo '<script>console.log('.json_encode($data).');</script>';
     }
   }
}
```
#### Second version

Due to the Header Location issue, it is not advisable to use the first version in the long run, as it also prints out the workspace, making it impossible for the header: location to take effect afterward.

To handle this, I created an SQL table:

-   id: unnecessary at some level, merely a formality
-   response: what would be logged in the console
-   date: timestamp, defined in seconds for easier tracking

Considering that the original naming was based on the use of a JavaScript function, it was later renamed to `logDB`.

## Controllers

## routes

#### How Handler works
Retrieve the method used on the previous page: Request method received by the index page -> `$_SERVER['REQUEST_METHOD']`

Which page the user wants to jump to: Requested page from the request -> `$_SERVER['REQUEST_URI']`

Create the page map as routes (with a fallback for page not found): Use a `notFoundHandler`

PHP is able to call a function where the function name is a string, and this is the basis of the procedure -> `$handlerFunction()`

We will need a compiler that is built with the actual page from the template with a prebuilt page -> compileTemplate `render($path, $params=[])`. Collect params like form data, SQL data, and other state data and give them to the pre-built page, and it will return the entire page as a string.


## auth

### Login system

The `loginHandler` simultaneously handles the rendering of the page for GET requests and the authentication process after submitting the login form via POST. To ensure correctness, let's verify if the data is appropriate. We'll use early return to expedite the process. Do we have data? Are we receiving data from the API? Is the username valid? Is the password valid? If all conditions are met, then create a session and set a session cookie with the `userId`. We can verify at any time whether the user is still logged in using `isLoggedIn` and authenticated using `isAuth` to view the page. When the user wants to logout, the `logoutHandler` retrieves the current cookie parameters and sets the expiration time to 0, thereby deleting that data.

**loginProcessHandler**

At first, I employed the following code snippet to incorporate user data into the database. However, upon further review, I decided to adopt a more efficient, streamlined, and functional approach.

Old version:

 ```
$user_index = null;
foreach ($users as $index => $user) {
   if ($user['username'] === $username) {
       $user_index = $index;
       break;
   }
}
 ```

Better version:

 ```
//list, as array, only usernames
$usernameList = array_column($users, 'username');
//search on array, one specific data
$userIndex = array_search($username, $usernameList );
```

**registerHandler**
The `registerHandler` functions similarly to the `loginHandler`. When accessed via the GET method, the page is generated for viewing, while when accessed via POST, the registration process takes place.

## Model
I tackled specific tasks either with MySQL or API handling. However, I managed user data management concurrently on both MySQL and API levels.
## mysql

Given that I don't have direct access to the data table, it needs to be created first and populated with data before it can be managed with functions. The process runs in the `checkSQL`, which first tests for the existence of the table; if it doesn't exist, it creates it. If it already exists, it compares the data to ensure its correctness. However, if there are discrepancies, the entire data package is uploaded, and a log tracks which data has been uploaded.

### checkSQL

An additional procedure is required to ensure that the database table and data are ready. It needs to check if the table exists; if not, create it. I've tried multiple approaches.

#### Table

Using the CREATE TABLE procedure along with error handling, we can determine whether the table existed previously (error code: 42S01).

If the table already existed, we need to check if the data quantity in the table matches the data from the API source. If the conditions are met, we exit the check with an early return.

In the case of creating the table at this point, we need to populate it. If the table needs to be updated due to differences or incompleteness, it also needs to be populated. Additionally, an ID is uploaded here and after successful population, we use `addAutoIncr` to put the ID into AUTO increment mode, ensuring it continues properly and does not conflict with existing data.
> **JJ** Using the `IF NOT EXISTS` expression, I attempted to proceed, but during execution, there is no data to determine whether the table existed previously, and I intend to have visibility into the processes. After some research, I came across the procedure `SELECT 1 FROM information_schema.tables WHERE table_schema = database() AND table_name = ?`, which proved to be perfect for a preliminary check. However, once I created the CREATE TABLE procedure, it became clear that the table would not be created if it already existed in this way. Therefore, through error handling, I query the appropriate error code, which makes it clear whether the table existed before or not.

#### Data
The SQL data upload is performed using `uploadDataBatchExe`. In this case, each insert query is parameterized individually and executed, and we handle the results accordingly. Besides error handling, it's necessary to encapsulate the process within a transaction (begin transaction - commit), which, in case of a failed execution, rolls back to the state before the event, avoiding incomplete modification issues.

> **JJ** I found several methods for implementing a similar functionality, but I chose this method because it's simpler and I feel more confident using it. In this case, where only 10 records are involved, efficiency considerations are not entirely necessary. However, for larger datasets, it would be more advisable to consider the others. 'Bulk insert' - where multiple VALUES are attached to a single INSERT statement. 'multi_query' - can be used with the mysqli type, but I currently use PDO. Here, multiple SQL statements can be provided in advance, concatenated together, and then executed, with the system moving forward handling the results for each.

## api

cURL - Up until now, I used the basic `file_get_contents` function for API handling. I attempted to use cURL, which had been mentioned several times during my learning journey, but I wanted to focus on mastering one language/library at a time.

Initially, I tried to print out a simple dataset, like 'users'. Once the printing was successful, I wanted to modify, expand, and test it. Initially, I encountered an issue where `CURLOPT_RETURNTRANSFER` was not set, causing some trouble in the output. Initially, I purposefully created functions for each task, then for each API method, and once I reached the end and understood the process, I was able to simply put together one function to handle any API. I didn't rewrite everything in the code, but as an alternative for API requests, I placed them in comments.

