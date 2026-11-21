<?php

require_once('config.php');


/*
 Add an era
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {

   try {

      $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $eraName = $_POST['eraName'];
      $eraYears = $_POST['eraYears'];

      $sql = "INSERT INTO Eras (EraName, EraYears)
              VALUES (:eraName, :eraYears)";

      $statement = $pdo->prepare($sql);

      $statement->bindValue(':eraName', $eraName);
      $statement->bindValue(':eraYears', $eraYears);

      $statement->execute();

      $pdo = null;

      header("Location: eras.php");
      exit;
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}


/*
 Delete an era
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {

   try {

      $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $eraName = $_POST['eraName'];

      $sql = "DELETE FROM Eras
              WHERE EraName = :eraName";

      $statement = $pdo->prepare($sql);

      $statement->bindValue(':eraName', $eraName);

      $statement->execute();

      $pdo = null;

      header("Location: eras.php");
      exit;
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}


/*
 Displays the list of eras
*/
function outputEras() {

   try {

      $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);

      $pdo->setAttribute(
         PDO::ATTR_ERRMODE,
         PDO::ERRMODE_EXCEPTION
      );

      $sql = "SELECT * FROM Eras
              ORDER BY EraName";

      $result = $pdo->query($sql);

      while ($row = $result->fetch()) {

         echo '<a href="' .
              $_SERVER["SCRIPT_NAME"] .
              '?era=' .
              urlencode($row['EraName']) .
              '" class="';

         if (isset($_GET['era']) &&
             $_GET['era'] == $row['EraName']) {

            echo 'active ';
         }

         echo 'item">';

         echo htmlspecialchars($row['EraName']);

         echo '</a>';
      }

      $pdo = null;
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}


/*
 Displays selected era details
*/
function outputEraDetails() {

   try {

      if (isset($_GET['era'])) {

         $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);

         $pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
         );

         $sql = "SELECT * FROM Eras
                 WHERE EraName = :eraName";

         $statement = $pdo->prepare($sql);

         $statement->bindValue(
            ':eraName',
            $_GET['era']
         );

         $statement->execute();

         $row = $statement->fetch();

         if ($row) {

            echo '<div class="ui segment">';

            echo '<h2>';
            echo htmlspecialchars($row['EraName']);
            echo '</h2>';

            echo '<p><strong>Era Years:</strong> ';
            echo htmlspecialchars($row['EraYears']);
            echo '</p>';

            echo '<form method="post"
                  onsubmit="return confirm(\'Are you sure you want to delete this era?\');">';

            echo '<input type="hidden"
                  name="eraName"
                  value="' .
                  htmlspecialchars($row['EraName']) .
                  '">';

            echo '<input type="hidden"
                  name="delete"
                  value="yes">';

            echo '<input type="submit"
                  class="ui red button"
                  value="Delete Era">';

            echo '</form>';

            echo '</div>';
         }

         $pdo = null;
      }
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}

?>


<!DOCTYPE html>

<html>

<head>

   <meta charset="utf-8">

   <meta name="viewport"
         content="width=device-width, initial-scale=1.0">

   <title>Art Gallery - Eras</title>

   <link href="semantic/semantic.css"
         rel="stylesheet">

</head>


<body>

<main class="ui container">


   <div class="ui secondary segment">

      <h1>Art Gallery - Eras</h1>

   </div>


   <div class="ui segment">

      <div class="ui grid">


         <!-- Era list -->

         <div class="five wide column">

            <h3>Eras</h3>

            <div class="ui link list">

               <?php outputEras(); ?>

            </div>

         </div>


         <!-- Era details -->

         <div class="eleven wide column">

            <h3>Era Details</h3>

            <?php outputEraDetails(); ?>

         </div>


      </div>

   </div>


   <!-- Add Era -->

   <div class="ui segment">

      <h3>Add Era</h3>

      <form class="ui form"
            method="post"
            action="eras.php">

         <div class="field">

            <label>Era Name</label>

            <input type="text"
                   name="eraName"
                   required>

         </div>


         <div class="field">

            <label>Era Years</label>

            <input type="text"
                   name="eraYears"
                   required>

         </div>


         <input type="hidden"
                name="add"
                value="yes">


         <input type="submit"
                class="ui button"
                value="Add Era">

      </form>

   </div>


</main>

</body>

</html>