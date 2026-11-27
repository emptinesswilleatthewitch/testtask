
<?php

require_once "../include.php";


global $dbconn;

if ($_SERVER["REQUEST_METHOD"] !== "GET") { 
    $err_msg = "GET method expected";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);
}

$method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER["REQUEST_URI"]);     

$query = $uri["query"] ?: '';
header('Content-Type: application/json');

$col = "id";
$result = null; $value = null;
$pg_query_str = ""; $extracted_data = "";

if ($query === "") {
    $pg_query_str = "SELECT * FROM students";
    $result = pg_query($dbconn, $pg_query_str);
} else {
    $query_expl = explode("=", $query, 2); 
    if ($query_expl[0] !== $col) {
        $err_msg = "access is only allowed via {$col}";
        log_error(500, $err_msg);
        throw new Exception ($err_msg);
    } else {
        $value = $query_expl[1];
    }
    $pg_query_str = "SELECT * FROM students WHERE {$col} = $1";
    $result = pg_query_params($dbconn, $pg_query_str, array($value));
}
if ($result === false) { 
    $err_msg = pg_last_error($dbconn);
    log_error(500, $err_msg);
    throw new Exception ($err_msg);
}

$extracted_data = pg_fetch_all($result);

echo json_encode(["data" => $extracted_data, "success" => true]);
http_response_code(200);


?>
