<?php

require_once('config.php');
require_once('art-sql.php');

$max_pages = 0;
$records_per_page = 20;

$query = [];

function artist_links_query() {
	global $query;
	
	if (!empty($query))
		return $query;

	$str_query = $_SERVER['QUERY_STRING'];
	parse_str($str_query, $query);
	
	if (isset($_GET['id']) )
		$query['id'] = $_GET['id'];
	if (!isset($_GET['sort_asc']) )
		$query['sort_asc'] = true;
	if (!isset($_GET['sort_by']) )
		$query['sort_by'] = 0;
	if (!isset($_GET['at_page']) )
		$query['at_page'] = 0;

	return $query;
}

function artist_links_sql() {
	global $artists_columns, $records_per_page;

	$query = artist_links_query();
	$sort_by = $artists_columns[$query['sort_by']];
	
	$sort_direction = $query['sort_asc'] ? 'ASC' : 'DESC';
	$min = $query['at_page'] * $records_per_page;
	
	$sql_statement = sprintf('select * from artists order by %s %s limit '.$min.', '.$records_per_page.';', $sort_by, $sort_direction);
	return $sql_statement;
}


function artists_links_sorting() {
	global $artists_columns;

	$query = artist_links_query();
	$sort_by = $query['sort_by'];
	$sort_asc = $query['sort_asc'];
	$sort_by_names = $artists_columns;
	unset($sort_by_names['Details']);
	unset($sort_by_names['ArtistLink']);
	
	$script = $_SERVER['SCRIPT_NAME'];
	$button_format = '<a class="ui button" href="'.$script.'?%s">%s</a>';

	$query['sort_asc'] = $sort_asc;
	$query['sort_by'] = $sort_by < count($sort_by_names) - 3 ? $sort_by + 1 : 0;
	echo sprintf($button_format, http_build_query($query), $sort_by_names[$sort_by]);

	$query['sort_asc'] = !$sort_asc;
	$query['sort_by'] = $sort_by;
	echo sprintf($button_format, http_build_query($query), $sort_asc ? 'ASC' : 'DESC');
	echo '<br/>';
}


function artists_links_pages() {
	global $max_pages;
	$query = artist_links_query();
	$at_page = $query['at_page'];

	$script = $_SERVER['SCRIPT_NAME'];
	$button_format = '<a class="ui button" href="'.$script.'?%s">%s</a>';
	
	$query['at_page'] = max(0, $at_page - 1);
	echo sprintf($button_format, http_build_query($query), '<');
	echo sprintf('<div class="ui button"> %s </div>',$at_page);
	$query['at_page'] = min($max_pages - 1, $at_page + 1);
	echo sprintf($button_format, http_build_query($query), '>');
	echo '<br/>';
}




function artist_links() {
	global $max_pages, $records_per_page, $artists_columns;
	
	$sql_statement = 'select count(*) from artists';
	$result = sql_execute($sql_statement);
	$row_counts = $result->fetch()[0];
	$max_pages = ceil((float)$row_counts / $records_per_page);

	echo sprintf('<h3>Artists: %s records</h3>', $row_counts);
	artists_links_sorting();
	artists_links_pages();

	$sort_by_names = $artists_columns;
	unset($sort_by_names['Details']);
	unset($sort_by_names['ArtistLink']);
	
	
	
	$query = artist_links_query();
	$sort_direction = $query['sort_asc'] ? 'ASC' : 'DESC';
	$sort_by = $sort_by_names[$query['sort_by']];
	$sql_statement = artist_links_sql();

	$result = sql_execute($sql_statement);

	while( $row = $result->fetch() ) {
		$id = $row['ArtistID'];
		$query['id'] = $id;

		$name = $row['FirstName'].' '.$row['LastName'];
        	$classes = 'item';
        	if (isset($_GET['id']) && $_GET['id'] == $id)
          		$classes .= ' active';
        	$goto = $_SERVER['SCRIPT_NAME'].'?'.http_build_query($query);

        	$link = '<a href="%s" class="%s">%s</a>';
        	echo sprintf($link, $goto, $classes, $name);
	}
  }


?>