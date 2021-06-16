<?php


namespace Guillian\AfpaConnect;


use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class AfpaConnect
{
    private Client $client;

    private string $hostname;

    private string $issuer;

    private string $publicKey;

    private ?string $jwt = null;

    public function __construct($hostname, $issuer, $publicKey)
    {
        $this->client = new Client();

        $this->hostname = trim($hostname, "/")."/api/";

        $this->issuer = $issuer;

        $this->publicKey = $publicKey;

        $this->tokenHandler();
    }

    private function tokenHandler()
    {
        if (empty($_SESSION['jsonWebToken'])) {
            echo "------ TOKEN NOT CACHED ------\n";
            $_SESSION['jsonWebToken'] = $this->getJsonWenToken();
        }

        if ($this->isTokenExpired($_SESSION['jsonWebToken'])) {
            echo "------ TOKEN EXPIRED ------\n";
            $_SESSION['jsonWebToken'] = $this->getJsonWenToken();
        }

        $this->jwt = $_SESSION['jsonWebToken'];
    }

    /**
     * Get JSON Web Token from AfpaConnect authentication API.
     */
    private function getJsonWenToken()
    {
        echo "------ GET TOKEN ------\n";
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
        $jwt = $this->decodeJWT($jwt);

        if ($jwt->exp <= time()) {
            return true;
        }

        return false;
    }

    private function decodeJWT($jwt)
    {
        return JWT::decode($jwt, $this->publicKey, [
            'RS256',
            'HS256'
        ]);
    }

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

    public function get(string $route, array $parameters = [])
    {
        $url = $this->hostname . $route;

        $parameters = array_merge(
            ['issuer' => $this->issuer],
            $parameters
        );

        $resp = $this->client->request('GET', $url, [
            "form_params" => $parameters
        ]);

        return $resp->getBody()->getContents();
    }
}
