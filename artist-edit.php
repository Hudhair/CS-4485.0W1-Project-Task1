<?php

	require_once('art-sql.php');

function artist_edit_form($row) {
	$goto = $_SERVER['SCRIPT_NAME'].'?id='.$_GET['id'].'&edit=1';
	
	$format = 
	'<div class="item" style="width:100%%;">
	  <form class="content" action="%s" method="post">
	   <h3>Edit: 
		[%s]
		<br/>| <input type="text" name="FirstName" value="%s" style="border: None;">
		<br/>| <input type="text" name="LastName" value="%s"  style="border: None;">
	   </h3>
	   <table style="margin-left: 2%%; width: 100%%; table-layout: auto;">
	    <tr>
             <td><h4><label for="Nationality">Nationality: </label></h4></td>
 	     <td><input type=text" 
                        name="Nationality" value="%s" 
			style="border: None; width: 90%%"></td>
            </tr>
	    <tr>
	     <td><h4><label for="Gender">Gender: </label></h4></td>
  	     <td><input type=text" maxlength="1" name="Gender" value="%s" style="border: None; width: 90%%"</td>
	    </tr>
	    <tr>
	     <td><h4><label for="YearOfBirth">Year of Birth: </label></h4></td>
  	     <td><input type=number" name="YearOfBirth" value="%s" style="border: None; width: 90%%"</td>
	    </tr>
	    <tr>
	     <td><h4><label for="YearOfDeath">Year of Death: </label></h4></td>
  	     <td><input type=number" name="YearOfDeath" value="%s" style="border: None; width: 90%%"</td>
	    </tr>
	    <tr>
	     <td><h4><label for="Details">Details: </label></h4></td>
 	     <td><input type=text name="Details" style="border: None; width: 90%%" value="%s"></td>
             </tr>
		    <tr>
		     <td><h4><label for="ArtistLink">Artist Link: </label></h4></td>
		     <td><input 
			  type=text" 
                          name="ArtistLink" value="%s" 
			  style="border: None; width: 90%%"></td>
                    </tr>			
		   </table>';

	$id = $_GET['id'];
	$FN = $row['FirstName'];
	$LN = $row['LastName'];
	$Nationality = $row['Nationality'];
	$Gender = $row['Gender'];
	$yob = $row['YearOfBirth'];
	$yod = $row['YearOfDeath'];
	$details = $row['Details'];
	$artist_link = $row['ArtistLink'];
	
	if ($FN == NULL)
		$FN = '';
	if ($LN == NULL)
		$LN = '';
	if ($Nationality == NULL)
		$Nationality = '';
	if ($Gender == NULL)
		$Gender = '';
	if ($yob == NULL)
		$yob = '';
	if ($yod == NULL)
		$yod = '';
	if ($details == NULL)
		$details = '';
	if ($artist_link == NULL)
		$artis_link = '';
	

 	echo sprintf($format, $goto, $id, $FN, $LN, $Nationality, $Gender, $yob, $yod, $details, $artist_link);
	

	echo '<input class="ui button" type="submit" value="Submit"></form></div>';
}


function artist_edit($row) {
	global $artists_columns;
	if (! isset($_GET['edit']) or !isset($_GET['id']) )
		return;
	$id = $_GET['id'];
	if (!isset($_POST['LastName']) or trim($_POST['LastName']) == '')
		return;

	$columns = $artists_columns;
	array_shift($columns);
	
	$sql_set = '';
	foreach ($columns as $index => $key) {
		$value = $_POST[$key];
		if (($key == 'YearOfBirth' or $key == 'YearOfDeath') and !is_numeric($value))
			continue;

		if ($index != 0)
			$sql_set .= ', ';
		
		if (trim($value) == '')
			$sql_set .= $key.' is NULL'; 
		else
			$sql_set .= $key.' = "'.$value.'"'; 
	}


	$sql_statement = sprintf('update artists set %s where ArtistID=%s;', $sql_set, $_GET['id']);
	$result = sql_execute($sql_statement);
}







?>