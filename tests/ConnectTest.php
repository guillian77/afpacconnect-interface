<?php


namespace guillian\afpaconnect\tests;


use Guillian\AfpaConnect\Connect;
use PHPUnit\Framework\TestCase;

class ConnectTest extends TestCase
{
    public function test()
    {
        $connect = new Connect();

        $connect->tokenHandler();
    }
}