
<h1> Installation Procedure</h1>

###Requirements
Composer should be installed on your system in order to run the project. 
Composer can be downloaded from the official website https://getcomposer.org/download/
####Composer
After downloading composer install it globally in your system to make project any where 
in your system. 
####Mamp for local server
Xammp/mampp should be installed in your system for localserver
To download Mamp visit their site https://www.mamp.info/en/downloads/ and download 
latest version of Mamp.

####CODE EDITOR
You can download any code editor for project. PHPStorm is preferable.
You can download PHPStorm from https://www.jetbrains.com/phpstorm/download/

##DB Configuration
After completing all the requirement. Just place the project any where and 
start your server by running Mamp and start services
First of all make a database in localhost/phpmyadmin and name your 
database as "mattermost".
.env File is already configured. you may need to change the <br>
DB_PORT, <br>
DB_USERNAME, <br>
DB_PASSWORD<br>
####Run migration or make tables manually in  Local host
You can make tables in 2 ways for the project after configuring. First method 
is this you can simply run the command in the editor terminal "php artisan migrate". 
This command will migrate all the migrations present in the project. 
Other way to make tables is by making manually in the localhost. Just open your mattermost
You need 3 tables <br>
users<br>
messages<br>
password_resets <br>

the preffered way is by running migrations <br>

Models and controllers are also made and proper routes are also defined in
api.php file in ROUTES

After this you can hit the route in POSTMAN.
