<?php
  require_once("config.php");
  require_once('header.php');
  require_once('art-sql.php');

  require_once('genres-links.php');
  require_once('genres-edit.php');


/*
 Displays the list of artist links on the left-side of page
*/
function link_genres() {
	$sql_statement = "select * from genres order by GenreName desc";
	$result = sql_execute($sql_statement);
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
}

function print_genre() {
  $id = $_GET['id'];
  $row = sql_get_genre($id);

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
    
    echo sprintf($format,$id, $name, htmlspecialchars_decode($desc), $link);
}


function form_delete_genre() {
  $id = $_GET['id'];
  $sql_statement = sprintf('select * from Genres where GenreID=%s;',$id);
  $result = sql_execute($sql_statement);

  $row = $result->fetch();
  if (empty($row))
    return;

  $name = $row['GenreName'];
  $output = '<div class="item"><a href="%s?id=%s&delete=1" class="ui button">Delete %s</a></div>';
  $goto = $_SERVER['SCRIPT_NAME'];
  echo sprintf($output, $goto, $id, $name);
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

  $sql_statement = sprintf('insert into genres (GenreName, Description, Link) values (%s, %s, %s);', $name, $desc, $link);
  $result = sql_execute($sql_statement);
}

function delete_genre() {
  if (!isset($_GET['delete']) || !isset($_GET['id']) )
    return;
  $id = $_GET['id'];
  $sql_statement = sprintf('delete from genres where GenreID=%s;',$id);
  $result = sql_execute($sql_statement);

  $sql_statement = sprintf('update paintinggenres set GenreID = null where GenreID = %s;', $genre_id);
  sql_execute($sql_statement);
}

 add_genre();
 genre_edit();
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
	<?php add_header('Genres'); ?>
      <div class="ui segment">
         <div class="ui segment">
           <?php form_add_genre(); ?>
         </div>
         <div class="ui grid">
           <div class="four wide column">
              <div class="ui link list">
                 <?php genres_links(); ?>
              </div>
           </div>
           <div class="twelve wide column">
              <div class="ui items">
                <?php 
  if (isset($_GET['id']) ) {
    print_genre();
    genre_edit_form(); 
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