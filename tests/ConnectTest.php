<?php


namespace Guillian\AfpaConnect\tests;


use Guillian\AfpaConnect\AfpaConnect;
use PHPUnit\Framework\TestCase;

class ConnectTest extends TestCase
{
    public function test()
    {
        $publicKey = file_get_contents("tests/afpanier.key");

        $ac = new AfpaConnect("http://localhost/AfpaConnect/", "afpanier", $publicKey);

        $resp = $ac->post("register", [
            'username' => "123456789",
            'password' => "test"
        ]);

        $resp = json_decode($resp);

        $this->assertEquals("302", $resp->code);
    }
}