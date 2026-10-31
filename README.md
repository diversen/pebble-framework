# pebble-framework

A small framework and library for creating PHP applications.

# Documentation 

[https://diversen.github.io/pebble-framework-docs/](https://diversen.github.io/pebble-framework-docs/)


# Install

Requirements (PHP >= 8.1):

    composer require diversen/pebble-framework:^12.0

# Install for testing

Clone the repo:

    git clone git@github.com:diversen/pebble-framework.git && cd pebble-framework

The framework is coupled against MySQL, so in order to run the tests you will need to edit `config/DB.php`.

You should add a `config-locale` folder and copy the `DB.php` file into this folder. 

    mkdir config-locale && cp config/DB.php config-locale/

`config-locale` is in [.gitignore](.gitignore) to make sure the folder is not commited and stays `locale`. 

Edit the `config-locale/DB.php` file and add a valid `database`, `username`, and `password`

Alternatively, set `PEBBLE_DB_URL`, `PEBBLE_DB_USERNAME`, and
`PEBBLE_DB_PASSWORD`. The tracked configuration does not contain database
credentials.

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

# Security configuration

Authentication cookies support `cookie_samesite` (`Lax`, `Strict`, or `None`)
and default to `Lax`. Cookies are `Secure` and `HttpOnly` by default.
`SameSite=None` requires `cookie_secure` to be enabled.
Browser-session authentication cookies are bounded on the server by
`cookie_session_seconds`, which defaults to 24 hours.

Session configuration supports `samesite` with the same values and defaults to
`Lax`; session cookies are also `Secure` and `HttpOnly` by default. Set
`App.server_url` to the canonical origin and enable `App.force_ssl` in
production. Host headers are not used to build redirects or canonical URLs.

The router validates CSRF tokens automatically for `POST`, `PUT`, `PATCH`, and
`DELETE`. Forms may send `csrf_token`; API clients may use the `X-CSRF-Token`
header. Bearer-authenticated/webhook routes must be explicitly listed with
`Router::setCSRFExclusions()`, or protection can be disabled for a separately
secured router with `Router::setCSRFProtection(false)`. Development mode does
not disable CSRF validation.

Authentication-cookie expiry values are stored as absolute Unix timestamps.
Migration `0003` changes `auth_cookie.expires` to `BIGINT UNSIGNED`, stores only
SHA-256 hashes of high-entropy login tokens, and makes token lookup unique.
Existing login cookies are intentionally invalidated. Password changes revoke
all login cookies.

Template files must resolve beneath the configured template root. `Tag::getTag()`
escapes text and attributes; use `Tag::getRawTag()` only for trusted HTML.

# Docker MySQL

Install (run) a MySQL image that will work:

    docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:8.0

Create a database:

    ./cli.sh db --server-connect
    CREATE DATABASE `pebble`;

# Other docker commands

Install (run) a MySQL image that will work:

    docker run -p 3306:3306 --name mysql-server -e MYSQL_ROOT_PASSWORD=password -d mysql:8.0

List containers 

    docker container ls

Stop container (mysql-server):

    docker stop mysql-server

Start container (mysql-server) again:

    docker start mysql-server

Remove container (you will need to run the 'run' command again):

    docker rm mysql-server

# License

MIT © [Dennis Iversen](https://github.com/diversen)
