<?php

require_once "../../include.php";


function update(string $path, array $payload, string $query): int
{
    //PATCH /src/1?id=14
    //PATCH /src/2?name=foo
    //PATCH /src/3?name_abbr=bar

    global $dbconn;
    if ($payload === []) 
        throw new InvalidArgumentException ("empty payload");
    $path_expl = explode("/", trim($path, "/"), 2);
    if (count($path_expl) !== 2) 
        throw new InvalidArgumentException ("desired path: \"/src/table_id\"");
    if ($path_expl[0] !== 'src') 
        throw new InvalidArgumentException ("only allowed to access via \"/src\"");

    $table_id = $path_expl[1];
    $values = []; $cols_values = []; 
    $pg_query_str = ""; $access_col = "";
    $args_cnt = 1;
    
    switch ($table_id) {
        case "1":
            $pg_query_str = "UPDATE students SET "; 
            $access_col = "id";
            foreach ($payload as $key => $value) { 
                if ($value === "") 
                    throw new InvalidArgumentException ("no empty values allowed");
                switch ($key) {
                    case "name":
                    case "surname":
                    case "second_surname":
                        $cols_values[] = strlen($value) <= 64 ? sprintf("%s = $%d", $key, $args_cnt++) : throw new InvalidArgumentException ("insufficient value length"); 
                        $values[] = $value; 
                        break;
                    case "group_name":
                        $cols_values[] = strlen($value) <= 10 ? sprintf("%s = $%d", $key, $args_cnt++) : throw new InvalidArgumentException ("insufficient value length"); 
                        $values[] = $value; 
                        break;
                    default:
                        throw new InvalidArgumentException ("invalid data field passed in");
                        break;
                } 
            }
            break;
             
        case "2":
            $pg_query_str = "UPDATE academic_groups SET ";
            $access_col = "name";
            foreach ($payload as $key => $value) {
                if ($value == "") 
                    throw new InvalidArgumentException ("no empty values allowed");
                switch ($key) {
                    case "name":
                        $cols_values[] = strlen($value) <= 10 ? sprintf("%s = $%d", $key, $args_cnt++) : throw new InvalidArgumentException ("insufficient value length");
                        $values[] = $value;
                        break;
                    case "faculty_abbr":
                        $cols_values[] = sprintf("%s = $%d", $key, $args_cnt++); 
                        $values[] = $value;
                        break;
                    default:
                        throw new InvalidArgumentException ("invalid data field passed in");
                        
                }
            }
            break;

        case "3": 
            $pg_query_str = "UPDATE faculties SET ";
            $access_col = "name_abbr";
            foreach ($payload as $key => $value) {
                if ($value == "") 
                    throw new InvalidArgumentException ("no empty values allowed");
                switch ($key) {
                    case "name":
                    case "name_abbr":
                        $cols_values[] = sprintf("%s = $%d", $key, $args_cnt++);
                        $values[] = $value; 
                        break;
                    default:
                        throw new InvalidArgumentException ("invalid data field passed in");
                }
            }
            break;

        default: 
            throw new InvalidArgumentException ("no such table id");
    }
    
    $pg_query_str .= implode(", ", $cols_values); 

    if ($query !== "" && $query !== null) {
        if (str_contains($query, "&"))
            throw new InvalidArgumentException ("several query parameters are not supported");  

        $query_expl = explode("=", $query, 2); //если ошибка то пусть вывод ошибки будет на совести бд

        if ($query_expl[0] !== $access_col)
            throw new InvalidArgumentException ("accessing tables is allowed only via primary keys (id, name, name_abbr)");  

        $pg_query_str .= sprintf(" WHERE %s = $%d", $query_expl[0], $args_cnt++);
        $values[] = $query_expl[1];
    } else throw new InvalidArgumentException ("empty query is not allowed");   

    $result = pg_query($dbconn, "BEGIN") ?: throw new Exception ("pg_query(\"BEGIN\") failed lol");
    
    if (($result = pg_query_params($dbconn, $pg_query_str, $values)) === false) { 
        pg_query($dbconn, "ROLLBACK");                                                       
        throw new Exception (pg_last_error($dbconn));
    }

    $rows_affected = pg_affected_rows($result);

    $result = pg_query($dbconn, "COMMIT") ?: throw new Exception ("pg_query(\"COMMIT\") failed lol");

    return $rows_affected;
}

?>
