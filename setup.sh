#!/bin/sh
# Clone repo and runs all tests
git clone https://github.com/diversen/pebble-framework.git && cd pebble-framework
composer install
mkdir config-locale && cp config/DB.php config-locale/
./cli.sh migrate --up
./test.sh

