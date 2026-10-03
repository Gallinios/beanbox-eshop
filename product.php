<?php
require __DIR__ . '/includes/bootstrap.php';

$stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([(int) ($_GET['id'] ?? 0)]);
$p = $stmt->fetch();

if (!$p) {
    http_response_code(404);
    $title = 'Δεν βρέθηκε';
    require __DIR__ . '/includes/header.php';
    echo '<p class="empty">Το προϊόν δεν βρέθηκε. <a href="index.php">Πίσω στα προϊόντα</a></p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$title = $p['name'];
require __DIR__ . '/includes/header.php';
?>
<p><a href="index.php">← Όλα τα προϊόντα</a></p>
<div class="detail">
  <div class="img"><?= product_image($p) ?></div>
  <div>
    <span class="cat"><?= e($p['category']) ?></span>
    <h1><?= e($p['name']) ?></h1>
    <p class="price big"><?= money((float) $p['price']) ?></p>
    <p><?= e($p['description']) ?></p>
    <?php if ($p['stock'] > 0): ?>
      <p class="muted">Διαθέσιμα: <?= (int) $p['stock'] ?></p>
      <form method="post" action="cart.php" class="inline">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
        <input type="hidden" name="from" value="product">
        <label>Ποσότητα <input type="number" name="qty" value="1" min="1" max="<?= (int) $p['stock'] ?>"></label>
        <button class="btn">Προσθήκη στο καλάθι</button>
      </form>
    <?php else: ?>
      <p class="out">Εξαντλήθηκε</p>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
