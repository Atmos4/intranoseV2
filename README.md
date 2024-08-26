The entire website is built with PicoCSS's philosophy:

> let's keep it graceful and simple.

- Home-made framework based on [PHPRouter](https://phprouter.com/)
- SQLite
- Doctrine ORM
- PicoCSS
- Home-made form builder and validation

# How to use

## Fastest way to start: PHP - SQLite

1. Install php latest version (8.2 works). Include IntlDateFormatter and PDO to your PHP install. Make sure the PHP install works.
2. Install composer
3. `composer install`
4. `composer seed` - sets up the local SQLite database, runs migrations and adds test data
5. `composer dev` - starts the local dev server

That's it. Optionally you can tweak some things in a `.env` file

## Slower way to start: Apache - PHP - MySQL/SQLite

Prerequisites:

1. Install Wamp/Xampp/other Lamp stack tool. Include IntlDateFormatter and PDO to your PHP install. Make sure the PHP install works.
2. Install composer
3. `composer install`
4. _Optional_: Create a new MySQL database inside PhpMyAdmin, and add the credentials
5. Create a `.env` file based on `.env.example` and change the values to match those of your local env. If you don't specify any `DB_HOST`, the app is going to fallback to a SQLite database located in the `.sqlite` directory.
6. Run `composer sync`. It should install composer dependencies and setup the database.
7. _Optional_: you can also run `php bin/seed` to seed the database with fake data!
