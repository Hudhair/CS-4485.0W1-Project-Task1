<?php
require_once('config.php');
require_once('art-sql.php');

function artist_add_sql($columns) {
	$sql_columns = '';
	$sql_values = '';

	$keys = array_keys($columns);
	for ($i = 0; $i < count($keys); $i++) {
		if ($i != 0) {
			$sql_columns .= ', ';
			$sql_values .= ', ';
 		}
		$sql_columns .= $keys[$i];
		$sql_values .= $columns[$keys[$i]];

        }

	return sprintf('insert into artists (%s) values (%s);', $sql_columns, $sql_values);
}
function artist_add_sql_find($columns) {
	$where = '';

	$keys = array_keys($columns);
	foreach ($keys as $index => $key) {
		if ($index != 0)
			$where .= ' and ';
		if ($columns[$key] == 'NULL')
			$where .= $key.' is null';
		else
			$where .= $key.'= '.$columns[$key];
        }
	
	return sprintf('select * from artists where %s;', $where);
}

function artist_add() {
    global $artists_columns;
    if (!isset($_GET['create']) )
      return;

    $values = [];
    foreach ($artists_columns as $field) {
      if ($field == 'ArtistID')
	continue;
      if (isset($_POST[$field]) && trim($_POST[$field]) != '')
      	$values[$field] = '"'.trim($_POST[$field]).'"';
      else
        $values[$field] = 'NULL';
    }

    if ($values['LastName'] == 'NULL') {
      return;
    }

    // Create artist
    $sql_statement = artist_add_sql($values);
    $result = sql_execute($sql_statement);

    // Retrieve row of Artist
    $sql_statement = artist_add_sql_find($values);
    $result = sql_execute($sql_statement);
    if (!isset($result) or $result == NULL) {
  	echo 'Retrieval Error';
	return;
    }
 
    $err = $result->errorCode();

    $row = $result->fetch();
    if (gettype($row) != 'array')
	return;
    $goto = $_SERVER['SCRIPT_NAME'].'?id='.$row['ArtistID'].'&created=1';
    header('Location: '.$goto);
    

}

function artist_add_form() {
  echo '<div class="item"><form action="'.$_SERVER['SCRIPT_NAME'].'?create=1" method="post">';
  echo '<h4 class="header"> Add Artist</h4>';

  echo '<table>';
  echo '<tr>
	 <td><label for="FirstName">First Name: </label></td>
         <td><input type="text" name="FirstName"></td>
        </tr>
	<tr>
	 <td><label for="LastName">Last Name: </label></td>
         <td><input type="text" name="LastName"></td>
        </tr>
        <tr>
	 <td><label for="Nationality">Nationality: </label></td>
         <td><input type="text" name="Nationality"></td>
        </tr>
	<tr>
	 <td><label for="Gender">Gender: </label></td>
         <td><input type="text" name="Gender" maxlength="1"></td>
        </tr>
	<tr>
	 <td><label for="YearOfBirth">Year of Birth: </label></td>
         <td><input type="number" name="YearOfBirth"></td>
        </tr>
	<tr>
	 <td><label for="YearOfDeath">Year of Death: </label></td>
         <td><input type="number" name="YearOfDeath"></td>
        </tr>
	<tr>
	 <td><label for="Details">Details: </label></td>
	 <td><textarea style="resize: None; width: 90%%"
		oninput="this.style.height = this.scrollHeight + \'px\'"></textarea></td>
	</tr>
	<tr>
	 <td><label for="ArtistLink">ArtistLink: </label></td>
         <td><input type="url" name="ArtistLink"></td>
        </tr>';
  echo '</table>';
    
    echo '<input class="ui button" type="submit" value="Submit">';
    echo '</form></div>';

}
