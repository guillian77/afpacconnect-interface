<?php

use Guillian\AfpaConnect\AfpaConnect;

require 'vendor/autoload.php';

session_start();

$publicKey = file_get_contents("tests/afpanier.key");

$ac = new AfpaConnect("http://localhost/AfpaConnect/", "afpanier", $publicKey);

$resp = $ac->post("register", [
    'username' => "123456789",
    'password' => "test"
]);

$resp = json_decode($resp);

echo "<pre>";
var_dump($resp);
echo "</pre>";