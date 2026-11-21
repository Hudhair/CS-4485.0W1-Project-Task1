<?php //admin-add.php
  require_once 'config.php';

  try
  {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
  }
  catch (\PDOException $e)
  {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }

  if (isset($_POST['fname']) &&
      isset($_POST['lname']))
  {
  	$fn = $_POST['fname'];
        $ln = $_POST['lname'];

 	$stmt = $pdo->prepare("INSERT INTO Artists (FirstName, LastName) VALUES(:fn,:ln)");
    	$stmt->execute([
      		':fn' => $fn,
		':ln' => $ln
   	]);

       	if ($stmt->rowCount() > 0) {
       		echo "Record added successfully.";
	}
  }
  else if (isset($_POST['title']) &&
	   isset($_POST['filename']) &&
	   isset($_POST['artist-id']))
  {
	$tl = $_POST['title'];
  	$file = $_POST['filename'];
	$id = $_POST['artist-id'];

 	$stmt = $pdo->prepare("INSERT INTO Paintings (ArtistID, ImageFileName, Title) VALUES(:id, :fl,:tl)");
    	$stmt->execute([
      		':id' => $id,
		':fl' => $file,
		':tl' => $tl
   	]);

       	if ($stmt->rowCount() > 0) {
       		echo "Record added successfully.";
	}
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Art Gallery - Admin Add Record</title>
  <link href="semantic/semantic.css" rel="stylesheet">
  <style>main { padding-top: 20px; padding-bottom: 40px; }</style>
</head>

<body>
<main class="ui container">
  <div class="ui secondary segment"><h1>Art Gallery - Admin Add Record</h1></div>
   <div class="ui segment">
    <div class="form-box">
    <h3>Add to Artists Table</h3>
    <hr>

    <form class="ui-form" method="POST" action="">
        <div class="field">
            <label for="fname">First Name: </label>
            <input type="text" name="fname" id="fname" placeholder="Enter Artist's First Name" required>
        </div>

        <div class="field">
            <label for="lname">Last Name: </label>
            <input type="text" name="lname" id="lname" placeholder="Enter Artist's Last Name" required>
        </div>

        <button type="submit">
            Add Object
        </button>
    </form>

    <h3>Add to Paintings Table</h3>
    <hr>

    <form class="ui-form" method="POST" action="">
        <div class="field">
            <label for="title">Title:    </label>
            <input type="text" name="title" id="title" placeholder="Enter Painting's Title" required>
        </div>

        <div class="field">
            <label for="filename">Filename: </label>
            <input type="text" name="filename" id="filename" placeholder="Enter Painting's Filename" required>
        </div>

        <div class="field">
            <label for="artist-id">Artist's ID: </label>
            <input type="text" name="artist-id" id="artist-id" placeholder="Enter Painting's Artist's ID" required>
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