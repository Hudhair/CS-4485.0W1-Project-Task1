<?php
  // Create a connection to the database
  // Or return an already created pdo
  $pdo = NULL;
  function sql_connect() {
    global $pdo;
    if (isset($pdo) and $pdo != NULL)
      return $pdo;
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  }
  // Run an sql statement
  function sql_execute($statement) {
	try {
		$pdo = sql_connect();
		return $pdo->query($statement);
	}  catch (PDOException $e)
	{ die($e->getMessage()); }
	return NULL;
 }

  $artists_columns = ['ArtistID', 'FirstName', 'LastName', 'Nationality', 'Gender', 'YearOfBirth', 'YearOfDeath', 'Details', 'ArtistLink'];
  $genres_columns = ['GenreID', 'GenreName', 'Description', 'Link'];


  // Get Artist
  function sql_get_artist($artist_id) {
	$sql_statement = 'select * from artists where ArtistID='.$artist_id.';';
	$result = sql_execute($sql_statement);
	return $result->fetch();
  }
  // Get Genre
  function sql_get_genre($genre_id) {
	$sql_statement = 'select * from genres where GenreID='.$genre_id.';';
	$result = sql_execute($sql_statement);
	return $result->fetch();
  }


?>