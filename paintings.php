<?php
require_once __DIR__ . '/painting-common.php';
$db = painting_db();
$id = painting_id();
$notice = $_SESSION['paintings_notice'] ?? '';
unset($_SESSION['paintings_notice']);
// sort methods by title/artist/year/gallery/medium
$sortOptions = [
    'title' => ['label' => 'Painting title', 'sql' => 'p.Title'],
    'artist' => ['label' => 'Artist name', 'sql' => 'a.LastName, a.FirstName'],
    'year' => ['label' => 'Year of work', 'sql' => 'p.YearOfWork'],
    'gallery' => ['label' => 'Gallery name', 'sql' => 'g.GalleryName'],
    'medium' => ['label' => 'Medium', 'sql' => 'p.Medium']
];
$sort = (string)($_GET['sort'] ?? 'title');
if (!array_key_exists($sort, $sortOptions)) $sort = 'title';
$direction = strtoupper((string)($_GET['direction'] ?? 'ASC'));
if (!in_array($direction, ['ASC', 'DESC'], true)) $direction = 'ASC';

$search = trim((string)($_GET['q'] ?? ''));
$search = substr($search, 0, 100);
$orderBy = implode(', ', array_map(
    static fn($column) => $column . ' ' . $direction,
    explode(', ', $sortOptions[$sort]['sql'])
));
// search painting title
$sql = 'SELECT p.PaintingID, p.Title, p.YearOfWork, p.ImageFileName, '
    . 'a.FirstName, a.LastName FROM Paintings p '
    . 'LEFT JOIN Artists a ON a.ArtistID = p.ArtistID '
    . 'LEFT JOIN Galleries g ON g.GalleryID = p.GalleryID ';
if ($search !== '') {
    $sql .= "WHERE p.Title LIKE ? ESCAPE '!' ";
}
$sql .= 'ORDER BY ' . $orderBy . ', p.PaintingID ASC';
$stmt = $db->prepare($sql);
if ($search !== '') {
    $escapedSearch = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);
    $stmt->execute([$escapedSearch . '%']);
} else {
    $stmt->execute();
}
$list = $stmt->fetchAll();
$totalPaintings = count($list);
function painting_sorted_url($paintingId, $sort, $direction, $search) {
    return 'paintings.php?' . http_build_query([
        'id' => (int)$paintingId, 'sort' => $sort, 'direction' => $direction, 'q' => $search
    ]);
}
$selected = $id !== null ? painting_find($db, $id) : null;
// only show fields users need to see, not things like painting id's etc.
function painting_external_link($value) {
    $url = trim((string)($value ?? ''));
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) return null;
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $url : null;
}
painting_header('Paintings');
?>
<?php if ($notice): ?><div class="ui positive message"><?= painting_h($notice) ?></div><?php endif; ?>
<div class="ui segment">
  <div class="ui stackable grid middle aligned">
    <div class="sixteen wide column">
      <form class="ui form" method="get" action="paintings.php">
        <?php if ($id !== null): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
        <?php if ($search !== ''): ?><input type="hidden" name="q" value="<?= painting_h($search) ?>"><?php endif; ?>
        <div class="fields" style="align-items:end">
          <div class="seven wide field">
            <label for="sort">Sort paintings by</label>
            <select id="sort" name="sort">
              <?php foreach ($sortOptions as $key => $option): ?>
                <option value="<?= painting_h($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= painting_h($option['label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="five wide field">
            <label for="direction">Order</label>
            <select id="direction" name="direction">
              <option value="ASC" <?= $direction === 'ASC' ? 'selected' : '' ?>>Ascending</option>
              <option value="DESC" <?= $direction === 'DESC' ? 'selected' : '' ?>>Descending</option>
            </select>
          </div>
          <div class="four wide field"><button class="ui primary fluid button" type="submit">Apply Sort</button></div>
        </div>
      </form>
      <a class="ui button" href="painting-add.php">Add a Painting</a>
    </div>
  </div>
