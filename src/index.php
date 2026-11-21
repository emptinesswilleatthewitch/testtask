<?php 

require_once 'include.php';
require_once 'src/read.php';
require_once 'src/create.php';
require_once 'src/delete.php';
require_once 'src/update.php';

$method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_SCHEME);     

$path = $uri["path"];
$query = $uri["query"] ?: '';

$raw_payload = file_get_contents("php://input");
$payload = json_decode($raw_payload);

if ($payload == '') $payload = [''];

try {
    switch ($method) {
        case "GET":
            try {
                $result = read($uri, $query);
                echo json_encode(["data" => $result]);
            } catch (InvalidArguemntException $e) {
                echo json_encode(["error" => $e-getMessage()]);
                http_response_code(400);
            } catch (Exception $e) {
                echo json_encode(["error" => $e-getMessage()]);
                http_response_code(500);
            }
            break;
        case "POST": => "create", 
        "PATCH" => "update", 
        "DELETE" => 'delete', 
        default => fn() => { throw new Exception ("unsupported http method\n"); },
}
?>
