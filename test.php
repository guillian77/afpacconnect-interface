<?php

use Guillian\AfpaConnect\AfpaConnect;

require 'vendor/autoload.php';

/**
 * Debug.
 * @param $toDebug
 */
function dd($toDebug) {
    echo "<pre>";
    var_dump($toDebug);
    echo "</pre>";
}

session_start();

$publicKey = file_get_contents("tests/afpanier.key");

$ac = new AfpaConnect("http://localhost/AfpaConnect/", "afpanier", $publicKey);

/**
 * POST EXAMPLE
 */
$resp = $ac->post("register", [
    'username' => "123456789",
    'password' => "test"
]);

$resp = json_decode($resp);

dd($resp);

/**
 * GET EXAMPLE
 */
$resp = $ac->get("user", [
    'username' => "123456789"
]);

$resp = json_decode($resp);

dd($resp);
