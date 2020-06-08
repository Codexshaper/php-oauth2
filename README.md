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

## Setup Database

```
use Illuminate\Support\Facades\Facade;
use Illuminate\Container\Container;
use CodexShaper\Database\Database;

Facade::setFacadeApplication(new Container);

$db = new Database([
	"driver" 		=> "mysql",
	"host" 			=> 'localhost',
	"database" 		=> 'php-oauth2',
	"username" 		=> 'root',
	"password" 		=> '',
	"prefix"   		=> '',
	"charset"   	=> 'utf8mb4',
	"collation"   	=> 'utf8mb4_unicode_ci',
]);

$db->run();
```

More details about database follow this link https://github.com/Codexshaper/php-database

#### Migrate tables

```
use CodexShaper\OAuth2\Server\Manager;

Manager::migrate();
```

#### Rollback tables

```
use CodexShaper\OAuth2\Server\Manager;

Manager::rollback();
```

#### Refresh tables

```
use CodexShaper\OAuth2\Server\Manager;

Manager::refresh();
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

#### Get Access Token

```
use CodexShaper\OAuth2\Server\Http\Controllers\AccessTokenController;
use League\OAuth2\Server\Exception\OAuthServerException;

try {
	
	$controller = new AccessTokenController;
	$response = $controller->issueAccessToken();
    
} catch (OAuthServerException $exception) {

    return $exception->generateHttpResponse($response);
    
}
```

#### The client sends a POST request with following body parameters to the authorization server:

    grant_type with the value refresh_token
    refresh_token with the refresh token
    client_id with the the client’s ID
    client_secret with the client’s secret
    scope with a space-delimited list of requested scope permissions. This is optional; if not sent the original scopes will be used, otherwise you can request a reduced set of scopes.

#### The authorization server will respond with a JSON object containing the following properties:

    token_type with the value Bearer
    expires_in with an integer representing the TTL of the access token
    access_token a new JWT signed with the authorization server’s private key
    refresh_token an encrypted payload that can be used to refresh the access token when it expires


#### Get Refresh Access Token

```
use CodexShaper\OAuth2\Server\Http\Controllers\RefreshTokenController;
use League\OAuth2\Server\Exception\OAuthServerException;

try {
	
	$controller = new RefreshTokenController;
	$response = $controller->issueAccessToken();
    
} catch (OAuthServerException $exception) {

    return $exception->generateHttpResponse($response);
    
}
```

### Part One

#### The client will redirect the user to the authorization server with the following parameters in the query string:

    response_type with the value code
    client_id with the client identifier
    redirect_uri with the client redirect URI. This parameter is optional, but if not send the user will be redirected to a pre-registered redirect URI.
    scope a space delimited list of scopes
    state with a CSRF token. This parameter is optional but highly recommended. You should store the value of the CSRF token in the user’s session to be validated when they return.

All of these parameters will be validated by the authorization server.

The user will then be asked to login to the authorization server and approve the client.

If the user approves the client they will be redirected from the authorization server to the client’s redirect URI with the following parameters in the query string:

    code with the authorization code
    state with the state parameter sent in the original request. You should compare this value with the value stored in the user’s session to ensure the authorization code obtained is in response to requests made by this client rather than another client application.

```
use CodexShaper\OAuth2\Server\Http\Controllers\RefreshTokenController;
use CodexShaper\OAuth2\Server\Models\User;
use League\OAuth2\Server\Exception\OAuthServerException;

// Step 1
try {
	
	$user = User::find(1);
	$authorize = new AuthorizationController;
	$authRequest = $authorize->authorize($user);
    
} catch (OAuthServerException $exception) {

    return $exception->generateHttpResponse($response);
    
}

// Redirect to callback if skip authorization is true

$client = new Client;

if($client->isSkipsAuthorization()) {

	$headers = $authRequest->getHeaders();
	$locations = $headers['Location'];

	foreach ($locations as $location) {
		header('Location: ' . $location);
	}
	die();
}

// If skip authorization is false then display html button to choose approve or deny. First set authRequest in your session to retrieve later for access token

session_start();

	$_SESSION['authRequest'] = $authRequest;

$html = <<<HTML
	<!DOCTYPE html>
	<html>
		<head>
			<title></title>
		</head>
		<body>
			<form>
				<a href="http://site.com/approve.php?action=approve">Approve</a>
				<a href="http://site.com/approve.php?action=deny">Deny</a>
			</form>
		</body>
	</html>
HTML;

echo $html;

// approve.php
// You need to setup database before call any request

if(isset($_SESSION['authRequest']) && $_REQUEST['action'] === 'approve') {
	try {

		$user = User::find(1);

		$authorize = new AuthorizationController;
		$authRequest = $_SESSION['authRequest'];

		var_dump($authRequest);

		$response = $authorize->approve($authRequest, $user);

		$headers = $response->getHeaders();
		$locations = $headers['Location'];

		foreach ($locations as $location) {
			header('Location: ' . $location);
		}
		die();

		session_destroy();

	} catch(\Exception $ex) {

	}
}
```

### Part Two

#### The client will now send a POST request to the authorization server with the following parameters:

    grant_type with the value of authorization_code
    client_id with the client identifier
    client_secret with the client secret
    redirect_uri with the same redirect URI the user was redirect back to
    code with the authorization code from the query string

Note that you need to decode the code query string first. You can do that with urldecode($code).

The authorization server will respond with a JSON object containing the following properties:

    token_type with the value Bearer
    expires_in with an integer representing the TTL of the access token
    access_token a JWT signed with the authorization server’s private key
    refresh_token an encrypted payload that can be used to refresh the access token when it expires.

#### Callback

```
if (isset($_GET['code'])) {
	// call part 2. Here I used guzzle http request

	$code = urldecode($_GET['code']);
	$http = new GuzzleHttp\Client;

	$response = $http->post('http://site.com/oauth/access_token', [
	    'form_params' => [
	        'grant_type' => 'authorization_code',
	        'client_id' => 'CLIENT_ID',
	        'client_secret' => 'CLIENT_SECRET',
	        'code' => $code,
	    ],
	]);

	$data = json_decode((string) $response->getBody(), true);

	var_dump($data);
}
```