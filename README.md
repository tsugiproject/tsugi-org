The Tsugi Learning Applications Framework
=========================================

Course materials for www.tsugi.org

Setup On Localhost
------------------

Here are the steps to set this up on localhost on a Macintosh using MAMP.

Install MAMP (or similar) using https://www.py4e.com/install.php

Check out this repo into a top level folder in htdocs

    cd /Applications/MAMP/htdocs
    git clone https://github.com/tsugiproject/tsugi-org.git

Go into the newly checked out folder and get a copy of Tsugi as well as the exercises:

    cd tsugi-org
    git clone https://github.com/tsugiproject/tsugi.git
    git clone https://github.com/tsugiproject/tsugi-php-exercises exercises
    git clone https://github.com/tsugiproject/tsugi-php-solutions solutions

The solutions repo is private and if you not a member of the project, you don't really need it.

Create a database:

    CREATE DATABASE tsugi DEFAULT CHARACTER SET utf8;

Create a user in MySQL 5.x and give it permissions on the table:

    GRANT ALL ON tsugi.* TO 'ltiuser'@'localhost' IDENTIFIED BY 'ltipassword';
    GRANT ALL ON tsugi.* TO 'ltiuser'@'127.0.0.1' IDENTIFIED BY 'ltipassword';

For MySQL 8.x the stntax is a bit different:

    CREATE USER 'ltiuser'@'localhost' IDENTIFIED BY 'ltipassword';
    CREATE USER 'ltiuser'@'127.0.0.1' IDENTIFIED BY 'ltipassword';
    GRANT ALL ON tsugi.* TO 'ltiuser'@'127.0.0.1';
    GRANT ALL ON tsugi.* TO 'ltiuser'@'localhost';

Still in the tsugi folder set up config.php:

    cp config-dist.php config.php

Edit the config.php file, scroll through and set up all the variables.  As you scroll through the file
some of the following values are the values I use on my MAMP:

    $wwwroot = 'http://localhost:8888/tsugi-org/tsugi';   // Embedded Tsugi localhost
    
    ...
    
    $CFG->pdo = 'mysql:host=127.0.0.1;port=8889;dbname=tsugi'; // MAMP
    $CFG->dbuser    = 'ltiuser';
    $CFG->dbpass    = 'ltipassword';
    
    ...
    
    $CFG->adminpw = 'short';
    
    ...
    
    $CFG->apphome = 'http://localhost:8888/tsugi-org';
    $CFG->context_title = "Building Learning Applications with Tsugi";
    // $CFG->lessons = $CFG->dirroot.'/../lessons.json';
    
    ... 
    
    $CFG->tool_folders = array("admin", "../tools", "../mod", 
        "../exercises", "../solutions");
    $CFG->install_folder = $CFG->dirroot.'./../mod'; // Tsugi as a store
    
    ...
    
    $CFG->servicename = 'WA4E';

Then go to https://console.developers.google.com/apis/credentials and
create an "OAuth Client ID".  Make it a "Web Application", give it a name,
put the following into "Authorized JavaScript Origins":

        http://localhost

And this into authorized redirect URIs:

    http://localhost/tsugi-org/tsugi/login.php

Note: You do not need port numbers for either of these values in your Google
configuration.

Google will give you a 'client ID' and 'client secret', add them to `config.php`
as follows:

    $CFG->google_client_id = '96..snip..oogleusercontent.com';
    $CFG->google_client_secret = 'R6..snip..29a';

While you are there, you could "Create credentials", select "API
key", and name the key "My Google MAP API Key" and put the API
key into `config.php` like the following:

    $CFG->google_map_api_key = 'AIza..snip..9e8';

Starting the Application
------------------------

After the above configuration is done, navigate to your application:

    http://localhost:8888/tsugi-org/

It should complain that you have not created tables and suggest you
use the Admin console to do that:

    http://localhost:8888/tsugi-org/tsugi/admin

It will demand the `$CFG->adminpw` from `config.php` (above) before
unlocking the admin console.  Run the "Upgrade Database" option and
it should create lots of tables in the database and the red warning
message about bad database, should go away.

Note: As of the latest version of Tsugi, 201705032100, a random password
is chosen for google.com as the table is created.  You are welcome to
change it to something using the code below:

Go into the database and the `lti_key` table, find the row with the `key_key`
of google.com and put a value in the `secret` column - anything will do -
just don't leave it NULL or the internal LTI tools will not launch.

    UPDATE lti_key SET secret = '35nKN...lDzRbw' WHERE key_key="google.com";

If you don't do this, the lessons tool will not be able to launch LTI
links even if you do log in.  Make sure to log out and back into Tsugi
in your browsers once this has been done.

Using the Application
---------------------

Navigate to:

    http://localhost:8888/tsugi-org/

You should click around and see if things work.
