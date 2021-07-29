# pebble-framework

A simple, small, and fast php framework. 

# Requirements

Known to work on:  `PHP >= 7.2.24`

# Test

## Create a MySQL database

The framework is tightly coupled against MySQL, so in order to run the tests you will need to edit `config/DB.php` and add a correct database. 

Into this database you will need to load `sql/mysql.sql`, 

Then run the unit tests:

    ./Pebble/test.sh

# License

MIT © [Dennis Iversen](https://github.com/diversen)
