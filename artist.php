<?php
  require_once('config.php');

  // Create a connection to the database
  // Or return an already created pdo
  $pdo = NULL;
  function create_connection() {
    global $pdo;
    if (isset($pdo))
      return $pdo;
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  }

  function link_artists() {
    try {
      $pdo = create_connection();
      $sql = "select * from artists order by LastName";
      $result = $pdo->query($sql);
      while($row = $result->fetch()) {
        $id = $row['ArtistID'];
        $name = $row['FirstName'].' '.$row['LastName'];
        $classes = 'item';
        if (isset($_GET['id']) && $_GET['id'] == $id)
          $classes .= ' active';
        $goto = $_SERVER['SCRIPT_NAME'].'?id='.$id;

        $link = '<a href="%s" class="%s">%s</a>';
        echo sprintf($link, $goto, $classes, $name);
      }

    }  catch (PDOException $e)
    { die($e->getMessage()); }
  }


  function get_artist($artist_id) {
    $row = NULL; 
    try {
      $pdo = create_connection();
      $sql = sprintf('select * from artists where ArtistId="%s"',$artist_id);
      $result = $pdo->query($sql);
      $row = $result->fetch();
    } catch (PDOException $e) {
        die($e->getMessage());
    }
    return $row;
  }

  function edit_artist() {
    if (!isset($_POST['column']) || $_POST['column'] == 'default')
      return;
    $column = $_POST['column'];
    if (!isset($_POST['value']) || trim($_POST['value']) == '')
      return;
    $value = trim($_POST['value']);

    $artist_id = $_GET['id'];

    try {
      $pdo = create_connection();
      $sql = sprintf('update artists set %s ="%s" where ArtistID=%s;', $column, $value, $artist_id);
      $result = $pdo->query($sql);
    } catch (PDOException $e) {
        die($e->getMessage());
    }
  }
  function delete_artist() {
    if (!isset($_GET['id']) || !isset($_GET['delete']) )
      return;
    $artist_id = $_GET['id'];

    try {
      $pdo = create_connection();
      $sql = 'delete from artists where ArtistID="'.$artist_id.'";';
      $result = $pdo->query($sql);
      remove_artist_in_paintings($artist_id);
    } catch (PDOException $e) {
        die($e->getMessage());
    }
  }
  function remove_artist_in_paintings($artist_id) {
    try {
      $pdo = create_connection();
      $sql = 'update paintings set ArtistID=NULL where ArtistId="'.$artist_id.'";';
      $result = $pdo->query($sql);

    } catch (PDOException $e) {
      die($e->getMessage());
    }
  }


  function create_artist() {
    if (!isset($_GET['id']) || !isset($_GET['create']) )
      return;
 
    $columns = ['FirstName', 'LastName', 'Nationality', 'Gender', 'YearOfBirth', 'Details', 'ArtistLink'];
    $values = [];
    foreach ($columns as $field) {
      if (isset($_POST[$field]) && trim($_POST[$field]) != '')
      	$values[$field] = '"'.trim($_POST[$field]).'"';
      else
        $values[$field] = 'NULL';
    }
    if ($values['LastName'] == 'NULL')
      return;
    $sql_columns = "";
    $sql_values = "";
    $columns = array_keys($values);
    for ($i=0; $i < count($values); $i++) {
      if ($i != 0) {
        $sql_columns .= ', ';
        $sql_values .= ', ';
      }
      $sql_columns .= $columns[$i];
      $sql_values .= $values[$columns[$i]];
    }

    $sql = sprintf('insert into artists (%s) values (%s);', $sql_columns, $sql_values);

    try {
      $pdo = create_connection();
      $result = $pdo->query($sql);
    } catch (PDOException $e) {
        die($e->getMessage());
    }
  }

  function print_artist($row) {
    
    $format =  '
    <div class="item"><div class="content">
     <h4 class="header">%s</h4>
     <div class="description">
      <p><strong>Nationality</strong>: %s</p>
      <p><strong>Gender</strong>: %s</p>
      <p><strong>Year of Birth</strong>: %s</p>
      <p><strong>Details</strong>: %s</p>
      <p><strong>Artist Link</strong>: %s</p>
     </div>
    </div></div>';

    $name = '['.$row['ArtistID'].'] '.$row['FirstName'].' '.$row['LastName'];
    $Nationality = $row['Nationality'];
    $Gender = $row['Gender'];
    $YoB = $row['YearOfBirth'];
    $Details = $row['Details'];
    $Link = $row['ArtistLink'];

    echo sprintf($format, $name, $Nationality, $Gender, $YoB, $Details, $Link);
  }


  function form_create_artist() {
    $id = 0;
    if (isset($_GET['id']) )
      $id = $_GET['id'];
    echo '<h4 class="header">--- Create an Artist ---</h4>';
    echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?id='.$id.'&create=1" method="post">';

    echo '<label for="FirstName">First Name: </label><input type="text" id="FirstName" name="FirstName"><br/>';
    echo '<label for="LastName">Last Name: </label><input type="text" id="LastName" name="LastName"><br/>';
    echo '<label for="Nationality">Nationality: </label><input type="text" id="Nationality" name="Nationality"><br/>';
    echo '<label for="Gender">Gender: </label><input type="text" id="Gender" name="Gender"><br/>';
    echo '<label for="YearOfBirth">Year of Birth: </label><input type="number" id="YearOfBirth" name="YearOfBirth"><br/>';
    echo '<label for="Details">Details: </label><input type="text" id="Details" name="Details"><br/>';
    echo '<label for="ArtistLink">Artist Link: </label><input type="text" id="ArtistLink" name="ArtistLink"><br/>';

    
    echo '<input class="ui button" type="submit" value="Submit">';
    echo '</form>';

    if (isset($_GET['create']) && isset($_POST['LastName'])) {
      $ln = strtolower(trim($_POST['LastName']));
      if ($ln == '' || $ln == 'null') {
        echo '<p style="color:red;">Last Name cannot be "NULL" or empty</p>';
        return;
      }
    }
  }

  function form_edit_artist() {
    $format = '
     <form action="%s" method="post">
      <label for="column">Edit:</label>
      <select id="column" name="column">
        <option value="default">a field.</option>
        <option value="FirstName">First Name</option>
        <option value="LastName">Last Name</option>
        <option value="Nationality">Nationality</option>
        <option value="Gender">Gender</option>
        <option value="YearOfBirth">Year of Birth</option>
        <option value="Details">Details</option>
        <option value="ArtistLink">Artist Link</option>
      </select>
      <input type="text" id="value" name="value">
      <input type="submit" value="Submit">
    </form>';

    $goto = $_SERVER['SCRIPT_NAME'].'?id='.$_GET['id'].'&edit=1';
    echo sprintf($format, $goto);

    if (isset($_GET['edit'])) {
      $col = $_POST['column'];
      $val = trim($_POST['value']);
      if ($col == 'default')
        echo '<p style="color:red;">Select an attribute to edit</p>';
      if ($val == '')
        echo '<p style="color:red;">Enter a value into the form</p>';

    }

  }

  function form_delete_artist($row) {
    $format = '<br/><br/><a href="%s" class="ui button">Delete %s</a>';
    $goto = $_SERVER['SCRIPT_NAME'].'?id='.$_GET['id'].'&delete=1';
    $name = $row['FirstName'].' '.$row['LastName'];

    echo sprintf($format, $goto, $name);
  }

  // form_artist
  function form_artist() {
    if (!isset($_GET['id'] ))
      return;
    $id = $_GET['id'];
    if (!isset($id)) {
      echo '<div class="item">' . 'Select Artist to Edit' . '</div>';
      return;
    }

    $row = get_artist($id);
    if (empty($row)) {
      echo '<p>Artist not found</p>';
      return;
    }

    if (!isset($row)) {
      echo '<div class="item">SQL ERROR</div>';
      return;
    }
   
    print_artist($row);
    form_edit_artist();
    form_delete_artist($row);
  }


 
 edit_artist();
 delete_artist();
 create_artist(); 
