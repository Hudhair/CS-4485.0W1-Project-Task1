<?php //admin-update.php
  require_once 'config.php';

  try
  {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
  }
  catch (\PDOException $e)
  {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }

  if (isset($_POST['artist-id']) &&
      isset($_POST['fname']) &&
      isset($_POST['lname']) &&
      isset($_POST['nationality']) &&
      isset($_POST['gender']))
  {
	$aid = $_POST['artist-id'];
  	$fn = $_POST['fname'];
        $ln = $_POST['lname'];
	$nat = $_POST['nationality'];
	$gen = $_POST['gender'];

 	$stmt = $pdo->prepare("UPDATE Artists SET FirstName = :fn, LastName = :ln, Gender = :gen, Nationality = :nat WHERE ArtistID = :aid");
    	$stmt->execute([
		':aid' => $aid,
      		':fn' => $fn,
		':ln' => $ln,
		':gen' => $gen,
		':nat' => $nat
   	]);

       	if ($stmt->rowCount() > 0) {
       		echo "Record updated successfully.";
	}
  }
  else if (isset($_POST['painting-id']) &&
	   isset($_POST['title']) &&
	   isset($_POST['filename']) &&
	   isset($_POST['painting-artist-id']))
  {
	$pid = $_POST['painting-id'];
	$tl = $_POST['title'];
  	$file = $_POST['filename'];
	$aid = $_POST['painting-artist-id'];

 	$stmt = $pdo->prepare("UPDATE Paintings SET ArtistID = :aid, ImageFileName = :fl, Title = :tl WHERE PaintingID = :pid");
    	$stmt->execute([
      		':pid' => $pid,
		':aid' => $aid,
		':fl' => $file,
		':tl' => $tl
   	]);

       	if ($stmt->rowCount() > 0) {
       		echo "Record updated successfully.";
	}
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Art Gallery - Admin Update Record</title>
  <link href="semantic/semantic.css" rel="stylesheet">
  <style>main { padding-top: 20px; padding-bottom: 40px; }</style>
</head>

<body>
<main class="ui container">
  <div class="ui secondary segment"><h1>Art Gallery - Admin Update Records</h1></div>
   <div class="ui segment">
    <div class="form-box">
    <h3>Update an Artist</h3>
    <hr>

    <form class="ui-form" method="POST" action="">
        <div class="field">
            <label for="artist-id">Artist's ID: </label>
            <input type="text" name="artist-id" id="artist-id" placeholder="Enter Artist's ID" required>
        </div>

        <div class="field">
            <label for="fname">First Name: </label>
            <input type="text" name="fname" id="fname" placeholder="Enter New First Name">
        </div>

        <div class="field">
            <label for="lname">Last Name: </label>
            <input type="text" name="lname" id="lname" placeholder="Enter New Last Name" required>
        </div>

        <div class="field">
            <label for="nationality">Nationality: </label>
            <input type="text" name="nationality" id="nationality" placeholder="Enter New Nationality" required>
        </div>

        <div class="field">
            <label for="gender">Gender: </label>
            <input type="text" name="gender" id="gender" placeholder="Enter New Gender" required>
        </div>

        <button type="submit">
            Add Object
        </button>
    </form>

    <h3>Update Paintings Table</h3>
    <hr>

    <form class="ui-form" method="POST" action="">
        <div class="field">
            <label for="painting-id">Painting ID:    </label>
            <input type="text" name="painting-id" id="painting-id" placeholder="Enter Painting's ID" required>
        </div>

        <div class="field">
            <label for="title">Title:    </label>
            <input type="text" name="title" id="title" placeholder="Enter New Title" required>
        </div>

        <div class="field">
            <label for="filename">Filename: </label>
            <input type="text" name="filename" id="filename" placeholder="Enter New Filename" required>
        </div>

        <div class="field">
            <label for="painting-artist-id">Artist's ID: </label>
            <input type="text" name="painting-artist-id" id="painting-artist-id" placeholder="Enter New Artist's ID" required>
        </div>

        <button type="submit">
            Add Object
        </button>
    </form>
   </div>
 </div>
</main>
</body>
</html>