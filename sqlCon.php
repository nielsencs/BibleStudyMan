<?php
  $host = 'localhost';
  $db   = 'biblestu_dy';
  $user = 'root';
  $pass = '';
  $charset = 'utf8mb4';

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  try {
      $link = mysqli_connect($host, $user, $pass, $db);
      mysqli_set_charset($link, $charset);
  } catch (\mysqli_sql_exception $e) {
       throw new \mysqli_sql_exception($e->getMessage(), $e->getCode());
  }
  unset($host, $db, $user, $pass, $charset); // we don't need them anymore
?>
