
<?php
/*
  session_start();

  if (isset($_SESSION['forename']))
  {
    header("Location: index.php"); 
    $forename = htmlspecialchars($_SESSION['forename']);
    $surname  = htmlspecialchars($_SESSION['surname']);

    echo "Welcome back $forename.<br>
          Your full name is $forename $surname.<br>";
  }
  else { 
*/
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>  
    <title>Login Page</title>    
    <link rel="stylesheet" href="login.css">

</head>

<body class="form-body">

<div class="form-box">
   <h1>Log In to Art Gallery</h1>
   <hr>

<form action="authenticate.php" name="loginForm" method="POST">
    <label class="icon" for="username">Username</label>
    <input type="text" name="username" id="username" placeholder="User Name" />

    <label class="icon" for="pass">Password</label>
    <input type="password" name="password" id="password" placeholder="Password" />

    <button type="submit" ><i class="fa fa-reply fa-fw"></i> Log In</button>
</form>
<form action="register.php" name="register">
    <button type="submit">Register a New Account</button>
</form>
</body>
</html>

<?php /* } */?>