<?php

return [
    'url' => getenv('PEBBLE_DB_URL') ?: 'mysql:host=127.0.0.1;dbname=pebble',
    'username' => getenv('PEBBLE_DB_USERNAME') ?: '',
    'password' => getenv('PEBBLE_DB_PASSWORD') ?: '',
];
