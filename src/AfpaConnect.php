<?php


namespace Guillian\AfpaConnect;


use Cache\Adapter\Filesystem\FilesystemCachePool;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Cache\InvalidArgumentException;

class AfpaConnect
{
    private Client $client;

    private string $hostname;

    private string $issuer;

    private string $publicKey;

    private ?string $jwt = null;

    private FilesystemCachePool $cache;

    public function __construct(string $hostname, string $issuer, string $publicKey)
    {
        /**
         * Configure cache system
         */
        $filesystemAdapter = new Local(dirname(__DIR__));
        $filesystem = new Filesystem($filesystemAdapter);
        $this->cache = new FilesystemCachePool($filesystem, "cache");

        /**
         * Initialize HTTP Client
         */
        $this->client = new Client();

        /**
         * Initialize general configuration
         */
        $this->hostname = trim($hostname, "/")."/api/";
        $this->issuer = $issuer;
        $this->publicKey = $publicKey;

        /**
         * Token handler
         */
        $this->tokenHandler();
    }

    /**
     * Handle JWT.
     *
     * Check if JWT is present in cache pool.
     * Check if JWT is expired.
     *
     * @throws InvalidArgumentException
     */
    private function tokenHandler()
    {
        $cachedJWT = $this->cache->getItem('jwt');

        if (!$cachedJWT->isHit()) { // Check JWT presence.
            $this->cacheJsonWebToken($cachedJWT);
        }

        if ($this->isTokenExpired($cachedJWT->get())) { // Check JWT expired.
            $this->cacheJsonWebToken($cachedJWT);
        }

        // Save JWT
        $this->jwt = $cachedJWT->get();
    }

    /**
     * Store JWT come from API into cache pool.
     *
     * @param $item
     */
    private function cacheJsonWebToken($item)
    {
        $jwt = $this->getJsonWenToken();

        $item
            ->set($jwt)
            ->expiresAfter($this->decodeJWT($jwt)->exp);

        $this->cache->save($item);
    }

    /**
     * Get JSON Web Token from AfpaConnect authentication API.
     */
    private function getJsonWenToken()
    {
        $respContent = $this->post(
            "auth",
            ['public_key' => $this->publicKey]
        );

        return json_decode($respContent)->content;
    }

    /**
     * Check if token is expired.
     *
     * @return bool
     */
    private function isTokenExpired($jwt):bool
    {
        try {
            $this->decodeJWT($jwt);
        } catch (\Exception $exception) {
            return true;
        }

        return false;
    }

    /**
     * Decode JWT.
     *
     * @param $jwt JWT to decode.
     * @return object|null
     */
    private function decodeJWT($jwt)
    {
        return JWT::decode($jwt, $this->publicKey, [
            'RS256',
            'HS256'
        ]);
    }

    /**
     * Send POST request to AfpaConnect.
     *
     * Request is also send with issuer and JWT.
     *
     * @param string $route
     * @param array $parameters
     *
     * @return string
     *
     * @throws GuzzleException
     */
    public function post(string $route, array $parameters = [])
    {
        $url = $this->hostname . $route;

        $form_params = array_merge(
            ['issuer' => $this->issuer],
            $parameters
        );

        $resp = $this->client->request('POST', $url, [
            "form_params" => $form_params,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwt
            ]
        ]);

        return $resp->getBody()->getContents();
    }

    /**
     * Send GET request to AfpaConnect.
     *
     * Request is also send with issuer and JWT.
     *
     * @param string $route
     * @param array $parameters
     *
     * @return string
     *
     * @throws GuzzleException
     */
    public function get(string $route, array $parameters = [])
    {
        $url = $this->hostname . $route;

        $parameters = array_merge(
            ['issuer' => $this->issuer],
            $parameters
        );

        $resp = $this->client->request('GET', $url, [
            "query" => $parameters,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwt
            ]
        ]);

        return $resp->getBody()->getContents();
    }
}
