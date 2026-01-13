The entire website is built with PicoCSS's philosophy:

> let's keep it graceful and simple.

- Home-made framework based on [PHPRouter](https://phprouter.com/)
- SQLite
- Doctrine ORM
- PicoCSS
- Home-made form builder and validation

# How to use

1. Install php latest version (8.2 works). Include IntlDateFormatter and PDO to your PHP install. Make sure the PHP install works.
2. Install composer
3. `composer install`
4. `composer seed` - sets up the local SQLite database, runs migrations and adds test data
5. `composer dev` - starts the local dev server

That's it. Optionally you can tweak some things in a `.env` file. Check out `/engine/load_env.php` to know what you can use.

# Release process

- create a tag. Respect semantic versioning (major.minor.patch), e.g. v5.4.3
- push tag, release is automatic.
