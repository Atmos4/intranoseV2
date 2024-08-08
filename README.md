The entire website is built with PicoCSS's philosophy:

> let's keep it graceful and simple.

- Home-made framework based on [PHPRouter](https://phprouter.com/)
- Doctrine ORM
- PicoCSS
- Home-made form builder and validation
- Docker for an easy install

# How to use

Prerequisites:

1. Install Wamp/Xampp/other. Include IntlDateFormatter and PDO to your PHP install. Make sure the PHP install works.
2. Install composer.

Next:

1. Create a new database inside your env.
2. Create a `.env` file based on `.env.example` and change the values to match those of your local env.
3. Run `composer setup`. It should install composer dependencies and setup the database.
4. Whenever you pull new changes or create new files in `database/models`, run `composer update:all` to keep your DB and autoload up to date!

## Docker Version

1. Install docker. I recommend installing docker desktop.
2. Create a `.env` file based on the env values contained in the `.devcontainer/devcontainer.json` file
3. Run `docker compose up`

When you run `docker compose up`, composer packages will be installed and migrations executed automatically.

4. Go to http://localhost/dev/create-user to create a first user

You're good to go !

### Alternate - DevContainer

An other solution is to use the dev container defined in the `.devcontainer` folder. Then, you can either develop locally on you computer using devcontainer extensions on vscode or other IDE, or use a **codespace** on github.
