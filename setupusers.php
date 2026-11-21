<?php //setupusers.php
  require_once 'config.php';

  try
  {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
  }
  catch (\PDOException $e)
  {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }

  $query = "CREATE TABLE users1 (
    forename VARCHAR(32) NOT NULL,
    surname  VARCHAR(32) NOT NULL,
    username VARCHAR(32) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    admin    BOOLEAN NOT NULL DEFAULT FALSE
  )";

  $result = $pdo->query($query);

  $forename = 'first1';
  $surname  = 'last1';
  $username = 'user1';
  $password = 'pass1';
  $hash     = password_hash($password, PASSWORD_DEFAULT);
  $admin = 0;
  
  add_user($pdo, $forename, $surname, $username, $hash, $admin);

  $forename = 'first2';
  $surname  = 'last2';
  $username = 'user2';
  $password = 'pass2';
  $hash     = password_hash($password, PASSWORD_DEFAULT);
  
  add_user($pdo, $forename, $surname, $username, $hash, $admin);

  $forename = 'first3';
  $surname  = 'last3';
  $username = 'user3';
  $password = 'pass3';
  $hash     = password_hash($password, PASSWORD_DEFAULT);

  add_user($pdo, $forename, $surname, $username, $hash, $admin);

  $forename = 'first4';
  $surname  = 'last4';
  $username = 'user4';
  $password = 'pass4';
  $hash     = password_hash($password, PASSWORD_DEFAULT);
  
  add_user($pdo, $forename, $surname, $username, $hash, $admin);

  function add_user($pdo, $fn, $sn, $un, $pw, $ad)
  {
    $stmt = $pdo->prepare('INSERT INTO users1 VALUES(:fn,:sn,:un,:pw, :ad)');
    $stmt->execute([
      ':fn' => $fn,
      ':sn' => $sn,
      ':un' => $un,
      ':pw' => $pw,
      ':ad' => $ad
    ]);
  }
?>
