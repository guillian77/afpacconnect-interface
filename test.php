<?php

use Guillian\AfpaConnect\AfpaConnect;

require 'vendor/autoload.php';

/**
 * Debug.
 * @param $toDebug
 */
function debug($toDebug) {
    echo "<pre>";
    var_dump($toDebug);
    echo "</pre>";
}

$publicKey = file_get_contents("tests/afpanier.key");

$api = new AfpaConnect();
$api
    ->setHostname("http://localhost:8000")
    ->setIssuer("afpanier")
    ->setPublicKey($publicKey);

//debug($api->getHostname());
//debug($api->getIssuer());
//debug($api->getPublicKey());

/**
 * POST EXAMPLE
 */
//$resp = $api->post("register", [
//    'username' => "123456789",
//    'password' => "test"
//]);

//$resp = json_decode($resp);
//
//debug($resp);

/**
 * GET EXAMPLE
 */
//$resp = $api->get("user", [
//    'username' => "123456789"
//]);
//
//$resp = json_decode($resp);

//debug($resp);

/**
 * GET AUTH TEMPLATE EXAMPLE
 */
echo $api->getAuthTemplate([
    'verify' => "auth-verify",
    'login' => "auth-login",
    'register' => "auth-register"
]);



