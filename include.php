<?php

if (!$dbconn = pg_connect("host=localhost port=5432 dbname=testtask user=testtask password=libed")) 
    throw new Exception ("couldnt connect to db" . "\n");

require_once 'src/read.php';
require_once 'src/create.php';
require_once 'src/update.php';
require_once 'src/delete.php';
require_once 'include.php';

