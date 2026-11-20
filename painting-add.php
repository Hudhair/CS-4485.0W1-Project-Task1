<?php
require_once __DIR__ . '/painting-common.php';
$db = painting_db();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        painting_require_csrf();
        $id = painting_save($db, $_POST, true);
        $_SESSION['paintings_notice'] = 'Painting added successfully.';
        header('Location: ' . painting_url($id)); exit;
    } catch (Throwable $e) { $error = painting_error($e); }
}
painting_header('Add Painting');
?>
<?php if ($error): ?><div class="ui negative message"><?= painting_h($error) ?></div><?php endif; ?>
<div class="ui segment"><h2 class="ui header">Add a New Painting</h2>
<?php painting_form($db, [], false, null, $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : []); ?>
</div>
<?php painting_footer(); ?>
