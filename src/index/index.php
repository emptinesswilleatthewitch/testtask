<?php 

require_once '../../include.php';
require_once '../api/read.php';
require_once '../api/create.php';
require_once '../api/delete.php';
require_once '../api/update.php';

$method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER["REQUEST_URI"]);     

$path = $uri["path"];
$query = $uri["query"] ?: '';

$raw_payload = file_get_contents("php://input");
$payload = json_decode($raw_payload, true);

if ($payload == '') $payload = [''];
header('Content-Type: application/json');

switch ($method) {
    case "GET":
        try {
            $result = read($path, $query);
            echo json_encode(["data" => $result, "success" => true]);
            http_response_code(200);
        } catch (InvalidArgumentException $e) {
            echo json_encode(["error" => $e->getMessage(), "success" => false]);
            http_response_code(400);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage(), "success" => false]);
            http_response_code(500);
        }
        break;
    case "POST": 
        try {
            $result = create($path, $payload);
            echo json_encode(["success" => true]);
            http_response_code(201);
        } catch (InvalidArgumentException $e) {
            echo json_encode(["error" => $e->getMessage(), "success" => false]);
            http_response_code(400);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage(), "success" => false]);
            http_response_code(500);
        }
        break;
#        "PATCH" => "update", 
#        "DELETE" => 'delete', 
#        default => fn() => { throw new Exception ("unsupported http method\n"); },
}
?>
