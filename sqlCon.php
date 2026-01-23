<?php
  require_once __DIR__ . '/vendor/autoload.php';

  try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
  } catch (\Dotenv\Exception\InvalidPathException $e) {
    die('Could not find .env file. Please create one in the root directory.');
  }

  $host = getenv('DB_HOST');
  $db   = getenv('DB_NAME_HOPE');
  $user = getenv('DB_USER_HOPE');
  $pass = getenv('DB_PASSWORD_HOPE');
  $charset = 'utf8mb4';

  if ($host === false || $db === false || $user === false || $pass === false) {
    die('Please set the required environment variables: DB_HOST, DB_NAME_HOPE, DB_USER_HOPE, DB_PASSWORD_HOPE');
  }

  $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
  $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  try {
       $pdo = new PDO($dsn, $user, $pass, $options);
  } catch (\PDOException $e) {
       throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }
?>
