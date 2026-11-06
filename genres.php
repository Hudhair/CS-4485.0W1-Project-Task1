<?php
  require_once("config.php");

  // Create a connection to the database
  function create_connection() {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  }

/*
 Displays the list of artist links on the left-side of page
*/
function link_genres() {
   try {
         $pdo = create_connection();
         $sql = "select * from genres order by GenreName";
         $result = $pdo->query($sql);
         while ($row = $result->fetch()) {
            $id = $row['GenreID'];
            $goto = $_SERVER['SCRIPT_NAME'].'?id='.$id;
            $classes = 'item';
            if (isset($_GET['id']) && $_GET['id'] == $id)
              $classes .= ' active';
            $content = $row['GenreName'];

            $format = '<a href="%s" class="%s">%s</a>';
            echo sprintf($format, $goto, $classes, $content);
         }
         $pdo = null;
   } catch (PDOException $e)
     { die( $e->getMessage() ); }
}

function print_genre() {
  $id = $_GET['id'];

  try {
    $pdo = create_connection();
    $sql = sprintf('select * from genres where GenreID=%s;',$id);
    $row = $pdo->query($sql)->fetch();

    if (empty($row))
      return;

    $name = $row['GenreName'];
    $desc = $row['Description'];
    $link = $row['Link'];
    
    $format = '<div class="item"><div class="content"><h4 class="header">[%s] %s</h4>
      <div class="description">
       <p><strong>Description: </strong>%s</p>
       <p><strong>Link: </strong>%s</p>
      </div>
     </div>
    </div>';
    
    echo sprintf($format,$id, $name, $desc, $link);

  }  catch (PDOException $e)
  { die($e->getMessage() ); }
}

function form_edit_genre() {
  $id = $_GET['id'];
  $form = '<div class="item"><form action="%s" method="post">
            <label for="column"><strong>Edit:</strong></label>
             <select id="column" name="column">
              <option value="default">a field.</option>
              <option value="GenreName">Genre Name</option>
              <option value="Description">Description</option>
              <option value="Link">Link</option>
             </select>
             <input type="text" id="value" name="value">
             <input type="submit" value="Submit">
            </form></div>';

  $goto = $_SERVER['SCRIPT_NAME'].'?id='.$id.'&edit=1';
  echo sprintf($form, $goto);
}

function form_delete_genre() {
  $id = $_GET['id'];
  try {
    $pdo = create_connection();
    $sql = sprintf('select * from Genres where GenreID=%s;',$id);
    $row = $pdo->query($sql)->fetch();
    if (empty($row))
      return;

    $name = $row['GenreName'];
    $output = '<div class="item"><a href="%s?id=%s&delete=1" class="ui button">Delete %s</a></div>';
    $goto = $_SERVER['SCRIPT_NAME'];
    echo sprintf($output, $goto, $id, $name);

  } catch (PDOException $e) 
  { die($e->getMessage() ); }
}

function form_add_genre() {
  $goto = $_SERVER['SCRIPT_NAME'] . '?';
  if (isset($_GET['id']) )
    $goto .= 'id='.$_GET['id'].'&';
  $goto .= 'add=1';

  $output = '<div class="item"><h4 class="header">--- Add a Genre ---</h4>
   <form action="%s" method="post">
    <label for="GenreName"><strong>Name: </strong></label>
    <input type="text" id="GenreName" name="GenreName"><br/>
    <label for="Description"><strong>Description: </strong></label>
    <input type="text" id="Description" name="Description"><br/>
    <label for="Link"><strong>Link: </strong></label>
    <input type="text" id="Link" name="Link"><br/><br/>
    <input class="ui button" type="submit" value="Submit">
   </form></div>';
   echo sprintf($output, $goto);
}


function add_genre() {
  
// GenreName cannot be null
  if (!isset($_GET['add']) || !isset($_POST['GenreName']) || trim($_POST['GenreName']) == '') 
    return;
  $name = '"'.trim($_POST['GenreName']).'"';
  $desc = 'null';
  $link = 'null'; 
  if (isset($_POST['Description']) && trim($_POST['Description']) != '')
    $desc = '"'.trim($_POST['Description']).'"';
  if (isset($_POST['Link']) && trim($_POST['Link']) != '')
    $link = '"'.trim($_POST['Link']).'"';

  $sql = sprintf('insert into genres (GenreName, Description, Link) values (%s, %s, %s);', $name, $desc, $link);
  try {
    $pdo = create_connection();
    $result = $pdo->query($sql);
  } catch (PDOException $e)
    { die($e->getMessage()); }
}

function edit_genre() {
  if (!isset($_GET['edit']) || !isset($_GET['id']) )
    return;
  $id = $_GET['id'];
  if (!isset($_POST['column']) || $_POST['column'] == 'default')
    return;
  $col = $_POST['column'];
  if (!isset($_POST['value']) || trim($_POST['value']) == '')
    return;
  $val = trim($_POST['value']);
  
  $sql = sprintf('update genres set %s = "%s" where GenreID=%s;', $col, $val, $id);
  try {
    $pdo = create_connection();
    $pdo->query($sql);
  } catch (PDOException $e)
    { die($e->getMessage()); }

}
function delete_genre() {
  if (!isset($_GET['delete']) || !isset($_GET['id']) )
    return;
  $id = $_GET['id'];
  $sql = sprintf('delete from genres where GenreID=%s;',$id);
  try {
    $pdo = create_connection();
    $pdo->query($sql);
    delete_genre_from_paintinggenres($id);
  } catch (PDOException $e)
  { die($e->getMessage()); }
}
function delete_genre_from_paintinggenres($genre_id) {
  $sql = sprintf('update paintinggenres set GenreID = null where GenreID = %s;', $genre_id);
  try {
    $pdo = create_connection();
    $pdo->query($sql);
  } catch (PDOException $e)
  { die($e->getMessage()); }

}


 add_genre();
 edit_genre();
 delete_genre();
 
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Genres</title>
   <link href="semantic/semantic.css" rel="stylesheet"> 
</head>
<body>
   <main class="ui container">
      <div class="ui secondary segment">
         <h1>Art Gallery - Genres</h1>
 
         <div class="ui small horizontal link list page-links">
          <a class="item" href="index.php">Home</a>
          <a class="item" href="artist.php">Artists</a>
          <a class="item" href="paintings.php">Paintings</a>
          <a class="item" href="genres.php">Genres</a>
          <a class="item" href="eras.php">Eras</a>
	  <a class="item" href="gallaries.php">Gallaries</a>
	  <a class="item" href="subjects.php">Subjects</a>
          <a class="item" href="paintinggenres.php">Painting Genres</a>
	  <a class="item" href="paintingeras.php">Painting Eras</a>

         </div>
      </div>
      <div class="ui segment">
         <div class="ui segment">
           <?php form_add_genre(); ?>
         </div>
         <div class="ui grid">
           <div class="four wide column">
              <div class="ui link list">
                 <?php link_genres(); ?>
              </div>
           </div>
           <div class="twelve wide column">
              <div class="ui items">
                <?php 
  if (isset($_GET['id']) ) {
    print_genre();
    form_edit_genre(); 
    form_delete_genre();
  }
 ?>
              </div>
           </div>
         </div>
      </div>
   </main>

</body>
</html>