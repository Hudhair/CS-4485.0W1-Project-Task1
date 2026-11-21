<?php // authenticate.php
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

  if (isset($_POST['username']) &&
      isset($_POST['password']))
  {
    $stmt = $pdo->prepare('SELECT * FROM users1 WHERE username=:un');
    $stmt->execute([':un' => $_POST['username']]);

    if (!$stmt->rowCount()) { 
       echo "User not found";
       echo "<br><br>";
       echo "<a href='login.php'>Go back.</a>";
       die();
    }

    $row = $stmt->fetch();
    $fn  = $row['forename'];
    $sn  = $row['surname'];
    $un  = $row['username'];
    $pw  = $row['password'];
    $ad  = $row['admin'];
      

    if (password_verify($_POST['password'], $pw)) {
      $_SESSION['forename'] = $fn;
      $_SESSION['surname'] = $sn;
      $_SESSION['username'] = $un;
      $_SESSION['admin'] = $ad;
      
      //echo htmlspecialchars("$fn $sn : Hi $fn, you are now logged in as '$un'");
      //echo '<br><br>';
      if ($ad == 0) {
      	header("Location: index.php"); 
      }
      else {
	header("Location: adminindex.php");
      }
      //echo "Please <a href='index.php'>click here</a> to proceed to the gallery.";
    }
    else  {
       echo "Invalid username/password combination";
       echo "<br><br>";
       echo "<a href='login.php'>Go back.</a>";
       die();
    }
  }
?>
