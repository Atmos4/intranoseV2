The entire website is built with PicoCSS's philosophy:

> let's keep it graceful and simple.

- Home-made framework based on [PHPRouter](https://phprouter.com/)
- Doctrine ORM
- PicoCSS
- Home-made form builder and validation
- Docker for an easy install

# How to use

Prerequisites:

1. Install docker. I recommend installing docker desktop.
2. If you are on Windows, install WSL and put the directory in a Linux install. This way, docker will be much faster.
3. Run `docker compose up`

Next:

1. Create a new database. You can deal with the database through the PhpMyAdmin image.
2. Create a `.env` file based on `example.env`.
3. Run `yarn setup` from inside the php-apache container. It should install composer dependencies and setup the database.
4. Whenever you pull new changes or create new files in `database/models`, run `yarn update` also from the php-apache container to keep your DB and autoload up to date!
