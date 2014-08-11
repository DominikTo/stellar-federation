# Stellar federation server

> Work in progress. Use at your own risk.

This server provides Stellar federation for your domain.

[Stellar](https://stellar.org) is a decentralized protocol for sending and receiving money in any pair of currencies.

The Stellar [federation protocol](https://wiki.stellar.org/Federation) makes it possible to set up federated addresses for your domain. That means, that if you control the domain example.org, you could receive payments under e.g. user@example.org. Check out this [blog post](http://tobschall.de/2014/08/08/federated-stellar-addresses/) if you want to learn more about Stellar federation.

## Setting up federation

Whenever a Stellar client tries to make a payment to user@example.org it tries to request a file called `stellar.txt` from three different URLs on your server (more on `stellar.txt` below). This file tells the client how to reach the federation server. Once the client knows the URL of the federation server it asks the federation server for the users wallet address.

> Both the server serving the stellar.txt file as well as your federation server need a valid SSL certificate.

### Federation server

The easiest and quickest way to get a federation server up and running is to init a new git repository, require `dominik/stellar-federation` as a dependency via composer, create a new [Heroku](http://heroku.com) application and push it there. When using Heroku it's already taken care of the webserver config in the `Procfile` - if you use something else, you'll have to configure your webserver yourself. For Heroku you'll need their command line tools set up on your machine (On a Mac simply install via `brew install heroku-toolbelt`). If you don't have composer on your machine, the instructions can be found on the [composer website](https://getcomposer.org/doc/00-intro.md#globally).

#### Now it's time to set up your project:

* Create a new git repository somewhere on your machine with `git init`
* Run `composer init` to initialize your new project
  * You should set `minimum-stability` to `dev`
  * When asked for dependencies enter `dominik/stellar-federation` with `dev-master` as the version constraint
  * You'll also need the mbstring extension, so make sure to require `ext-mbstring` with `*` as the version constraint as well
* Then run `composer install` to pull in the dependencies

#### Next up you'll have to configure your federation server:

* Create the directory `public/` in your project.
  * Copy the example server into the directory `cp vendor/dominik/stellar-federation/example/server.php public/index.php`
  * Configure `public/index.php` as needed with your domain and users you want to provide federation for
* Copy the Heroku Procfile into the root directory of your project `cp vendor/dominik/stellar-federation/example/Procfile .`

#### Deploy to Heroku (or elsewhere):

* Add everything to git with `git add .`
* Commit your changes `git commit -m "Initial commit"`
* Create a new Heroku application `heroku apps:create`
* Push to Heroku `git push heroku master`

> Whenever you want to update, just run `composer update` in your project, commit the updated composer.lock and deploy again.

### Configure stellar.txt

The last step is to bring the `stellar.txt` file in place to tell Stellar clients where to find your federation server in one of these locations:

* https://stellar.example.org/stellar.txt
* https://example.org/stellar.txt
* https://www.example.org/stellar.txt

Don't forget to adjust the URL in `stellar.txt` to the actual location of your federation server.

```
[federation_url]
https://example.herokuapp.com
```

## Usage

If you did everything correctly you should now be able to query your federation server like this:

```
curl -i 'https://example.herokuapp.com?type=federation&user=user&domain=example.org'

HTTP/1.1 200 OK
Connection: keep-alive
Content-Type: application/json
Access-Control-Allow-Origin: *

{
    "result": "success",
    "federation_json": {
        "type": "federation_record",
        "domain": "example.org",
        "user": "user",
        "destination_address": "gDnu3fdGNNAuUy84DmbfyxwELjfu8kpmHg"
    }
}
```

When someone now tries to make a payment to `user@example.org`, the client will first try to find your `stellar.txt` to figure out where the federation server for your domain is running and then ask your federation server for the wallet address of `user@example.org`.
