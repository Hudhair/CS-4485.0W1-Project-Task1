<?php 

require_once('config.php');
require_once('art-sql.php');


function genres_links() {
	$sql_statement = 'select count(*) from genres';
	$result = sql_execute($sql_statement);
	$row_counts = $result->fetch()[0];
	
	echo sprintf('<h3>Genres: %s records</h3>', $row_counts);
	$script = $_SERVER['SCRIPT_NAME'];
	$button_format = '<a class="ui button" href="'.$script.'?%s">%s</a>';

	$sort_asc = true;
	$sort_name = true;

	$query = [];
	if (isset($_GET['id']) )
		$query['id'] = $_GET['id'];
	if (isset($_GET['sort_asc']) )
		$sort_asc = $_GET['sort_asc'];
	if (isset($_GET['sort_name']) )
		$sort_name = $_GET['sort_name'];
	
	$query['sort_asc'] = $sort_asc;
	$query['sort_name'] = !$sort_name;
	echo sprintf($button_format, http_build_query($query), $sort_name ? 'NAME': 'ID' );

	$query['sort_asc'] = !$sort_asc;
	$query['sort_name'] = $sort_name;
	echo sprintf($button_format, http_build_query($query), $sort_asc ? 'ASC' : 'DESC');
	
	$sort_by = $sort_name ? 'GenreName' : 'GenreID';
	$sort_direction = $sort_asc ? 'ASC' : 'DESC';
	$sql_statement = "select * from genres order by ".$sort_by." ".$sort_direction.';';
	
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




?>