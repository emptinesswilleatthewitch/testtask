<?php

require_once "../../include.php";


function create(string $path, array $payload): true
{
    //POST /src/1
    //POST /src/2
    //POST /src/3

    global $dbconn;
    if ($payload === []) 
        throw new InvalidArgumentException ("empty payload");
    $path_expl = explode("/", trim($path, "/"), 2);
    if (count($path_expl) !== 2) 
        throw new InvalidArgumentException ("desired path: \"/src/table_id\"");
    if ($path_expl[0] !== 'src') 
        throw new InvalidArgumentException ("only allowed to access via \"/src\"");

    $table_id = $path_expl[1];
    $values = []; $values_ordered = []; 
    $pg_query_str = "";

    switch ($table_id) {
        case "1":
            $pg_query_str = "INSERT INTO students (name, surname, second_surname, group_name) VALUES ($1, $2, $3, $4)";
            foreach ($payload as $key => $value) { //была идея сделать через преинициализированный массив со ключами (столбцами в таблице) и через in_array + тернарки 
                if ($value === "")                 //но мне кажется это будет менее производительным 
                    throw new InvalidArgumentException ("no empty values allowed");
                switch ($key) {
                    case "name":
                    case "surname":
                    case "second_surname":
                        $values[$key] = strlen($value) <= 64 ? $value : throw new InvalidArgumentException ("insufficient value length"); 
                        break;
                    case "group_name":
                        $values[$key] = strlen($value) <= 10 ? $value : throw new InvalidArgumentException ("insufficient value length"); 
                        break;
                    default:
                        throw new InvalidArgumentException ("invalid data field passed in");
                        break;
                } 
            }
            if (count($values) !== 4) throw new InvalidArgumentException ("wrong count of arguments");
            $values_ordered[0] = $values["name"];
            $values_ordered[1] = $values["surname"];
            $values_ordered[2] = $values["second_surname"];
            $values_ordered[3] = $values["group_name"];
            break;
             
        case "2":
            $pg_query_str = "INSERT INTO academic_groups (name, faculty_abbr) VALUES ($1, $2)";
            foreach ($payload as $key => $value) {
                if ($value == "") 
                    throw new InvalidArgumentException ("no empty values allowed");
                switch ($key) {
                    case "name":
                        $values[$key] = strlen($value) <= 10 ? $value : throw new InvalidArgumentException ("insufficient value length");
                        break;
                    case "faculty_abbr":
                        $values[$key] = $value;
                        break;
                    default:
                        throw new InvalidArgumentException ("invalid data field passed in");
                        
                }
            }
            if (count($values) !== 2) throw new InvalidArgumentException ("wrong count of arguments");
            $values_ordered[0] = $values["name"];
            $values_ordered[1] = $values["faculty_abbr"];
            break;

        case "3": 
            $pg_query_str = "INSERT INTO faculties (name, name_abbr) VALUES ($1, $2)";
            foreach ($payload as $key => $value) {
                if ($value == "") 
                    throw new InvalidArgumentException ("no empty values allowed");
                switch ($key) {
                    case "name":
                    case "name_abbr":
                        $values[$key] = $value; 
                        break;
                    default:
                        throw new InvalidArgumentException ("invalid data field passed in");
                }
            }
            if (count($values) !== 2) throw new InvalidArgumentException ("wrong count of arguments");
            $values_ordered[0] = $values["name"];
            $values_ordered[1] = $values["name_abbr"];
            break;

        default: 
            throw new InvalidArgumentException ("no such table id");
    }

    $result = pg_query($dbconn, "BEGIN") ?: throw new Exception ("pg_query(\"BEGIN\") failed lol");
    
    if (pg_query_params($dbconn, $pg_query_str, $values_ordered) === false) { 
        pg_query($dbconn, "ROLLBACK");                                                       
        throw new Exception (pg_last_error($dbconn));
    }
    $result = pg_query($dbconn, "COMMIT") ?: throw new Exception ("pg_query(\"COMMIT\") failed lol");

    return true;
}

?>
