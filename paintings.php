<?php
require_once __DIR__ . '/painting-common.php';
$db = painting_db();
$id = painting_id();
$notice = $_SESSION['paintings_notice'] ?? '';
unset($_SESSION['paintings_notice']);
$list = $db->query('SELECT p.PaintingID, p.Title, p.YearOfWork, p.ImageFileName, a.FirstName, a.LastName FROM Paintings p LEFT JOIN Artists a ON a.ArtistID = p.ArtistID ORDER BY p.Title, p.PaintingID')->fetchAll();
$selected = $id !== null ? painting_find($db, $id) : null;
$detailsFields = [
    'PaintingID' => 'Painting ID', 'ArtistID' => 'Artist ID', 'GalleryID' => 'Gallery ID',
    'ShapeID' => 'Shape ID', 'YearOfWork' => 'Year of work', 'Width' => 'Width',
    'Height' => 'Height', 'Medium' => 'Medium', 'Cost' => 'Cost', 'MSRP' => 'MSRP',
    'AccessionNumber' => 'Accession number', 'CopyrightText' => 'Copyright',
    'Description' => 'Description', 'Excerpt' => 'Excerpt', 'MuseumLink' => 'Museum link',
    'GoogleLink' => 'Google link', 'GoogleDescription' => 'Google description', 'WikiLink' => 'Wiki link'
];
painting_header('Paintings');
?>
<?php if ($notice): ?><div class="ui positive message"><?= painting_h($notice) ?></div><?php endif; ?>
<div class="ui segment"><a class="ui primary button" href="painting-add.php">Add a Painting</a></div>
  <div class="ui segment">
  <div class="ui stackable grid">
    <aside class="four wide column">
      <div class="ui segment">
        <h3 class="ui dividing header">Paintings (<?= count($list) ?>)</h3>
        <div class="ui relaxed divided link list painting-list">
          <?php foreach ($list as $p): ?>
          <a class="item <?= $id === (int)$p['PaintingID'] ? 'active' : '' ?>" href="<?= painting_url($p['PaintingID']) ?>">
            <strong><?= painting_h($p['Title']) ?></strong>
            <div class="description">#<?= (int)$p['PaintingID'] ?> · <?= painting_h(trim(($p['FirstName'] ?? '') . ' ' . ($p['LastName'] ?? ''))) ?></div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </aside>
    <section class="twelve wide column">
      <?php if ($selected): ?>
      <div class="ui segment painting-detail">
        <h2 class="ui header"><?= painting_h($selected['Title']) ?></h2>
        <p><strong>Artist:</strong> <?= painting_h(trim(($selected['FirstName'] ?? '').' '.($selected['LastName'] ?? '')) ?: 'Not assigned') ?>
        <span> · <strong>Gallery:</strong> <?= painting_h($selected['GalleryName'] ?? 'Not assigned') ?></span></p>
        <?php $filename = (string)$selected['ImageFileName']; $path = __DIR__.'/images/art/works/square-medium/'.$filename.'.jpg'; ?>
        <?php if (preg_match('/^[a-zA-Z0-9_-]+$/D', $filename) && is_file($path)): ?>
          <img class="ui rounded image painting-image" alt="<?= painting_h($selected['Title']) ?>" src="images/art/works/square-medium/<?= rawurlencode($filename) ?>.jpg">
        <?php else: ?><p class="ui info message">No local image available for filename <?= painting_h($filename) ?>.</p><?php endif; ?>
        <table class="ui definition compact table"><tbody>
          <?php foreach ($detailsFields as $key => $label): ?>
          <tr><td><?= painting_h($label) ?></td><td><?= nl2br(painting_h($selected[$key])) ?></td></tr>
          <?php endforeach; ?>
        </tbody></table>
        <a class="ui primary button" href="painting-edit.php?id=<?= (int)$id ?>">Edit Painting</a>
        <a class="ui red button" href="painting-delete.php?id=<?= (int)$id ?>">Delete Painting</a>
        <p><small>Deletion is blocked while painting-genre or painting-subject references exist.</small></p>
      </div>
      <?php elseif ($id !== null): ?>
      <div class="ui warning message">Painting #<?= (int)$id ?> does not exist.</div>
      <?php else: ?>
      <div class="ui segment"><h2 class="ui header">Select a painting</h2><p>Choose a painting on the left to view its complete details, edit or delete it, or use Add a Painting.</p></div>
      <?php endif; ?>
    </section>
  </div>
  </div>
<?php painting_footer(); ?>
