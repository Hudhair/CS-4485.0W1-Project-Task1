<?php //admin-delete.php
  require_once 'config.php';

  try
  {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
  }
  catch (\PDOException $e)
  {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }

  if (isset($_POST['table']) &&
      isset($_POST['object-id']))
  {
  	$table = $_POST['table'];
        $id = $_POST['object-id'];

        if ($table = "Paintings") {
 		$stmt = $pdo->prepare("DELETE FROM paintings WHERE PaintingID = :id");
    			$stmt->execute([
      				':id' => $id,
   			]);

       		if ($stmt->rowCount() > 0) {
       		 	echo "Record deleted successfully.";
    		} else {
       	 		echo "No record found with that ID.";
    		}
        }
        else if ($table = "Artists") {
 		$stmt = $pdo->prepare("DELETE FROM artists WHERE ArtistID = :id");
    			$stmt->execute([
      				':id' => $id,
   			]);

       		if ($stmt->rowCount() > 0) {
       		 	echo "Record deleted successfully.";
    		} else {
       	 		echo "No record found with that ID.";
    		}
        }

  }

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Art Gallery - Admin Delete</title>
  <link href="semantic/semantic.css" rel="stylesheet">
  <style>main { padding-top: 20px; padding-bottom: 40px; }</style>
</head>

<body>
<main class="ui container">
  <div class="ui secondary segment"><h1>Art Gallery - Admin Deletion</h1></div>
   <div class="ui segment">
    <form class="ui-form" method="POST" action="">
      <div class="form-box">
      <h3>Delete Record based on Object ID</h3>
      <hr>
        <div class="field">
            <label for="table">Select Table:</label>
            <select name="table" id="table" required>
                <option value="">Choose a table</option>
                <option value="Artists">Artists</option>
                <option value="Paintings">Paintings</option>

            </select>
        </div>

        <div class="field">
            <label for="object-id">Object ID:</label>
            <input type="text" name="object-id" id="object-id" placeholder="Enter ID to delete" required>
        </div>

        <button type="submit">
            Delete Object
        </button>
    </form>
   </div>
 </div>
</main>
</body>
</html>