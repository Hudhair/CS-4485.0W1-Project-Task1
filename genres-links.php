<?php 

require_once('config.php');
require_once('art-sql.php');

$max_pages = 0;
$records_per_page = 10;

$query = [];

function genres_links_query() {
	global $query;
	
	if (!empty($query))
		return $query;

	$str_query = $_SERVER['QUERY_STRING'];
	parse_str($str_query, $query);
	
	if (isset($_GET['id']) )
		$query['id'] = $_GET['id'];
	if (!isset($_GET['sort_asc']) )
		$query['sort_asc'] = true;
	if (!isset($_GET['sort_name']) )
		$query['sort_name'] = true;
	if (!isset($_GET['at_page']) )
		$query['at_page'] = 0;

	return $query;
}

function genres_links_sql() {
	global $records_per_page;

	$query = genres_links_query();
	$sort_by = $query['sort_name'] ? 'GenreName' : 'GenreID';
	$sort_direction = $query['sort_asc'] ? 'ASC' : 'DESC';
	$min = $query['at_page'] * $records_per_page;
	$max = $min + $records_per_page;

	$sql_statement = sprintf('select * from genres order by %s %s limit '.$min.', '.$max.';', $sort_by, $sort_direction);
	return $sql_statement;
}

function genres_links_sorting() {
	$query = genres_links_query();
	$sort_name = $query['sort_name'];
	$sort_asc = $query['sort_asc'];
	
	$script = $_SERVER['SCRIPT_NAME'];
	$button_format = '<a class="ui button" href="'.$script.'?%s">%s</a>';

	$query['sort_asc'] = $sort_asc;
	$query['sort_name'] = !$sort_name;
	echo sprintf($button_format, http_build_query($query), $sort_name ? 'NAME': 'ID' );

	$query['sort_asc'] = !$sort_asc;
	$query['sort_name'] = $sort_name;
	echo sprintf($button_format, http_build_query($query), $sort_asc ? 'ASC' : 'DESC');
	echo '<br/>';
	
	$query['sort_asc'] = $sort_asc;
}

function genres_links_pages() {
	global $max_pages;
	$query = genres_links_query();
	$at_page = $query['at_page'];

	$script = $_SERVER['SCRIPT_NAME'];
	$button_format = '<a class="ui button" href="'.$script.'?%s">%s</a>';
	
	$query['at_page'] = max(0, $at_page - 1);
	echo sprintf($button_format, http_build_query($query), '<');
	echo sprintf('<div class="ui button"> %s </div>',$at_page);
	$query['at_page'] = min($max_pages - 1, $at_page + 1);
	echo sprintf($button_format, http_build_query($query), '>');
	echo '<br/>';
	$query['at_page'] = $at_page;
}

function genres_links() {
	global $max_pages, $records_per_page;
	
	$query = genres_links_query();
	
	$sql_statement = 'select count(*) from genres';
	$result = sql_execute($sql_statement);
	$row_counts = $result->fetch()[0];
	$max_pages = ceil((float)$row_counts / $records_per_page);
	
	echo sprintf('<h3>Genres: %s records</h3>', $row_counts);
	genres_links_sorting();
	genres_links_pages();
	
	
	$sql_statement = genres_links_sql();
	$script = $_SERVER['SCRIPT_NAME'];

	$result = sql_execute($sql_statement);
	while ($row = $result->fetch()) {
            $query['id'] = $row['GenreID'];
            $goto = $_SERVER['SCRIPT_NAME'].'?'.http_build_query($query);
            $classes = 'item';
            if (isset($_GET['id']) && $_GET['id'] == $query['id'])
              $classes .= ' active';
            $content = $row['GenreName'];

            $format = '<a href="%s" class="%s">%s</a>';
            echo sprintf($format, $goto, $classes, $content);
         }

}




?>