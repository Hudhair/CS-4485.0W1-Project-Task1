<?php //registeruser.php
  session_start();
  require_once 'config.php';

  try
  {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
  }
  catch (\PDOException $e)
  {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }

  if (isset($_POST['forename']) &&
      isset($_POST['surname'])  &&
      isset($_POST['username']) &&
      isset($_POST['password']) &&
      isset($_POST['admin']))
  {
  	$forename = $_POST['forename'];
  	$surname  = $_POST['surname'];
  	$username = $_POST['username'];
  	$password = $_POST['password'];
  	$hash     = password_hash($password, PASSWORD_DEFAULT);
  	$admin = $_POST['admin'];
	
        add_user($pdo, $forename, $surname, $username, $hash, $admin);	
  }

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

  header("Location: login.php"); 