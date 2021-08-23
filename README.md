# pebble-framework

A simple, small, and fast php framework. 

# Requirements

Known to work on:  `PHP >= 7.2.24`

# Run tests

## Create a MySQL database

You can set up a docker MySQL database:

    docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:5.7

Create a database:

    docker exec -it mysql-server bash
    mysql -uroot -ppassword
    create database pebble;
    exit; # exit from mysql-server 
    exit; # exit from container

The framework is tightly coupled against MySQL, so in order to run the tests you will need to edit `config/DB.php` and add a correct database. 

Into this database you will need to load `sql/mysql.sql`, 

    docker exec -i mysql-server mysql -uroot -ppassword pebble  < ./sql/mysql.sql

Then run the unit tests:

    ./Pebble/test.sh

# License

MIT © [Dennis Iversen](https://github.com/diversen)
