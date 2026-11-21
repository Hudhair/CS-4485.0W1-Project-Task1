<?php

require_once('config.php');
require_once('art-sql.php');

function artist_fetch_button() {
	
	echo  '<table class="ui" style="background: #888; width:100%%; border: 1px solid #999">
		<tr>
		 <td class="ui button">Search</td>
		 <td id="fetch_input" class="ui button" maxlength="100" contenteditable="true" oninput="fetch_query(this)">Input</td>
		</tr>
		<div id="fetch_suggest" class="ui vertical fluid menu" role="listbox" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:20;margin:0;max-height:240px;overflow-y:auto;box-shadow:0 3px 12px #0002"></div>
		</table>';            

}

?>

<script>

let fetch_input;
let fetch_search;
let timer;
let prefix;

function fetch_close() {
	if (fetch_suggest == null)
		return
	
	fetch_suggest.style.display = 'none';
	fetch_suggest.replaceChildren();
	fetch_input.setAttribute('aria-explanded', 'false');
}



function fetch_query(arg) {
	fetch_input = arg;
	if (fetch_suggest == null)
		fetch_suggest = document.getElementById('fetch_suggest');

	clearTimeout(timer);
	fetch_close();
	if (fetch_input.textContent == null)
		return;
	prefix = fetch_input.textContent.trim();
	if (!prefix) return;

	timer = setTimeout(fetch_query_delayed, 200);
}

function fetch_query_response (response) {
	if (!response.ok) throw new Error('Search unavailable');
	return response.json();
}

function fetch_query_catch() {
	if (fetch_input.textContent.trim() !== prefix) return;
	
	fetch_suggest.replaceChildren();
	const error = document.createElement('div');
	error.className = 'item';
	error.textContent = 'Could not load suggestions';
	fetch_suggest.appendChild(error);
	fetch_suggest.style.display = 'block';
}
function fetch_query_artists(artists) {
	if (fetch_input.textContent.trim() !== prefix) return;
	fetch_suggest.replaceChildren();

	if (artists.length === 0) {
		const empty = document.createElement('div');
		empty.className = 'item';
		empty.textContent = 'No matching artists';
		fetch_suggest.appendChild(empty);
	} else {
		artists.forEach(fetch_query_artist);
	}
	fetch_suggest.style.display = 'block';
	fetch_input.setAttribute('aria-explanded', 'true');

}
function fetch_query_artist(artist) {
	const link = document.createElement('a');
	link.className = 'item';

	link.href = 'artist.php?id=' + artist.id ;
	link.textContent = artist.LastName;
	link.setAttribute('role', 'option');
	fetch_suggest.appendChild(link);
}

function fetch_query_delayed() {
	url = 'artist-search.php?search='+prefix;
	r = fetch('artist-search.php?search='+prefix)
	.then(fetch_query_response)
	.then(fetch_query_artists)
	.catch(fetch_query_catch);	
}

document.addEventListener('DOMContentLoaded', function () {
	fetch_input = document.getElementById('fetch_input');
	fetch_suggest = document.getElementById('paintingSuggestions');
	

	fetch_input.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') fetch_close();
	}); // End of keydown
	document.addEventListener('click', function (event) {
		if (!fetch_input.contains(event.target) && !fetch_suggest.contains(event.target)) fetch_close();
	});
});

</script>