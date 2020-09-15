# wordpress-heroku

This project uses wordpress as a dependency and configures the themes and plugins that are required.
The goal of this repository is to create a backend for clients apps that can be deployed to Heroku.

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/ibandominguez/wordpress-heroku)

The app is automatically provisioned and adds aws s3 support out of the box using *Cloudcube*(https://elements.heroku.com/addons/cloudcube) and a running db using *Jaws DB Mysql*(https://elements.heroku.com/addons/jawsdb).

In less that a minute you should have wordpress up and running.

## Features

* Uses a single mysql connection and creates dynamic databases based on the requested url so that the same core can be reused and easily maintained.
* Uses s3 for file storage, since Heroku doesn't support file persistence.

## Plugins and functionalities

* STMP Settings: provided by worpress-heroku-hosting.
* Google analytics: provided by google-site-kit.
* DB Search and replace: provided by better-search-replace.
* Multilingual: [TODO {Polylang, WPML}]. + Translate support.
* Contact forms: [TODO {Contact form 7}].
* Seo: [TODO {Yoast}].
* E-commerce: [TODO {Woocommerce}] + (Stripe, paypal support).
* Livechat: [TODO].
* Newsletter: [TODO].
* Backup: [TODO].
* Pagebuilder (drag & drop): [TODO: {BeaverBuilder, Elemetor}].
* Social network autopublish: [TODO].

> Plugins should be compatible between each other. For example multilingual support should
expand across all different plugins.

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
