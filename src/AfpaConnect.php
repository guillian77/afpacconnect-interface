<?php


namespace Guillian\AfpaConnect;


use Cache\Adapter\Filesystem\FilesystemCachePool;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Cache\InvalidArgumentException;

/**
 * Class AfpaConnect
 * @package Guillian\AfpaConnect
 */
class AfpaConnect
{
    private Client $client;

    private string $hostname;

    private string $issuer;

    private string $publicKey;

    private ?string $jwt = null;

    private FilesystemCachePool $cache;

    private bool $verifyToken = true;

    public function __construct(string $hostname = null, string $issuer = null, string $publicKey = null)
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
        if (!is_null($hostname)) {
            $this->setHostname($hostname);
        }

        if (!is_null($issuer)) {
            $this->setIssuer($issuer);
        }

        if (!is_null($publicKey)) {
            $this->setPublicKey($publicKey);
        }
    }

    /**
     * Configure API hostname.
     *
     * @param string $hostname API hostname.
     *
     * @return $this
     */
    public function setHostname(string $hostname): self
    {
        $this->hostname = trim($hostname, "/")."/api/";

        return $this;
    }

    /**
     * Get API hostname
     *
     * @return string|null
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * Configure issuer. Who is sending request to API.
     *
     * @param string $issuer
     *
     * @return $this
     */
    public function setIssuer(string $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * Get the issuer.
     *
     * @return string|null
     */
    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    /**
     * Configure public key used to verify external API authenticity.
     *
     * @param string $publicKey External app public key. Delivered by the API owner.
     *
     * @return $this
     */
    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Get external app public key.
     *
     * @return string|null
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * Verify needed configuration.
     *
     * @throws Exception
     */
    private function checkConfiguration()
    {
        if (!isset($this->hostname)) {
            throw new Exception("API hostname is missing. Use setHostname() method.");
        }

        if (!isset($this->issuer)) {
            throw new Exception("Issuer is missing. Use setIssuer() method.");
        }

        if (!isset($this->publicKey)) {
            throw new Exception("Issuer is missing. Use setIssuer() method.");
        }
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
        // Prevent for infinite loop.
        // Because this method use post method to get JWT. And POST method also call this method itself.
        if (!$this->verifyToken) {
            return;
        }

        $this->checkConfiguration();

        $cachedJWT = $this->cache->getItem('jwt');

        if (!$cachedJWT->isHit()) { // Check JWT presence.
            $this->verifyToken = false;

            $this->cacheJsonWebToken($cachedJWT);

            $this->verifyToken = true;
        }

        if ($this->isTokenExpired($cachedJWT->get())) { // Check JWT expired.
            $this->verifyToken = false;

            $this->cacheJsonWebToken($cachedJWT);

            $this->verifyToken = true;

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
        } catch (Exception $exception) {
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
     * @throws InvalidArgumentException
     */
    public function post(string $route, array $parameters = [])
    {
        $this->tokenHandler();

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
     * @throws InvalidArgumentException
     */
    public function get(string $route, array $parameters = [])
    {
        $this->tokenHandler();

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
