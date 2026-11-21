<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>  
    <title>Registration Page</title>    
    <link rel="stylesheet" href="login.css">

</head>

<body class="form-body">

<div class="form-box">
   <h1>Register an Account</h1>
   <hr>

<form action="registeruser.php" name="registerForm" method="POST">
    <label class="icon" for="forename">First Name</label>
    <input type="text" name="forename" id="forename" placeholder="First Name" />

    <label class="icon" for="surname">Last Name</label>
    <input type="text" name="surname" id="surname" placeholder="Last Name" />

    <label class="icon" for="username">Username</label>
    <input type="text" name="username" id="username" placeholder="Username" />

    <label class="icon" for="pass">Password</label>
    <input type="password" name="password" id="password" placeholder="Password" />

    <button type="submit" name="admin" value="0"><i class="fa fa-reply fa-fw"></i>Register as Visitor</button>
    <button type="submit" name="admin" value="1"><i class="fa fa-reply fa-fw"></i>Register as Admin</button>
</form>
<form action="login.php" name="back">
    <button type="submit">Go Back to Login</button>
</form>
</body>
</html>