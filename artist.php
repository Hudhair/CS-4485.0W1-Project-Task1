<?php
  require_once('header.php');

  require_once('config.php');

  require_once('art-sql.php');
  require_once('artist-add.php');
  require_once('artist-edit.php');

  

  function link_artists() {
	$sql_statement = 'select * from artists order by LastName';
	$result = sql_execute($sql_statement);

	while( $row = $result->fetch() ) {
		$id = $row['ArtistID'];
		$name = $row['FirstName'].' '.$row['LastName'];
        	$classes = 'item';
        	if (isset($_GET['id']) && $_GET['id'] == $id)
          		$classes .= ' active';
        	$goto = $_SERVER['SCRIPT_NAME'].'?id='.$id;

        	$link = '<a href="%s" class="%s">%s</a>';
        	echo sprintf($link, $goto, $classes, $name);
	}
  }

  function delete_artist() {
    if (!isset($_GET['id']) || !isset($_GET['delete']) )
      return;
    $artist_id = $_GET['id'];

    $sql_statement = 'delete from artists where ArtistID="'.$artist_id.'";';
    sql_execute($sql_statement);
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


  

  function print_artist($row) {
    
    $format =  '
    <div class="item"><div class="content">
     <h4 class="header">%s</h4>
     <div class="description">
      <p><strong>Nationality</strong>: %s</p>
      <p><strong>Gender</strong>: %s</p>
      <p><strong>Year of Birth</strong>: %s</p>
      <p><strong>Year of Death</strong>: %s</p>
      <p><strong>Details</strong>: %s</p>
      <p><strong>Artist Link</strong>: %s</p>
     </div>
    </div></div>';

    $name = '['.$row['ArtistID'].'] '.$row['FirstName'].' '.$row['LastName'];
    $Nationality = $row['Nationality'];
    $Gender = $row['Gender'];
    $YoB = $row['YearOfBirth'];
    $YoD = $row['YearOfDeath'];
    $Details = $row['Details'];
    $Link = $row['ArtistLink'];

    echo sprintf($format, $name, $Nationality, $Gender, $YoB, $YoD, $Details, $Link);
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

    $row = sql_get_artist($id);

    if (empty($row)) {
      echo '<p>Artist not found</p>';
      return;
    }

    if (!isset($row)) {
      echo '<div class="item">SQL ERROR</div>';
      return;
    }
   
    print_artist($row);
    artist_edit_form($row);
    form_delete_artist($row);
  }

artist_add();
if (isset($_GET['id']) ) {
	$row = sql_get_artist($_GET['id']);
 
	delete_artist();
 	 
 	artist_edit($row);
}
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
      <?php add_header('Artist'); ?>
      <div class="ui segment">
         <div class="ui segment">
           <?php artist_add_form(); ?>
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
