# pebble-framework

A simple, small, and fast php framework. 

# Requirements

Known to work on:  `PHP >= 7.4.3`

# Install as dependency

    composer require diversen/pebble-framework

# Install for test

    git clone https://github.com/diversen/pebble-framework.git && cd pebble-framework

The framework is coupled against MySQL, so in order to run the tests you will need to edit `config/DB.php`.

You can also add a `config-locale` folder and copy the `DB.php` file into this folder. 

    mkdir config-locale && cp config/DB.php config-locale/

`config-locale` is in `.gitignore` to make sure the folder is `locale`. 

Edit one of the `DB.php` files and add a valid `database`, `username`, and `password`

# Install

Install dependencies (there is only require-dev dependencies):

    composer install
    
Check if you can connect:

    ./cli.sh db --con

Run DB migrations

    ./cli.sh migrate --up

Then run the unit tests:

    ./test.sh

# Dependencies

Most classes can be used without any other dependencies, but if you want to use

`Pebble\Captcha` you will need:

    composer require gregwar/captcha:^1.1

`Pebble\SMTP` you will need:

    composer require phpmailer/phpmailer:^6.0
    composer require erusev/parsedown:^1.7

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
