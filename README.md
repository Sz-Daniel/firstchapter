# firstchapter
FirstChapter: 
Currently, my main focus is on the PHP language and the application of learned functionalities - CRUD, Login, Routes. Once I exhaust my knowledge in this language, I plan to continue with JavaScript, refactor functions, and, if possible, manually format using Bootstrap at the very end.

Code Documentation - Junior Journal

Routes Handler (Step-by-Step)
Retrieve the method used on the previous page = Request method received by the index page -> $_SERVER['REQUEST_METHOD']
Which page the user wants to jump to = Requested page from the request -> $_SERVER['REQUEST_URI']
Create the page map as routes (with a fallback for page not found) -> ?? "notFoundHandler"
PHP is able to call a function where the function name is a string, and this is the basis of the procedure -> $handlerFunction()
  
How Handler works
We will need a compiler that is built with the actual page from the template with a prebuilt page -> compileTemplate
Compiler collect params like form data, sql data and other state data and give to the prebuilded page 
and it give back the whole page as string


Long-term plan:
Once I completely finish this 'chapter,' I will create an entirely different project with an active API source.
The setup and configuration of docker-phpmyadmin-mysql are not mine. I utilize it from a completed course to ensure smooth development.
In the future, there are plans to complete additional courses: advanced PHP - Docker - Node.js - JS framework - PHP framework learning and working with them.
