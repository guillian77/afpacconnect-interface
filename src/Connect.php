<?php


namespace Guillian\AfpaConnect;


use DateInterval;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

class Connect
{
    private Client $client;

    private FilesystemAdapter $cache;

    private string $hostname;

    private string $issuer;

    private string $publicKey;

    public function __construct($hostname, $issuer, $publicKey)
    {
        $this->client = new Client();
        $this->cache = new FilesystemAdapter();
        $this->hostname = trim($hostname, "/")."/api/";
        $this->issuer = $issuer;
        $this->publicKey = $publicKey;
    }

    public function tokenHandler()
    {
        /** @var CacheItem $token */
        $token = $this->cache->getItem('jwt');

        if (!$token->isHit()) {
            $token
                ->set('test')
                ->expiresAfter(DateInterval::createFromDateString('6 hours'));
            ;
        }

        $this->client->request('POST', $this->hostname."/auth", [
            "form_params" => [
                'issuer' => $this->issuer,
                'public_key' => $this->publicKey
            ]
        ]);

        var_dump($token);
    }
}
