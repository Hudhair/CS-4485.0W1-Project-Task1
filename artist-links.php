<?php

require_once('config.php');
require_once('art-sql.php');

function artist_links() {
	global $artists_columns;

	$sql_statement = 'select count(*) from artists';
	$result = sql_execute($sql_statement);
	$row_counts = $result->fetch()[0];
	
	echo sprintf('<h3>Artists: %s records</h3>', $row_counts);
	$script = $_SERVER['SCRIPT_NAME'];
	$button_format = '<a class="ui button" href="'.$script.'?%s">%s</a>';

	$sort_asc = true;
	$sort_by = $artists_columns;
	$sort_by_index = 0;
	unset($sort_by['Details']);
	unset($sort_by['ArtistLink']);

	$sort_name = true;

	$query = [];
	if (isset($_GET['id']) )
		$query['id'] = $_GET['id'];
	if (isset($_GET['sort_asc']) )
		$sort_asc = $_GET['sort_asc'];
	if (isset($_GET['sort_by']) )
		$sort_by_index = $_GET['sort_by'];
	
	$query['sort_asc'] = $sort_asc;
	$query['sort_by'] = $sort_by_index < count($sort_by) - 3 ? $sort_by_index + 1 : 0;
	echo sprintf($button_format, http_build_query($query), $sort_by[$sort_by_index]);

	$query['sort_asc'] = !$sort_asc;
	$query['sort_by'] = $sort_by_index;
	echo sprintf($button_format, http_build_query($query), $sort_asc ? 'ASC' : 'DESC');
	
	$sort_direction = $sort_asc ? 'ASC' : 'DESC';
	$sql_statement = "select * from artists order by ".$sort_by[$sort_by_index]." ".$sort_direction.';';

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


?>