</div>
  <div class="ui segment">
  <div class="ui stackable grid">
    <aside class="four wide column">
      <div class="ui segment">
        <h3 class="ui dividing header">Paintings (<?= $totalPaintings ?>)</h3>
        <!-- submit search to filter paintings -->
        <form class="ui form" method="get" action="paintings.php" id="paintingSearchForm" style="margin-bottom:1rem">
          <input type="hidden" name="sort" value="<?= painting_h($sort) ?>">
          <input type="hidden" name="direction" value="<?= painting_h($direction) ?>">
          <div class="field" style="position:relative">
            <label for="paintingSearch">Search by painting title</label>
            <input type="search" id="paintingSearch" name="q" value="<?= painting_h($search) ?>" placeholder="Start typing a title..." autocomplete="off" aria-controls="paintingSuggestions" aria-expanded="false">
            <div id="paintingSuggestions" class="ui vertical fluid menu" role="listbox" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:20;margin:0;max-height:240px;overflow-y:auto;box-shadow:0 3px 12px #0002"></div>
          </div>
          <button class="ui primary fluid button" type="submit"><i class="search icon"></i>Search</button>
          <?php if ($search !== ''): ?>
            <a class="ui basic fluid button" style="margin-top:0.5rem" href="paintings.php?<?= painting_h(http_build_query(['sort' => $sort, 'direction' => $direction])) ?>">Clear Search</a>
          <?php endif; ?>
        </form>
        <?php if ($search !== ''): ?>
          <p class="ui small text">Showing <?= count($list) ?> painting<?= count($list) === 1 ? '' : 's' ?> starting with "<?= painting_h($search) ?>".</p>
        <?php endif; ?>
        <div class="ui relaxed divided link list painting-list">
          <?php foreach ($list as $p): ?>
          <a class="item <?= $id === (int)$p['PaintingID'] ? 'active' : '' ?>" href="<?= painting_h(painting_sorted_url($p['PaintingID'], $sort, $direction, $search)) ?>">
            <strong><?= painting_h($p['Title']) ?></strong>
            <div class="description"><?= painting_h(trim(($p['FirstName'] ?? '') . ' ' . ($p['LastName'] ?? '')) ?: 'Unknown artist') ?><?php if ($p['YearOfWork'] !== null && $p['YearOfWork'] !== ''): ?> · <?= painting_h($p['YearOfWork']) ?><?php endif; ?></div>
          </a>
          <?php endforeach; ?>
          <?php if (!$list): ?><p class="ui message">No matching paintings found.</p><?php endif; ?>
        </div>
        <button id="loadMorePaintings" class="ui fluid button" type="button" hidden>Load More</button>
      </div>
    </aside>
    <section class="twelve wide column">
      <?php if ($selected): ?>
      <div class="ui segment painting-detail">
        <?php
          $artistName = trim(($selected['FirstName'] ?? '') . ' ' . ($selected['LastName'] ?? '')) ?: 'Unknown artist';
          $year = $selected['YearOfWork'] ?? null;
          $width = $selected['Width'] ?? null;
          $height = $selected['Height'] ?? null;
          $museumLink = painting_external_link($selected['MuseumLink'] ?? null);
          $wikiLink = painting_external_link($selected['WikiLink'] ?? null);
          $filename = (string)($selected['ImageFileName'] ?? '');
          $path = __DIR__ . '/images/art/works/square-medium/' . $filename . '.jpg';
        ?>
        <h2 class="ui header"><?= painting_h($selected['Title']) ?></h2>
        <p class="ui sub header"><?= painting_h($artistName) ?><?php if ($year !== null && $year !== ''): ?> · <?= painting_h($year) ?><?php endif; ?></p>
        <?php if ($filename !== '' && preg_match('/^[a-zA-Z0-9_-]+$/D', $filename) && is_file($path)): ?>
          <img class="ui rounded image painting-image" alt="<?= painting_h($selected['Title']) ?>" src="images/art/works/square-medium/<?= rawurlencode($filename) ?>.jpg">
        <?php else: ?>
          <p class="ui info message">No painting image available.</p>
        <?php endif; ?>
        <h3 class="ui dividing header">Artwork Details</h3>
        <table class="ui definition compact table"><tbody>
          <tr><td>Artist</td><td><?= painting_h($artistName) ?></td></tr>
          <?php if ($year !== null && $year !== ''): ?>
          <tr><td>Year</td><td><?= painting_h($year) ?></td></tr>
          <?php endif; ?>
          <?php if (trim((string)($selected['Medium'] ?? '')) !== ''): ?>
          <tr><td>Medium</td><td><?= painting_h($selected['Medium']) ?></td></tr>
          <?php endif; ?>
          <?php if (($width !== null && $width !== '') || ($height !== null && $height !== '')): ?>
          <tr><td>Dimensions</td><td><?= $width !== null && $width !== '' ? painting_h($width) : 'Unknown' ?> × <?= $height !== null && $height !== '' ? painting_h($height) : 'Unknown' ?> (units not specified)</td></tr>
          <?php endif; ?>
          <?php if (trim((string)($selected['GalleryName'] ?? '')) !== ''): ?>
          <tr><td>Gallery</td><td><?= painting_h($selected['GalleryName']) ?></td></tr>
          <?php endif; ?>
          <?php if (trim((string)($selected['AccessionNumber'] ?? '')) !== ''): ?>
          <tr><td>Accession Number</td><td><?= painting_h($selected['AccessionNumber']) ?></td></tr>
          <?php endif; ?>
        </tbody></table>
        <?php if (trim((string)($selected['Description'] ?? '')) !== ''): ?>
        <h3 class="ui dividing header">Description</h3>
        <p><?= nl2br(painting_h($selected['Description'])) ?></p>
        <?php endif; ?>
        <?php if ($museumLink || $wikiLink): ?>
        <div class="ui buttons">
          <?php if ($museumLink): ?><a class="ui button" href="<?= painting_h($museumLink) ?>" target="_blank" rel="noopener noreferrer">Museum Website</a><?php endif; ?>
          <?php if ($wikiLink): ?><a class="ui button" href="<?= painting_h($wikiLink) ?>" target="_blank" rel="noopener noreferrer">Wikipedia</a><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (trim((string)($selected['CopyrightText'] ?? '')) !== ''): ?>
        <p><small><?= painting_h($selected['CopyrightText']) ?></small></p>
        <?php endif; ?>
        <div class="ui divider"></div>
        <a class="ui primary button" href="painting-edit.php?id=<?= (int)$id ?>">Edit Painting</a>
        <a class="ui red button" href="painting-delete.php?id=<?= (int)$id ?>">Delete Painting</a>
        <p><small>Deletion is blocked while painting genre or painting-subject references exist.</small></p>
      </div>
      <?php elseif ($id !== null): ?>
      <div class="ui warning message">Painting #<?= (int)$id ?> does not exist.</div>
      <?php else: ?>
      <div class="ui segment"><h2 class="ui header">Select a painting</h2><p>Choose a painting on the left to view its artwork details, edit or delete it, or use Add a Painting.</p></div>
      <?php endif; ?>
    </section>
  </div>
  </div>
