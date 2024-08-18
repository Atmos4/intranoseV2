The entire website is built with PicoCSS's philosophy:

> let's keep it graceful and simple.

- Home-made framework based on [PHPRouter](https://phprouter.com/)
- SQLite
- Doctrine ORM
- PicoCSS
- Home-made form builder and validation

# How to use

Prerequisites:

1. Install Wamp/Xampp/other. Include IntlDateFormatter and PDO to your PHP install. Make sure the PHP install works.
2. Install composer.

Next:

1. _Optional_: Create a new database inside your env.
1. Create a `.env` file based on `.env.example` and change the values to match those of your local env. If you don't specify any `DB_HOST`, the app is going to fallback to a SQLite database located in the `.sqlite` directory.
1. Run `composer sync`. It should install composer dependencies and setup the database.
1. _Optional_: you can also run `php bin/seed` to seed the database with fake data!
