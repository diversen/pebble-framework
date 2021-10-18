# pebble-framework

A simple, small, and fast php framework. 

# Requirements

Known to work on:  `PHP >= 7.4.3`

# Run tests

# Edit config

The framework is coupled against MySQL, so in order to run the tests you will need to edit `config/DB.php` and add a correct database.

You can also add a `config-locale` folder and copy the `DB.php` file into this folder. 

Edit `DB.php` and add a valid database, username, and password

## Create a MySQL database

You can set up a docker MySQL database:

    docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:5.7

Create a database:

    docker exec -it mysql-server bash
    mysql -uroot -ppassword
    create database pebble;
    exit; # exit from mysql-server 
    exit; # exit from container

Into this database you will need to load SQL found in `migrations`, 

    ./cli.sh migrate --up

Then run the unit tests:

    ./Pebble/test.sh

# License

MIT © [Dennis Iversen](https://github.com/diversen)
