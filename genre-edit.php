<?php
	require_once('config.php');
	require_once('art-sql.php');

	function genre_edit_form() {
		if (!isset($_GET['id']))
			return;
		$id = $_GET['id'];
		$row = sql_get_genre($id);

		$goto = $_SERVER['SCRIPT_NAME'].'?id='.$_GET['id'].'&edit=1';
		$format = '
		<div class="item" style="width:100%%;">
	  	 <form class="content" action="%s" method="post">
	   	  <h3>Edit: [%s]
		   <input type="text" name="GenreName" value="%s" style="border: None;">
		  </h3>
		  <table style="margin-left: 2%%; width: 100%%; table-layout: auto;">
	    	   <tr>
             	    <td><h4><label for="Description">Description: </label></h4></td>
 	            <td><textarea type=text" 
                        name="Description" 
			oninput="this.style.height = this.scrollHeight + \'px\'" 
			style="resize: None; border: None; width: 90%%" >%s</textarea></td>
            	   </tr>

	    	    <tr>
	     	     <td><h4><label for="Link">Link: </label></h4></td>
  	     	     <td><input type="text" name="Link" value="%s" style="border: None; width: 90%%"</td>
	    	    </tr>
	    	   </table>
		   <input class="ui button" type="submit" value="Submit"></form></div>';

		$name = $row['GenreName'];
		$desc = $row['Description'];
		$link = $row['Link'];
		
		if ($name == NULL)
			$name = '';
		if ($desc == NULL)
			$desc = '';
		if ($link == NULL)
			$link = '';

		echo sprintf($format, $goto, $id, $name, $desc, $link);	
	}

	function genre_edit() {
		global $genres_columns;
		if (!isset($_GET['edit']) || !isset($_GET['id']) )
    			return;
  		$id = $_GET['id'];
		
		$columns = $genres_columns;
		array_shift($columns);
		$sql_set = '';

		foreach ($columns as $index => $key) {
			if ($index != 0)
				$sql_set .= ', ';
			if (!isset($_POST[$key]) )
				continue;
			$value = trim($_POST[$key]);
			if ($value == '')
				$sql_set .= $key.' is NULL';
			else
				$sql_set .= $key.' = "'.htmlspecialchars($value).'"';
		}
		
		$sql_statement = sprintf('update genres set %s where GenreID=%s;', $sql_set , $id);
		echo '<p>'.$sql_statement.'</p>';
  		$result = sql_execute($sql_statement);
	}



?>