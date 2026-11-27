<?php

require_once "../include.php";

global $dbconn;

if ($_SERVER["REQUEST_METHOD"] !== "POST") { 
    $err_msg = "POST method expected";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);
}

$raw_payload = file_get_contents("php://input");
$payload = json_decode($raw_payload, true);

if ($payload == []) { 
    $err_msg = "empty payload";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);
}

header('Content-Type: application/json');

$values = []; $values_ordered = []; 
$pg_query_str = "";

$pg_query_str = "INSERT INTO students (name, surname, second_surname, group_name) VALUES ($1, $2, $3, $4)";
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
        if ($values[$key] <= 64) $values[$key] = $value;
        else {
            $err_msg = "insufficient length either for name, surname or second_surname";
            log_error(400, $err_msg);
            throw new InvalidArgumentException ($err_msg);
        }
        break;
    case "group_name":
        if ($values[$key] <= 10) $values[$key] = $value;
        else {
            $err_msg = "insufficient length either for name, surname or second_surname";
            log_error(400, $err_msg);
            throw new InvalidArgumentException ("insufficient length for group_name");
        }
        break;
    default:
        $err_msg = "invalid data field passed in";
        log_error(400, $err_msg);
        throw new InvalidArgumentException ("invalid data field passed in");
        break;
    } 
}
if (count($values) !== 4) {
    $err_msg = "wrong count of arguments";
    log_error(400, $err_msg);
    throw new InvalidArgumentException ($err_msg);
}
$values_ordered[0] = $values["name"];
$values_ordered[1] = $values["surname"];
$values_ordered[2] = $values["second_surname"];
$values_ordered[3] = $values["group_name"];

if (pg_query($dbconn, "BEGIN") === false) {
    $err_msg = "pg_query(\"BEGIN\") failed lol";
    log_error(500, $err_msg);
    throw new Exception ($err_msg);
}

if (pg_query_params($dbconn, $pg_query_str, $values_ordered) === false) { 
    $err_msg = pg_last_error($dbconn);
    log_error(500, $err_msg);
    pg_query($dbconn, "ROLLBACK");                                                       
    throw new Exception ($err_msg);
}
if ( pg_query($dbconn, "COMMIT") === false) {
    $err_msg = "pg_query(\"COMMIT\") failed lol"; 
    log_error(500, $err_msg);
    throw new Exception ($err_msg);
}  
http_response_code(201);
echo json_encode(["success" => true]);


?>

