<?php 
session_start();
require_once('config.php'); 

/*
 Displays the list of subject links on the left-side of page
*/
function outputSubjects() {
   try {
         $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         $sql = "SELECT DISTINCT s.SubjectName, s.SubjectID
		FROM Paintingsubjects p 
		INNER JOIN Subjects s ON (p.SubjectID = s.SubjectID) limit 0,1000";
         $result = $pdo->query($sql);
         while ($row = $result->fetch()) {
            echo '<a href="' . $_SERVER["SCRIPT_NAME"] . '?id=' . $row['SubjectID'] . '" class="';
            if (isset($_GET['id']) && $_GET['id'] == $row['SubjectID']) echo 'active ';
            echo 'item">';
            echo $row['SubjectName'] . '</a>';
         }
         $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}
/*
 Deletes a painting-subject link from the database
*/
function deletePaintingSubject() {
  if (isset($_POST['delete']) && isset($_POST['id']))
  {
    $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $id   = sanitize_post_value($pdo, 'id');
    $query  = "DELETE p
		FROM paintingsubjects p
		WHERE p.PaintingID=$id";
    $result = $pdo->query($query);
    $pdo = null;
  }
}

/*
 Adds a painting-subject link to the database
*/
function addPaintingSubject() {
  if (isset($_POST['painting'])    &&
      isset($_POST['subject']))
  {
    $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $title = sanitize_post_value($pdo, 'painting');
    $subject = sanitize_post_value($pdo, 'subject');

    // check if painting title actually exists in the database
    $query = "SELECT PaintingID
	      FROM Paintings
	      WHERE Title=$title";
    $result = $pdo->query($query);
    $paintingRow = $result->fetch();
    $painting = $paintingRow['PaintingID'];

    $result = null;
    // check if painting subject actually exists in the database
    $query = "SELECT SubjectID
              FROM Subjects
	      WHERE SubjectName=$subject";
    $result = $pdo->query($query);
    $subjectRow = $result->fetch();
    $subject = $subjectRow['SubjectID'];
    $result = null;

    // insert into database if both painting title and subject exist
    if (!$paintingRow) {
	echo "Cannot insert, painting does not exist";
    }
    else if (!$subjectRow) {
	echo "Cannot insert, subject does not exist";
    }
    else {
        $query = "INSERT INTO paintingsubjects (PaintingID, SubjectID) VALUES" .
	  "($painting, $subject)";
        $result = $pdo->query($query);
        echo "Successfully inserted.";
    }
  }
}

/*
 Displays the list of paintings for the artist id specified in the id query string
*/
function outputPaintings() {
   try {
      if (isset($_GET['id']) && $_GET['id'] > 0) {   
         $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         
         $sql = 'SELECT * 
		FROM Paintings p
		INNER JOIN Paintingsubjects p2 ON (p.PaintingID = p2.PaintingID)
		WHERE SubjectID=:id';
         $id = $_GET['id'];
         $statement = $pdo->prepare($sql);
         $statement->bindValue(':id', $id);
         $statement->execute();

         while ($row = $statement->fetch()) {
            outputSinglePainting($row);         
         }
         $pdo = null;
      }
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

/*
 Displays a single painting
*/
function outputSinglePainting($row) {
   echo '<div class="item">';
   echo '<div class="image">';
   echo '<img  src="images/art/works/square-medium/' . $row['ImageFileName'] .'.jpg">';
   echo '</div>';
   echo '<div class="content">';
   echo '<h4 class="header">';
   echo $row['Title']; 
   echo '</h4>';
   echo '<p class="description">';
   echo $row['Excerpt'];
   echo '</p>';
   echo '</div>';  // end class=content
   echo '</div>';  // end class=item
  $r0 = htmlspecialchars($row['PaintingID']);
    echo <<<_END
  <form action='art-painting-subjects.php' method='post'>
  <input type='hidden' name='delete' value='yes'>
  <input type='hidden' name='id' value='$r0'>
  <input type='submit' value='DELETE RECORD'></form>
_END;
}

function sanitize_post_value($pdo, $var)
{
  return $pdo->quote($_POST[$var]);
}

?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Chapter 14</title>
   <link href="semantic/semantic.css" rel="stylesheet"> 
</head>
<body>
  <?php
     echo <<<_END
     <form action="art-painting-subjects.php" method="post"><pre>
       <label for="paintingName">INSERT A NEW LINK BETWEEN A PAINTING AND A SUBJECT</label>
       Painting Name    <input type="text" name="painting" required>
       Subject Name     <input type="text" name="subject" required>
                      <input type="submit" value="ADD RECORD">
     </pre></form>
   _END; ?>
   <?php deletePaintingSubject(); ?>
   <?php addPaintingSubject(); ?>
   <main class="ui container">
      <div class="ui secondary segment">
         <?php echo '<h1>Welcome to the Art Gallery, Subject-Paintings section' . $_SESSION['forename'] . ' ' . $_SESSION['surname'] . '</h1>'; ?>
      </div>
      <div class="ui segment">
         <div class="ui grid">
           <div class="four wide column">
              <div class="ui link list">
                 <?php outputSubjects(); ?>
              </div>
           </div>
           <div class="twelve wide column">
              <div class="ui items">
                 <?php outputPaintings(); ?> 
              </div>
           </div>
         </div>
      </div>
   </main>

</body>
</html>