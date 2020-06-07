[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://badges.mit-license.org)
[![Build Status](https://travis-ci.org/Codexshaper/php-oauth2.svg?branch=master)](https://travis-ci.org/Codexshaper/php-oauth2)
[![StyleCI](https://github.styleci.io/repos/270232789/shield?branch=master)](https://github.styleci.io/repos/270232789)
[![Quality Score](https://img.shields.io/scrutinizer/g/Codexshaper/php-oauth2.svg?style=flat-square)](https://scrutinizer-ci.com/g/Codexshaper/php-oauth2)
[![Downloads](https://poser.pugx.org/Codexshaper/php-oauth2/d/total.svg)](https://packagist.org/packages/Codexshaper/php-oauth2)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/Codexshaper/php-oauth2.svg?style=flat-square)](https://packagist.org/packages/Codexshaper/php-oauth2)

# Description
OAuth2 authentication for PHP

## Install

```
composer require codexshaper/php-oauth2
```

### Client Credentials Grant

#### The client sends a POST request with following body parameters to the authorization server:

    `grant_type` with the value `client_credentials`
    `client_id` with the client’s ID
    `client_secret` with the client’s secret
    `scope` with a space-delimited list of requested scope permissions.

#### The authorization server will respond with a JSON object containing the following properties:

    `token_type` with the value Bearer
    `expires_in` with an integer representing the TTL of the access token
    `access_token` a JWT signed with the authorization server’s private key

### Password Grant


#### The client then sends a POST request with following body parameters to the authorization server:

    `grant_type` with the value `password`
    `client_id` with the the client’s ID
    `client_secret` with the client’s secret
    `scope` with a space-delimited list of requested scope permissions.
    `username` with the user’s username
    `password` with the user’s password

#### The authorization server will respond with a JSON object containing the following properties:

    `token_type` with the value Bearer
    `expires_in` with an integer representing the TTL of the access token
    `access_token` a JWT signed with the authorization server’s private key
    `refresh_token` an encrypted payload that can be used to refresh the access token when it expires.


