# pebble-framework

A simple and small php framework.

# Requirements

Known to work on:  `PHP >= 7.4.3`

# Install as dependency

    composer require diversen/pebble-framework

# Install for testing

Clone the repo:

    git clone git@github.com:diversen/pebble-framework.git && cd pebble-framework

The framework is coupled against MySQL, so in order to run the tests you will need to edit `config/DB.php`.

You should add a `config-locale` folder and copy the `DB.php` file into this folder. 

    mkdir config-locale && cp config/DB.php config-locale/

`config-locale` is in [.gitignore](.gitignore) to make sure the folder is not commited and stays `locale`. 

Edit the `config-locale/DB.php` file and add a valid `database`, `username`, and `password`

Install dependencies (there is only require-dev dependencies):

    composer install
    
Check if you can connect:

    ./cli.sh db --con

Run DB migrations

    ./cli.sh migrate --up

Then run the unit tests:

    ./test.sh

# Clean up

Install:

    composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer
    ./tools/php-cs-fixer.sh

# Docker MySQL

If you don't have a mysql-server it is quite easy to setup a docker MySQL database:

    docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:5.7

Create a database:

    docker exec -it mysql-server bash
    mysql -uroot -ppassword
    create database pebble;
    exit; # exit from mysql-server 
    exit; # exit from container

# Other docker commands

List conainers 

    docker container ls

Stop container (mysql-server):

    docker stop mysql-server

Start container (mysql-server) again:

    docker start mysql-server

Remove container (you will need run 'run' command again):

    docker rm mysql-server

# License

MIT © [Dennis Iversen](https://github.com/diversen)
