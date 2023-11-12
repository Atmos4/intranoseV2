<?php
# Copyright (c) 2013-2023, OVH SAS.
# All rights reserved.
# this is a fork. If you read this OVH, your code is old and slow, please update.

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class OvhHttpClient
{
    /**
     * Url to communicate with Ovh API
     *
     * @var array
     */
    private $endpoints = [
        'ovh-eu' => 'https://eu.api.ovh.com/1.0',
        'ovh-ca' => 'https://ca.api.ovh.com/1.0',
        'ovh-us' => 'https://api.us.ovhcloud.com/1.0',
        'kimsufi-eu' => 'https://eu.api.kimsufi.com/1.0',
        'kimsufi-ca' => 'https://ca.api.kimsufi.com/1.0',
        'soyoustart-eu' => 'https://eu.api.soyoustart.com/1.0',
        'soyoustart-ca' => 'https://ca.api.soyoustart.com/1.0',
        'runabove-ca' => 'https://api.runabove.com/1.0',
    ];

    /**
     * Contain endpoint selected to choose API
     *
     * @var string
     */
    private ?string $endpoint;

    /**
     * Contain key of the current application
     *
     * @var string
     */
    private ?string $application_key;

    /**
     * Contain secret of the current application
     *
     * @var string
     */
    private ?string $application_secret;

    /**
     * Contain consumer key of the current application
     *
     * @var string
     */
    private ?string $consumer_key;

    /**
     * Contain delta between local timestamp and api server timestamp
     *
     * @var string
     */
    private ?string $time_delta;

    /**
     * Contain http client connection
     *
     * @var Client
     */
    private ?Client $http_client;

    /**
     * Construct a new wrapper instance
     *
     * @param string $application_key    key of your application.
     *                                   For OVH APIs, you can create a application's credentials on
     *                                   https://api.ovh.com/createApp/
     * @param string $application_secret secret of your application.
     * @param string $api_endpoint       name of api selected
     * @param string $consumer_key       If you have already a consumer key, this parameter prevent to do a
     *                                   new authentication
     * @param Client $http_client        instance of http client
     */
    public function __construct(
        $application_key,
        $application_secret,
        $api_endpoint,
        $consumer_key = null,
        Client $http_client = null
    ) {
        if (!isset($api_endpoint)) {
            throw new Exception("Endpoint parameter is empty");
        }

        if (preg_match('/^https?:\/\/..*/', $api_endpoint)) {
            $this->endpoint = $api_endpoint;
        } else {
            if (!array_key_exists($api_endpoint, $this->endpoints)) {
                throw new Exception("Unknown provided endpoint");
            }

            $this->endpoint = $this->endpoints[$api_endpoint];
        }

        if (!isset($http_client)) {
            $http_client = new Client([
                'timeout' => 30,
                'connect_timeout' => 5,
            ]);
        }

        $this->application_key = $application_key;
        $this->application_secret = $application_secret;
        $this->http_client = $http_client;
        $this->consumer_key = $consumer_key;
    }

    /**
     * Calculate time delta between local machine and API's server
     *
     * @throws ClientException if http request is an error
     */
    private function calculateTimeDelta()
    {
        if (!isset($this->time_delta)) {
            $response = $this->rawCall(
                'GET',
                "/auth/time",
                null,
                false
            );
            $serverTimestamp = (int) (string) $response->getBody();
            $this->time_delta = $serverTimestamp - (int) \time();
        }

        return $this->time_delta;
    }

    /**
     * getTarget returns the URL to target given an endpoint and a path.
     * If the path starts with `/v1` or `/v2`, then remove the trailing `/1.0` from the endpoint.
     *
     * @param string path to use prefix from
     * @return string
     */
    protected function getTarget($path): string
    {
        $endpoint = $this->endpoint;
        if (
            substr($endpoint, -4) == '/1.0' && (
                substr($path, 0, 3) == '/v1' ||
                substr($path, 0, 3) == '/v2')
        ) {
            $endpoint = substr($endpoint, 0, strlen($endpoint) - 4);
        }
        return $endpoint . $path;
    }

    protected function buildRequestAndHeaders($method, $path, $content = null, $is_authenticated = true, $headers = null): array
    {
        if ($is_authenticated) {
            if (!isset($this->application_key)) {
                throw new Exception("Application key parameter is empty");
            }

            if (!isset($this->application_secret)) {
                throw new Exception("Application secret parameter is empty");
            }
        }

        $url = $this->getTarget($path);
        $request = new Request($method, $url);
        if (isset($content) && $method === 'GET') {
            $query_string = $request->getUri()->getQuery();

            $query = [];
            if (!empty($query_string)) {
                $queries = explode('&', $query_string);
                foreach ($queries as $element) {
                    $key_value_query = explode('=', $element, 2);
                    $query[$key_value_query[0]] = $key_value_query[1];
                }
            }

            $query = array_merge($query, (array) $content);

            // rewrite query args to properly dump true/false parameters
            foreach ($query as $key => $value) {
                if ($value === false) {
                    $query[$key] = "false";
                } elseif ($value === true) {
                    $query[$key] = "true";
                }
            }

            $query = \GuzzleHttp\Psr7\Query::build($query);

            $url = $request->getUri()->withQuery($query);
            $request = $request->withUri($url);
            $body = "";
        } elseif (isset($content)) {
            $body = json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

            $request->getBody()->write($body);
        } else {
            $body = "";
        }
        if (!is_array($headers)) {
            $headers = [];
        }
        $headers['Content-Type'] = 'application/json; charset=utf-8';

        $headers['X-Ovh-Application'] = $this->application_key ?? '';
        if ($is_authenticated) {
            if (!isset($this->time_delta)) {
                $this->calculateTimeDelta();
            }
            $now = time() + $this->time_delta;

            $headers['X-Ovh-Timestamp'] = $now;

            if (isset($this->consumer_key)) {
                $toSign = $this->application_secret . '+' . $this->consumer_key . '+' . $method
                    . '+' . $url . '+' . $body . '+' . $now;
                $signature = '$1$' . sha1($toSign);
                $headers['X-Ovh-Consumer'] = $this->consumer_key;
                $headers['X-Ovh-Signature'] = $signature;
            }
        }

        return [$request, $headers];
    }

    protected function rawCall($method, $path, $content = null, $is_authenticated = true, $headers = null): ResponseInterface
    {

        [$request, $headers] = $this->buildRequestAndHeaders($method, $path, $content, $is_authenticated, $headers);

        /** @var Response $response */
        return $this->http_client->send($request, ['headers' => $headers]);
    }

    protected function rawCallAsync($method, $path, $content = null, $is_authenticated = true, $headers = null): PromiseInterface
    {
        [$request, $headers] = $this->buildRequestAndHeaders($method, $path, $content, $is_authenticated, $headers);
        return $this->http_client->sendAsync($request, ['headers' => $headers]);
    }

    /**
     * Decode a Response object body to an Array
     *
     * @param Response $response
     *
     * @throws \JsonException
     */
    private function decodeResponse(Response $response)
    {
        if ($response->getStatusCode() === 204 || $response->getBody()->getSize() === 0) {
            return null;
        }
        return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Wrap call to Ovh APIs for POST requests
     *
     * @param string $path    path ask inside api
     * @param array  $content content to send inside body of request
     * @param array  headers  custom HTTP headers to add on the request
     * @param bool   is_authenticated   if the request need to be authenticated
     *
     * @throws ClientException if http request is an error
     */
    public function send($method, $path, $content = null, $headers = null, $is_authenticated = true)
    {
        return $this->decodeResponse(
            $this->rawCall($method, $path, $content, $is_authenticated, $headers)
        );
    }

    public function sendAsync($method, $path, $content = null, $headers = null, $is_authenticated = true)
    {
        return $this->rawCallAsync($method, $path, $content, $is_authenticated, $headers);
    }

    /**
     * Get the current consumer key
     */
    public function getConsumerKey(): ?string
    {
        return $this->consumer_key;
    }

    /**
     * Return instance of http client
     */
    public function getHttpClient(): ?Client
    {
        return $this->http_client;
    }
}