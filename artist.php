<?php
  require_once('config.php');

  function state_machine() {
    if (!isset($_GET['id']))
      return;
    $id = $_GET['id'];

    if (isset($_GET['edit']) )
    	edit_artist($id);
    else if (isset($_GET['delete']))
    	delete_artist($id);
    else if (isset($_GET['create']))
    	create_artist();
  }


  // Create a connection to the database
  function create_connection() {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  }

  function cyclical_link($content="", $classes="", $id=null) {
    $format = '<a href="%s?id=%s" class="%s">%s</a>';

    echo sprintf($format, $_SERVER['SCRIPT_NAME'], $id, $classes, $content);
  }

  function foreach_artist($func_ptr) {
    try { 
      $pdo = create_connection();
      $sql = "select * from artists order by LastName";
      $result = $pdo->query($sql);
      while($row = $result->fetch())
        $func_ptr($row);
    } catch (PDOException $e) {
      die($e->getMessage());
    }
  }
  function get_artist($artist_id) {
    $row;
    try {
      $pdo = create_connection();
      $sql = 'select * from artists where ArtistId="'.$artist_id . '"';
      $result = $pdo->query($sql);
      $row = $result->fetch();
    } catch (PDOException $e) {
        die($e->getMessage());
    }
    return $row;
  }
  function edit_artist($artist_id) {
    if (!isset($_POST['column']) || $_POST['column'] == 'default')
      return;
    $column = $_POST['column'];
    if (!isset($_POST['value']) || trim($_POST['value']) == '')
      return;
    $value = trim($_POST['value']);

    try {
      $pdo = create_connection();
      $sql = 'update artists set '.$column.' ="'.$value.'" where ArtistID='.$artist_id.';';
      $result = $pdo->query($sql);
    } catch (PDOException $e) {
        die($e->getMessage());
    }
  }
  function delete_artist($artist_id) {
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
    $columns = ['FirstName', 'LastName', 'Nationality', 'Gender', 'YearOfBirth', 'Details', 'ArtistLink'];
    $values = [];
    foreach ($columns as $field) {
      if (isset($_POST[$field]) && trim($_POST[$field]) != '')
      	$values[$field] = trim($_POST[$field]);
    }
    if (empty($values))
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
      $sql_values .= '"'.$values[$columns[$i]].'"';
    }

    $sql = sprintf('insert into artists (%s) values (%s);', $sql_columns, $sql_values);
    
    try {
      $pdo = create_connection();
      $result = $pdo->query($sql);
    } catch (PDOException $e) {
        die($e->getMessage());
    }
    

  }



  // Output artists
  function link_artist($row) {
   $classes = 'item';
   $artist = '['.$row['ArtistID'].'] ' .$row['FirstName'] . ' ' . $row['LastName'];
   if (isset($_GET['id']) && $_GET['id'] == $row['ArtistID'])
     $classes .= ' active';
   cyclical_link($artist, $classes, $row['ArtistID']);
  }

  function form_create_artist() {
    $id = 0;
    if (isset($_GET['id']) )
      $id = $_GET['id'];
    echo '<h4 class="header">--- Create an Artist ---</h4>';
    echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?id='.$id.'&create=1" method="post">';
    $columns = ['FirstName', 'LastName', 'Nationality', 'Gender', 'YearOfBirth', 'Details', 'ArtistLink'];

    echo '<label for="FirstName">First Name: </label><input type="text" id="FirstName" name="FirstName"><br/>';
    echo '<label for="LastName">Last Name: </label><input type="text" id="LastName" name="LastName"><br/>';
    echo '<label for="Nationality">Nationality: </label><input type="text" id="Nationality" name="Nationality"><br/>';
    echo '<label for="Gender">Gender: </label><input type="text" id="Gender" name="Gender"><br/>';
    echo '<label for="YearOfBirth">Year of Birth: </label><input type="text" id="YearOfBirth" name="YearOfBirth"><br/>';
    echo '<label for="Details">Details: </label><input type="text" id="Details" name="Details"><br/>';
    echo '<label for="ArtistLink">Artist Link: </label><input type="text" id="ArtistLink" name="ArtistLink"><br/>';

    
    echo '<input class="ui button" type="submit" value="Submit">';
    echo '</form>';
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
    /* ArtistID, FirstName, LastName, Nationality, Gender
       YearOfBirth, Details, ArtistLink 
    */
    echo '<div class="item"><div class="content">';
    echo '<h4 class="header">'.$row['FirstName'].' '.$row['LastName'].'</h4>';
    echo '<div class="description">';
    echo '<p><strong>' . 'Nationality</strong>: ' . $row['Nationality'] . '</p>';
    echo '<p><strong>' . 'Gender</strong>: ' . $row['Gender'] . '</p>';
    echo '<p><strong>' . 'Year of Birth</strong>: ' . $row['YearOfBirth'] . '</p>';
    echo '<p><strong>' . 'Details</strong>: ' . $row['Details'] . '</p>';
    echo '<p><strong>' . 'Artist Link</strong>: ' . $row['ArtistLink'] . '</p>';
    echo '</div>';
    echo '</div></div>';
    
    echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?id='.$_GET['id'].'&edit=1" method="post">
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

   echo '<br/><br/><a href="'.$_SERVER['SCRIPT_NAME'].'?id='.$_GET['id'].'&delete=1" class="ui button">Delete '.$row['FirstName'].' '.$row['LastName'].'</a>';

  }
  

  state_machine();
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
   <main class="ui container">
      <div class="ui secondary segment">
         <h1>Art Gallery - Artists</h1>
      </div>
      <div class="ui segment">
         <div class="ui segment">
           <?php form_create_artist(); ?>
         </div>
         <div class="ui grid">
           <div class="four wide column">
              <div class="ui link list">
               <?php foreach_artist( function($row) {link_artist($row);}); ?>
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