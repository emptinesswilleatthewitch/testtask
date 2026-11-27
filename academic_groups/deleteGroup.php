<?php

require_once "../include.php";

global $dbconn;

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") { 
    $err_msg = "DELETE method expected";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);
}

$uri = parse_url($_SERVER["REQUEST_URI"]);     

$query = $uri["query"] ?: '';
$col = "name"; $pg_query_str = ""; 
$value = null;

header('Content-Type: application/json');

if ($query !== '') {
    if (count(($query_expl = explode("=", $query, 2))) !== 2) {
        $err_msg = "invalid format of query arguments";
        log_error(400, $err_msg);
        throw new InvalidArgumentException ($err_msg);
    }
    if ($query_expl[0] !== $col) {
        $err_msg ="couldnt access table via column {$query_expl[0]}";
        log_error(400, $err_msg);
        throw new InvalidArgumentException ($err_msg);
    }
    $value = $query_expl[1] ?? null; 
} else {
    $err_msg = "no query parameter provided";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);
}


if (pg_query($dbconn, "BEGIN") === false) {
    $err_msg = "pg_query(\"BEGIN\") failed lol";
    log_error(500, $err_msg);
    throw new Exception ($err_msg);
}  

$result = null;
$pg_query_str = "DELETE FROM academic_groups WHERE {$col} = $1";
if (($result = pg_query_params($dbconn, $pg_query_str, array($value))) === false) {
    pg_query($dbconn, "ROLLBACK");                                                       
    $err_msg = "delete query didnt succeed";
    log_error(500, $err_msg);
    throw new exception ($err_msg);
}

$rows_affected = pg_affected_rows($result);

if (pg_query($dbconn, "COMMIT") === false) { 
    $err_msg = "pg_query(\"COMMIT\") failed lol";
    log_error(500, $err_msg);
    throw new Exception ($err_msg);
}

http_response_code(200);
echo json_encode(["rows_deleted" => $rows_affected, "success" => true]);


?>
