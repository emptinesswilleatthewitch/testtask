<?php

if (!$dbconn = pg_connect("host=localhost port=5432 dbname=testtask user=postgres password=libed")) 
    throw new Exception ("couldnt connect to db" . "\n");

define ("ERR_LOG_PATH", "../logs/log_api_error");

function log_error(int $code, string $errMsg) 
{
    http_response_code($code);
    error_log(date("y:m:d h:i:s") . $errMsg, 3, ERR_LOG_PATH);
    echo json_encode(["error" => $errMsg, "success" => false]);
}
