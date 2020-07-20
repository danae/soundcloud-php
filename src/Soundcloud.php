<?php
namespace Danae\Soundcloud;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

class Soundcloud
{
  // SoundCloud API location
  const URI = 'https://api.soundcloud.com/';
  const OEMBED_URI = 'https://soundcloud.com/oembed';
  const AUTHORIZE_URI = 'https://soundcloud.com/connect';

  // Reference to the provided options
  public $client_id;
  public $client_secret;
  public $redirect_uri;
  public $access_token;
  public $scope;

  // Reference to the GuzzleHttp client
  private $client;


  // Constructor
  public function __construct(array $options = [])
  {
    // Set the provided options
    if (!array_key_exists('client_id', $options))
      throw new InvalidArgumentException('At least a client_id must be provided');

    $this->client_id = $options['client_id'];
    $this->client_secret = $options['client_secret'] ?? null;
    $this->redirect_uri = $options['redirect_uri'] ?? null;
    $this->access_token = $options['access_token'] ?? null;
    $this->scope = $options['scope'] ?? 'non-expiring';

    // Initilalize the GuzzleHttp client
    $this->client = new Client(['base_uri' => self::URI, 'verify' => false, 'http_errors' => false]);

    // Authorize based on the provided options
    if (self::optionsPresent($options, ['client_id', 'redirect_uri']))
      $this->connect();
    if (self::optionsPresent($options, ['client_id', 'client_secret', 'refresh_token']))
      $this->authorizeWithRefreshToken($options['refresh_token']);
    else if (self::optionsPresent($options, ['client_id', 'client_secret', 'username', 'password']))
      $this->authorizeWithCredentials($options['username'], $options['password']);
  }

  // Return the authorization URL
  public function connect(): string
  {
    $options = [
      'response_type' => 'code',
      'client_id' => $this->client_id,
      'redirect_uri' => $this->redirect_uri,
      'scope' => $this->scope
    ];

    $this->authorize_url = AUTHORIZE_URI . http_build_query($options);
    return $this->authorize_url;
  }

  // Obtain an access token given an authorization code
  public function authorizeWithCode($code): void
  {
    $response_json = $this->post('/oauth2/token', [
      'grant_type' => 'authorization_code',
      'client_id' => $this->clientId,
      'client_secret' => $this->client_secret,
      'redirect_uri' => $this->redirectUri,
      'code' => $code
    ]);

    $this->access_token = $response_json->access_token;
  }

  // Obtain an access token given a refresh token
  public function authorizeWithRefreshToken($refresh_token): void
  {
    $response_json = $this->post('/oauth2/token', [
      'grant_type' => 'refresh_token',
      'client_id' => $this->client_id,
      'client_secret' => $this->client_secret,
      'refresh_token' => $refresh_token
    ]);

    $this->access_token = $response_json->access_token;
  }

  // Obtain an access token given a username and password
  public function authorizeWithCredentials(string $username, string $password): void
  {
    $response_json = $this->post('/oauth2/token', [
      'grant_type' => 'password',
      'client_id' => $this->client_id,
      'client_secret' => $this->client_secret,
      'scope' => $this->scope,
      'username' => $username,
      'password' => $password
    ]);

    $this->access_token = $response_json->access_token;
  }

  // Send a request
  private function request(string $method, string $uri, array $query = [], array $json = [])
  {
    try
    {
      // Prepare the query
      $query['client_id'] = $this->client_id;
      if ($this->access_token)
        $query['oauth_token'] = $this->access_token;

      // Send the request
      if ($method == 'POST' || $method == 'PUT')
        $response = $this->client->request($method, $uri, ['query' => $query, 'json' => $json]);
      else
        $response = $this->client->request($method, $uri, ['query' => $query]);

      // Check if authorization is needed
      if ($response->getStatusCode() == 401)
        throw new RuntimeException("You must authorize your application first");

      // Check if the request succeeded
      if (!preg_match('/[2-3][0-9]{2}/', $response->getStatusCode()))
        throw new RuntimeException("The request \"{$method} {$uri}" . (!empty($query) ? "?" . http_build_query($query) : "") . "\" returned with HTTP status code {$response->getStatusCode()}: {$response->getBody()}");

      // Otherwise handle the response
      if (strpos($response->getHeaderLine('Content-Type'),'application/json') === 0)
        return json_decode($response->getBody());
      else
        return $response->getBody();
    }
    catch (GuzzleException $ex)
    {
      throw new RuntimeException($ex->getMessage(), $ex->getCode(), $ex);
    }
  }

  // Sends a GET request
  public function get(string $uri, array $query = [])
  {
    return $this->request('GET', $uri, $query);
  }

  // Sends a POST request
  public function post(string $uri, array $body = [], array $query = [])
  {
    return $this->request('POST', $uri, $query, $body);
  }

  // Sends a PUT request
  public function put(string $uri, array $body = [], array $query = [])
  {
    return $this->request('PUT', $uri, $query, $body);
  }

  // Sends a DELETE request
  public function delete(string $uri, array $query = [])
  {
    return $this->request('DELETE', $uri, $query);
  }

  // Sends a resolve request
  public function resolve(string $url)
  {
    return $this->request('GET', '/resolve', ['url' => $url]);
  }

  // Sends a oembed request
  public function oembed(string $url, array $query = [])
  {
    return $this->get(self::OEMBED_URI, array_merge(['url' => $url, 'format' => 'json'], $query));
  }


  // Return if the options specified by the parameters are present
  private static function optionsPresent(array $options, array $keys): bool
  {
    return array_reduce($keys, function($result, $key) use ($options) {
      return $result && array_key_exists($key, $options);
    }, true);
  }
}
