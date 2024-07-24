<?php

/*HBS*/
// required headers
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Headers: Content-Type, Authorization');
// header("Access-Control-Allow-Credentials: true");
// header('Content-Type: application/json');
// $method = $_SERVER['REQUEST_METHOD'];
// if ($method == "OPTIONS") {
//     header('Access-Control-Allow-Origin: *');
//     header("Access-Control-Allow-Headers: Content-Type, Authorization");
//     header("HTTP/1.1 200 OK");
//     die();
// }
/*hbs*/

//// Forward Vercel requests to normal index.php
//require __DIR__ . '/../public/index.php';


require_once __DIR__ . '/../vendor/autoload.php';

$client = new \GuzzleHttp\Client();

$response = $client->request('GET', 'https://api.github.com/repos/guzzle/guzzle');

$result = json_decode($response->getBody(), true); 

dd($result);



$host = $_ENV['PG_HOST'];
$port = $_ENV['PG_PORT'];
$db = $_ENV['PG_DB'];
$user = $_ENV['PG_USER'];
$password = $_ENV['PG_PASSWORD'];
$endpoint = $_ENV['PG_ENDPOINT'];

$connection_string = "host=" . $host . " port=" . $port . " dbname=" . $db . " user=" . $user . " password=" . $password . " options='endpoint=" . $endpoint . "' sslmode=require";

$dbconn = pg_connect($connection_string);

if (!$dbconn) {
    die("Connection failed: " . pg_last_error());
}
echo "Connected successfully";