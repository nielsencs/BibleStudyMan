<?php
  $host = getenv('DB_HOST');
  $db   = getenv('DB_NAME');
  $user = getenv('DB_USER');
  $pass = getenv('DB_PASSWORD');
  $charset = 'utf8mb4';

  if ($host === false || $db === false || $user === false || $pass === false) {
    die('Please set the required environment variables: DB_HOST, DB_NAME, DB_USER, DB_PASSWORD');
  }

  $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
  $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO.ATTR_EMULATE_PREPARES   => false,
  ];

  try {
       $pdo = new PDO($dsn, $user, $pass, $options);
  } catch (\PDOException $e) {
       throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }
?>
