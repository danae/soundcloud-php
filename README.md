**soundcloud-php** is a SoundCloud® API wrapper for PHP 7.2 or higher.

## Installation

The SoundCloud library is best installed using Composer:

```shell
$ composer require danae/soundcloud-php
```

## Authorization

To use the library, you must create an instance of the `Danae\Soundcloud\Soundcloud` class. The only constructor argument is an associative array of options, which should contain at least the `client_id` key.

```php
$sc = new Soundcloud(['client_id' => $client_id]);
```

This client has support foor all authorization flows provided by the SoundCloud API and will attempt to automatically authenticate the application based on the provided options. The following sections describe the available authorization flows.

If you don't want the client to authorize itself upon constructing, you can always authorize the client later using the following functions:

```php
$sc->authorizeWithCode($code);
$sc->authorizeWithRefreshToken($refresh_token);
$sc->authorizeWithCredentials($username, $password);
```

### Authorize using an authorization code

Provide at least the `client_id` and `redirect_uri` keys in the constructor. The `scope` key is optional.

```php
$sc = new Soundcloud([
  'client_id' => $client_id,
  'redirect_uri' => $redirect_uri,
  'scope' => $scope
]);
```

This authorization flow stores the authorization URL in the client instance, which can be used to redirect the user to the SoundCloud connect screen.

```php
echo $sc->authorize_url;
```

When you obtained an authorization code from the connect screen, you can exchange this for an acces token. Upon succesful authorization, the authorization token can be obtained via the client instance.

```php
$sc->authorizeWithCode($code);
echo $sc->authorization_token;
```

### Authorize using a refresh token

Provide at least the `client_id`, `client_secret` and `refresh_token` keys in the constructor

```php
$sc = new Soundcloud([
  'client_id' => $client_id,
  'client_secret' => $client_secret,
  'refresh_token' => $refresh_token
]);
```

Upon succesful authorization, the authorization token can be obtained via the client instance.

```php
echo $sc->authorization_token;
```

### Authorize using credentials

Provide at least the `client_id`, `client_secret`, `username` and `password` keys n the constructor. The `scope` key is optional and defaults to `'not-expiring'`.

```php
$sc = new Soundcloud([
  'client_id' => $client_id,
  'client_secret' => $client_secret,
  'username' => $username,
  'password' => $password,
  'scope' => $scope
]);
```

Upon succesful authorization, the authorization token can be obtained via the client instance.

```php
echo $sc->authorization_token;
```

Note that this authorization method is not recommended by [SoundCloud](https://developers.soundcloud.com/docs/api/guide#authentication) and is prohibited is using other user's accounts.


## Usage

When the client is instantiated, you can always access public endpoints of the SoundCloud API. If you succesfully authorized the client, you can use that to access personal resources such as `/me ` or private sets.

The following functions make a request to the SoundCloud api and return a `stdClass` object created by `json_decode`ing the response body. All request functions throw a `Runtimexception` with the status code if the request failed.

```php
$soundcloud->get($uri, array $query = []);
$soundcloud->post($uri, array $body = [], array $query = []);
$soundcloud->put($uri, array $body[], array $query = []);
$soundcloud->delete($uri, array $query = []);
$soundcloud->resolve($url, array $query = []);
$soundcloud->oembed($url, array $query = []);
```

For more information on the API itself, please refer to the SoundCloud API [documentation](https://developers.soundcloud.com/docs/api/reference).
