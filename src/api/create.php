<?php

require_once "../include.php";


function create(string $path, array $payload): array
{
    //POST /src/1
    //POST /src/2
    //POST /src/3

    global $dbconn;
    $path_expl = explode("/", trim($path, "/"), 2);
    if (count($path_expl) !== 2) 
        throw InvalidArgumentException ("desired path: \"/src/table_id\"");
    if ($path_expl[0] !== 'src') 
        throw InvalidArgumentException ("only allowed to access via \"/src\"");

    $table_id = $path_expl[1];
    $value = null;

    [$table, $col] = match ($table_id) {
        "1" => ["students", "id"],
        "2" => ["academic_groups", "name"],
        "3" => ["faculty", "faculty_abbr"],
        default => throw InvalidArgumentException ("no such table id");
    };
    if ($query !== '') {
        if (count(($query_expl = explode("=", $query, 2))) !== 2) 
            throw InvalidArgumentException ("invalid format of query arguments");
        if ($query_expl[0] !== $col) 
            throw InvalidArgumentException ("couldnt access table via column {$col}");
        $value = $query_expl[1] ?? null; 
    }

    $pg_query_str = ""; 
    $extracted_data = "";

    if ($query === "" || $query === null) {
        $pg_query_str = "SELECT * FROM {$table}";
        $result = pg_query($dbconn, $pg_query_str);
    }
    else {
        $pg_query_str = "SELECT * FROM {$table} WHERE {$col} = $1";
        $result = pg_query_params($dbconn, $pg_query_str, array($value));
    }
    if ($result === false)  
        throw new Exception ("read query didnt succeed");
    $extracted_data = pg_fetch_all($result);

    return $extracted_data ?: []; 
}

?>
