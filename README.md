The entire website is built with PicoCSS's philosophy:

> let's keep it graceful and simple.

- Home-made framework based on [PHPRouter](https://phprouter.com/)
- Doctrine ORM
- PicoCSS
- Home-made form builder and validation

# How to use

Prerequisites:

1. Install Wamp/Xampp/other. Include IntlDateFormatter and PDO to your PHP install. Make sure the PHP install works.
2. Install composer.

Next:

1. Create a new database inside your env.
2. Create a `.env` file based on `.env.example` and change the values to match those of your local env.
3. Run `composer setup`. It should install composer dependencies and setup the database.
4. Whenever you pull new changes or create new files in `database/models`, run `composer update:all` to keep your DB and autoload up to date!
