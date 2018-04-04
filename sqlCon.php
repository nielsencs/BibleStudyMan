<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'user');
define('DB_PASS', 'crypt1cP4ssw0rd!');
define('DB_NAME', 'bible');

$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)
        OR die('Sorry I&rsquo;m unable to connect to the database. (' . mysqli_connect_error() . ')' );
?>