?>

<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallerry - Artists</title>
   <link href="semantic/semantic.css" rel="stylesheet"> 
</head>
<body>
   <main class="ui container">
      <div class="ui secondary segment">
         <h1>Art Gallery - Artists</h1>
 
         <div class="ui small horizontal link list page-links">
          <a class="item" href="index.php">Home</a>
          <a class="item" href="artist.php">Artists</a>
          <a class="item" href="paintings.php">Paintings</a>
          <a class="item" href="genres.php">Genres</a>
          <a class="item" href="eras.php">Eras</a>
	  <a class="item" href="gallaries.php">Gallaries</a>
	  <a class="item" href="subjects.php">Subjects</a>
          <a class="item" href="art-painting-genres.php">Painting Genres</a>
	  <a class="item" href="art-painting-subjects.php">Painting Subjects</a>
         </div>

      </div>
      <div class="ui segment">
         <div class="ui segment">
           <?php form_create_artist(); ?>
         </div>
         <div class="ui grid">
           <div class="four wide column">
              <div class="ui link list">
               <?php link_artists(); ?>
              </div>
           </div>
           <div class="twelve wide column">
              <div class="ui items">
                <?php form_artist(); ?>
              </div>
           </div>
         </div>
      </div>
   </main>

</body>
</html>
