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
try { 
    switch ($method) {
        case "GET":
            $result = read($path, $query);
            echo json_encode(["data" => $result, "success" => true]);
            http_response_code(200);
            break;
        case "POST": 
            $result = create($path, $payload);
            echo json_encode(["success" => true]);
            http_response_code(201);
            break;
        case "PATCH":
            $result = update($path, $payload, $query);
            echo json_encode(["rows_updated" => $result, "success" => true]);
            http_response_code(200);
            break;
        case "DELETE":
            $result = delete($path, $query);
            echo json_encode(["rows_deleted" => $result, "success" => true]);
            http_response_code(200);
            break;
        default:
            throw new InvalidArgumentException ("unsupported http method"); 
            break;
    }
} catch (InvalidArgumentException $e) {
    echo json_encode(["error" => $e->getMessage(), "success" => false]);
    error_log($e->getMessage(), 3, ERR_LOG_PATH);
    http_response_code(400);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage(), "success" => false]);
    error_log(date("y:m:d h:i:s" . $e->getMessage(), 3, ERR_LOG_PATH);
    http_response_code(500);
} catch (Throwable $e) {
    error_log($e->getMessage(), 3, ERR_LOG_PATH);
    error_log(print_r(debug_stacktrace(), true), 3, ERR_LOG_PATH);
    http_response_code(500);
}
?>
