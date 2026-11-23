<?php
require_once "../../include.php";


function delete(string $path, string $query): int
{
    //DELETE /src/1?id=14 HTTP/1.1
    global $dbconn;
    $path_expl = explode("/", trim($path, "/"), 2);
    if (count($path_expl) !== 2) 
        throw new InvalidArgumentException ("desired path: \"/src/table_id?arg=value\"");
    if ($path_expl[0] !== 'src') 
        throw new InvalidArgumentException ("only allowed to access via \"/src\"");

    $table_id = $path_expl[1];
    $value = null;

    [$table, $col] = match ($table_id) {
        "1" => ["students", "id"],
        "2" => ["academic_groups", "name"],
        "3" => ["faculties", "name_abbr"],
        default => throw new InvalidArgumentException ("no such table id"),
    };
    if ($query !== '') {
        if (count(($query_expl = explode("=", $query, 2))) !== 2) 
            throw new InvalidArgumentException ("invalid format of query arguments");
        if ($query_expl[0] !== $col) 
            throw new InvalidArgumentException ("couldnt access table via column {$col}");
        $value = $query_expl[1] ?? null; 
    }

    $pg_query_str = ""; 
    $extracted_data = "";

    $result = pg_query($dbconn, "BEGIN") ?: throw new Exception ("pg_query(\"BEGIN\") failed lol");
    if ($query === "" || $query === null) 
        throw new Exception ("no query parameter");    
    else {
        $pg_query_str = "DELETE FROM {$table} WHERE {$col} = $1";
        $result = pg_query_params($dbconn, $pg_query_str, array($value));
    }
    if ($result === false) { 
        pg_query($dbconn, "ROLLBACK");                                                       
        throw new Exception ("delete query didnt succeed");
    } 

    $rows_affected = pg_affected_rows($result);
    
    $result = pg_query($dbconn, "COMMIT") ?: throw new Exception ("pg_query(\"COMMIT\") failed lol");

    return $rows_affected;
}

?>
