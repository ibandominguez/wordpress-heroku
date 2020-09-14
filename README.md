# wordpress-heroku

This project uses wordpress as a dependency and configures the themes and plugins that are required.
The goal of this repository is to create a backend for clients apps that can be deployed to Heroku.

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/ibandominguez/wordpress-heroku)

The app is automatically provisioned and adds aws s3 support out of the box using *Cloudcube*(https://elements.heroku.com/addons/cloudcube) and a running db using *Jaws DB Mysql*(https://elements.heroku.com/addons/jawsdb).

In less that a minute you should have wordpress up and running.

## Features

* Uses a single database and creates dynamic tables based on the url prefix so that the same core can be used for multiple backends.
* Uses s3 for file storage, since Heroku doesn't support file persistence.

## Getting started

* Edit de config/wp-config.php according to your needs
* Develop your theme (/themes directory)
* Add your required plugins (/plugins directory)
* Configure your ENV vars

## Serving locally

To make it the easiest to setup, we are using the php built in web server and mysql installed locally

```sh
mysql.server start # requires mysql installed locally
php serve.php # requires php installed locally
```

## LICENSE

MIT