<script>
// get painting tile suggestions as user types
document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('paintingSearch');
  const menu = document.getElementById('paintingSuggestions');
  let timer;

  function closeSuggestions() {
    menu.style.display = 'none';
    menu.replaceChildren();
    input.setAttribute('aria-expanded', 'false');
  }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    closeSuggestions();
    const prefix = input.value.trim();
    if (!prefix) return;

    // added delay so it doesnt search on every character typed
    timer = setTimeout(function () {
      fetch('painting-search.php?q=' + encodeURIComponent(prefix), {
        headers: { 'Accept': 'application/json' }
      })
        .then(function (response) {
          if (!response.ok) throw new Error('Search unavailable');
          return response.json();
        })
        .then(function (paintings) {
          // ignore old results if user typed something else
          if (input.value.trim() !== prefix) return;
          menu.replaceChildren();
          if (paintings.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'item';
            empty.textContent = 'No matching paintings';
            menu.appendChild(empty);
          } else {
            paintings.forEach(function (painting) {
              const link = document.createElement('a');
              link.className = 'item';
              const params = new URLSearchParams({
                id: String(painting.id),
                sort: <?= json_encode($sort) ?>,
                direction: <?= json_encode($direction) ?>,
                q: prefix
              });
              link.href = 'paintings.php?' + params.toString();
              link.textContent = painting.title;
              link.setAttribute('role', 'option');
              menu.appendChild(link);
            });
          }
          menu.style.display = 'block';
          input.setAttribute('aria-expanded', 'true');
        })
        .catch(function () {
          if (input.value.trim() !== prefix) return;
          menu.replaceChildren();
          const error = document.createElement('div');
          error.className = 'item';
          error.textContent = 'Could not load suggestions';
          menu.appendChild(error);
          menu.style.display = 'block';
        });
    }, 200);
  });

  input.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeSuggestions();
  });
  document.addEventListener('click', function (event) {
    if (!input.contains(event.target) && !menu.contains(event.target)) closeSuggestions();
  });
});

// show 12 paintings at a time, loading 12 more if load more is clicked
document.addEventListener('DOMContentLoaded', function () {
  const paintings = Array.from(document.querySelectorAll('.painting-list > .item'));
  const loadMoreButton = document.getElementById('loadMorePaintings');
  const batchSize = 12;
  let visibleCount = batchSize;

  if (!loadMoreButton || paintings.length <= batchSize) return;

  function updateList() {
    paintings.forEach(function (painting, index) {
      painting.style.display = index < visibleCount ? '' : 'none';
    });
    loadMoreButton.hidden = visibleCount >= paintings.length;
  }

  loadMoreButton.addEventListener('click', function () {
    visibleCount += batchSize;
    updateList();
  });
  updateList();
});
</script>
<?php painting_footer(); ?>
