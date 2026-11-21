<?php

require_once('config.php');
require_once('art-sql.php');

header('Content-Type: application/json; charset=utf-8');

$search = $_GET['search'] ?? '';
$prefix = trim((string) $search);
if ($prefix === '') {
	echo json_encode([]);
	exit();
}

// limit chars
$rpefix = substr($prefix, 0, 100);

$sql_format = 'SELECT * FROM Artists where LastName REGEXP "^%s" Order by LastName ASC, FirstName asc, ArtistID asc Limit 10;';
$sql_statement = sprintf($sql_format, $prefix);

$result = sql_execute($sql_statement);
$search_results = [];
foreach ($result->fetchAll() as $row) {
	$search_results[] = ['id' => (int)$row['ArtistID'], 'LastName' => $row['LastName'] ];
}
echo json_encode($search_results, JSON_INVALID_UTF8_SUBSTITUTE)

?>