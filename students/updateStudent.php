<?php

require_once "../include.php";

global $dbconn;

if ($_SERVER["REQUEST_METHOD"] !== "PATCH") { 
    $err_msg = "PATCH method expected";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);
}

$values = []; $cols_values = []; 
$pg_query_str = ""; $access_col = "id";
$args_cnt = 1;

$method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER["REQUEST_URI"]);     

$query = $uri["query"] ?: '';

$raw_payload = file_get_contents("php://input");
$payload = json_decode($raw_payload, true);

if ($payload == []) {
    $err_msg = "empty payload";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);
}

$pg_query_str = "UPDATE students SET "; 
foreach ($payload as $key => $value) { 
    if ($value === "") {
        $err_msg = "no empty values allowed";
        log_error(400, $err_msg);
        throw new InvalidArgumentException ($err_msg);
    }
    switch ($key) {
    case "name":
    case "surname":
    case "second_surname":
        if (strlen($value) <= 64) {
            $cols_values[] = sprintf("%s = $%d", $key, $args_cnt++); 
            $values[] = $value; 
        } else {
            $err_msg = "insufficient value length";
            log_error(400, $err_msg);
            throw new InvalidArgumentException ($err_msg);
        }
        break;
    case "group_name":
        if (strlen($value) <= 10) {
            $cols_values[] = sprintf("%s = $%d", $key, $args_cnt++); 
            $values[] = $value; 
        } else {
            $err_msg = "insufficient value length";
            log_error(400, $err_msg);
            throw new InvalidArgumentException ($err_msg);
        }
        break;
    default:
        $err_msg = "invalid data passed in";
        log_error(400, $err_msg);
        throw new InvalidArgumentException ($err_msg);
    } 
}

$pg_query_str .= implode(", ", $cols_values); 

if ($query !== "") {
    if (str_contains($query, "&")) {
        $err_msg = "several query parameters are not supported";
        log_error(400, $err_msg);
        throw new InvalidArgumentException ($err_msg);  
    }

    $query_expl = explode("=", $query, 2); //если ошибка то пусть вывод ошибки будет на совести бд

    if ($query_expl[0] !== $access_col) {
        $err_msg = "accessing tables is allowed only via primary keys (id, name, name_abbr)";
        log_error(400, $err_msg);
        throw new InvalidArgumentException ($err_msg);  
    }

    $pg_query_str .= sprintf(" WHERE %s = $%d", $query_expl[0], $args_cnt++);
    $values[] = $query_expl[1];
} else {
    $err_msg = "empty query is not allowed";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);   
}


if (pg_query($dbconn, "BEGIN") === false) {
    $err_msg = "pg_query(\"BEGIN\") failed lol";
    log_error(500, $err_msg);
    throw new Exception ($err_msg);
}

$result = null;

if (($result = pg_query_params($dbconn, $pg_query_str, $values)) === false) { 
    $err_msg = pg_last_error($dbconn);
    log_error(500, $err_msg);
    pg_query($dbconn, "ROLLBACK");                                                       
    throw new Exception ($err_msg);   
}

$rows_affected = pg_affected_rows($result);

if (pg_query($dbconn, "COMMIT") === false) {
    $err_msg = "pg_query(\"COMMIT\") failed lol";
    log_error(500, $err_msg);
    throw new Exception ($err_msg);
}

http_response_code(200);
echo json_encode(["rows_affected" => $rows_affected, "success" => true]);


?>
