<?php

require_once "../../include.php";


function create(string $path, array $payload): true
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
    $values = [];
    $pg_query_str = "";

    switch ($table_id) {
        case "1":
            $pg_query_str = "INSERT INTO students (name, surname, second_surname, group_name) VALUES ($1, $2, $3, $4)";
            foreach ($payload as $key => $value) { //была идея сделать через преинициализированный массив со ключами (столбцами в таблице) и через in_array + тернарки 
                if ($key === "name" ||             //но мне кажется это будет менее производительным 
                    $key === "surname" || 
                    $key === "second_surname")  
                        $values[$key] = strlen($value) <= 64 ? $value : throw InvalidArgumentException ("insufficient value length");
                elseif ($key === "group_name") {
                        $values[$key] = strlen($value) <= 10 ? $value : throw InvalidArgumentException ("insufficient value length");
                } else throw new InvalidArgumentException ("invalid data field passed in");
                if ($value === "") 
                    throw new InvalidArgumentException ("no empty values allowed");
            }
            if (count($values) !== 4) throw InvalidArgumentException ("wrong count of arguments");
            $values_copy = $values; 
            $values[0] = $values_copy["name"];
            $values[1] = $values_copy["surname"];
            $values[2] = $values_copy["second_surname"];
            $values[3] = $values_copy["group_name"];
            break;
             
        case "2":
            $pg_query_str = "INSERT INTO academic_groups (name, faculty_abbr) VALUES ($1, $2)";
            foreach ($payload as $key => $value) {
                if ($value == "") 
                    throw new InvalidArgumentException ("no empty values allowed");
                if ($key === "name") 
                    $values[$key] = strlen($value) <= 10 ? $value : throw new InvalidArgumentException ("insufficient value length");
                elseif ($key === "faculty_abbr") 
                    $values[$key] = $value;
                else 
                    throw new InvalidArgumentException ("invalid data field passed in");
            }
            if (count($values) !== 2) throw InvalidArgumentException ("wrong count of arguments");
            $values_copy = $values; 
            $values[0] = $values_copy["name"];
            $values[1] = $values_copy["faculty_abbr"];
            break;

        case "3": 
            $pg_query_str = "INSERT INTO faculties (name, name_abbr) VALUES ($1, $2)";
            foreach ($payload as $key => $value) {
                if ($key === "name" ||
                    $key === "name_abbr") {
                    $values[$key] = $value;  
                } else throw new InvalidArgumentException ("invalid data field passed in");
            }
            if (count($values) !== 2) throw InvalidArgumentException ("wrong count of arguments");
            $values_copy = $values; 
            $values[0] = $values_copy["name"];
            $values[1] = $values_copy["name_abbr"];
            break;

        default: 
            throw new InvalidArgumentException ("no such table id");
    }

    $result = pg_query($dbconn, "BEGIN") ?: throw new Exception ("pg_query(\"BEGIN\") failed lol");
    
    if (pg_query_params($dbconn, $pg_query_str, array($values[0], $values[1])) === false) { //угар эта шляпа не работает по-человечески
        pg_query($dbconn, "ROLLBACK");                                                       
        throw new Exception (pg_last_error($dbconn));
    }
    $result = pg_query($dbconn, "COMMIT") ?: throw new Exception ("pg_query(\"COMMIT\") failed lol");

    return true;
}

?>
