# Stellar federation server

> This is still work in progress. Use at your own risk.

This server provides Stellar federation for your domain.

[Stellar](https://stellar.org) is a decentralized protocol for sending and receiving money in any pair of currencies.

The [federation protocol](https://wiki.stellar.org/Federation) allows payment systems to federate in the Stellar network via the creation of gateways. Each gateway is responsible for creation and managing user' accounts. Gateway users are provided a payment address similar to user@example.org while this address can be used to send and receive payments from any other user on the Stellar network. Main benefit for users is the ability to remember and easily tell others their address.

## Setting up federation

Whenever a Stellar client tries to make a payment to user@example.org it tries to request a file called `stellar.txt` from the following URLs, until one request is successful:

1. https://stellar.example.org/stellar.txt
2. https://example.org/stellar.txt
3. https://www.example.org/stellar.txt

The file `stellar.txt` tells the client how to reach the federation server. Once the client knows the URL of the federation server it asks the federation server for the users wallet address.

> Both the server serving the stellar.txt file as well as your federation server need a valid SSL certificate.

### stellar.txt

Make sure that the file `stellar.txt` is served in one of the locations mentioned above and don't forget to adjust the URL to the actual URL of your federation server.

```
[federation_url]
https://url-of-your-federation-server/directory
```

### Federation server

Next up you'll have to set up the federation server itself in the location that you've configured in the `stellar.txt` file. The easiest and quickest way to get this up and running is just cloning this repository, creating a new [Heroku](http://heroku.com) application and pushing it there. When using Heroku it's already taken care of the webserver config in the `Procfile` - if you use something else, you have to configure your webserver yourself:

#### Webserver config

Configure your webserver document root to `public/` and make sure that query strings don't get lost:

#### Apache

```
RewriteRule ^/(.*)$ /index.php [L]
```

#### Nginx

```
index index.php;

location / {
    try_files $uri /index.php?$args;
}
```

#### Configure user and domains

Don't forget to configure the domains, users and their wallet addresses you want this server to provide federation service for.

> @TODO: Currently this is hardcoded in `public/index.php`.

## Usage

If you did everything correctly you should now be able to query your federation server like this:

```
curl -i 'https://example.org?type=federation&user=user&domain=example.org'

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
