<?php

if (!$dbconn = pg_connect("host=localhost port=5432 dbname=testtask user=postgres password=libed")) 
    throw new Exception ("couldnt connect to db" . "\n");

