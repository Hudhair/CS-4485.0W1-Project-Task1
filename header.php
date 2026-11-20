<?php

	function add_header($page) {
		echo '
	<div class="ui secondary segment">
         <h1>Art Gallery - '.$page.'</h1>
 
         <div class="ui small horizontal link list page-links">
          <a class="item" href="index.php">Home</a>
          <a class="item" href="artist.php">Artists</a>
          <a class="item" href="paintings.php">Paintings</a>
          <a class="item" href="genres.php">Genres</a>
          <a class="item" href="eras.php">Eras</a>
	  <a class="item" href="gallaries.php">Gallaries</a>
	  <a class="item" href="subjects.php">Subjects</a>
          <a class="item" href="art-painting-genres.php">Painting Genres</a>
	  <a class="item" href="art-painting-subjects.php">Painting Subjects</a>
         </div>
        </div>';
	}



?>