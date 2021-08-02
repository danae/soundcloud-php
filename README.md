# soundcloud-php

**soundcloud-php** is a SoundCloud® API wrapper for PHP 7.4 or higher.

## Installation

The SoundCloud library is best installed using Composer:

```shell
$ composer require danae/soundcloud-php
```

Alternatively include the following line in the require block in composer.json:

```json
"require": {
  "danae/soundcloud-php": "^3.0"
}
```

## Usage

The following is a usage example of the library:

```php
require("vendor/autoload.php");

// Create the client
$soundcloud = new Danae\Soundcloud\Soundcloud([
  'client_id' => '<client id>',
  'client_secret' => '<client secret>',
  'redirect_url' => '<redirect url>'
]);

// Authorize the client with an authorization code
$soundcloud->authorizeWithCode('<authorization code>');

// Get the tracks of the authenticated user
$tracks = $soundcloud->get('/me/tracks');
```

To use the library, you must create an instance of the `Danae\SoundCloud\Soundcoud` class and provide your client_id and client_secret to the constructor:

```php
$soundcloud = new Danae\Soundcloud\Soundcloud([
  'client_id' => '<client id>',
  'client_secret' => '<client secret>'
]);
```


### Authorization

To use the client you must authenticate it first. The client has support for the `auth_code`, `client_credentials` and `refresh_token` grants provided by the SoundCloud API. You can use one of the following methods to authenticate the client:

```php
// Using an authorization code
$soundcloud->authorizeWithCode('<authorization code>');

// Using client credentials provided via the constructor
$soundcloud->authorizeWithClientCredentials();

// Using a refresh token
$soundcloud->authorizeWithRefreshToken('<refresh token>');
```

Upon succesful authorization, the access token will be automatically added to requests to the client, but can also be obtained via the client instance.

```php
$accessToken = $soundcloud->accessToken;
```

#### Authorize using an authorization code

Provide at least the `client_id`, `client_secret` and `redirect_uri` keys to the constructor of the client. Then call the `authorizeWithCode` method.

```php
$soundcloud = new Danae\Soundcloud\Soundcloud([
  'client_id' => '<client id>',
  'client_secret' => '<client secret>',
  'redirect_url' => '<redirect url>'
]);

$soundcloud->authorizeWithCode('<authorization code>');
```

#### Authorize using client credentials

Provide at least the `client_id` and `client_secret` keys to the constructor of the client. Then call the `authorizeWithClientCredentials` method.

```php
$soundcloud = new Danae\Soundcloud\Soundcloud([
  'client_id' => '<client id>',
  'client_secret' => '<client secret>'
]);

$soundcloud->authorizeWithClientCredentials();
```

#### Authorize using a refresh token

```php
$soundcloud = new Danae\Soundcloud\Soundcloud([
  'client_id' => '<client id>',
  'client_secret' => '<client secret>',
  'redirect_url' => '<redirect url>'
]);

$soundcloud->authorizeWithRefreshToken('<refresh_token>');
```

### Making requests

When the client is authorized, you can use the following methods to make requests to the SoundCloud API and receive a `stdClass` object created by `json_decode`ing the response body. All request functions throw a `Runtimexception` with the status code if the request failed.

```php
// Send a request to one of the API endpoints
$soundcloud->get($uri, array $query = []);
$soundcloud->post($uri, array $body = [], array $query = []);
$soundcloud->put($uri, array $body[], array $query = []);
$soundcloud->delete($uri, array $query = []);

// Resolve a SoundCloud URL to an API endpoint
$soundcloud->resolve($url, array $query = []);

// Send an oembed request to the API
$soundcloud->oembed($url, array $query = []);
```

For more information on the API itself, please refer to the [SoundCloud API explorer](https://developers.soundcloud.com/docs/api/explorer/open-api) or the [accompanying guide](https://developers.soundcloud.com/docs/api/guide).
