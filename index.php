<?php
require __DIR__ . '/includes/bootstrap.php';

$q   = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');

$sql = 'SELECT * FROM products WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (name LIKE ? OR description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($cat !== '') {
    $sql .= ' AND category = ?';
    $params[] = $cat;
}
$sql .= ' ORDER BY category, name';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = db()->query('SELECT DISTINCT category FROM products ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

$title = 'Προϊόντα';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>Φρεσκοκαβουρδισμένος καφές, στην πόρτα σου</h1>
  <p>Δωρεάν μεταφορικά για παραγγελίες από <?= money(FREE_SHIPPING_FROM) ?>.</p>
</section>

<form class="filters" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Αναζήτηση προϊόντος...">
  <select name="cat">
    <option value="">Όλες οι κατηγορίες</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= e($c) ?>" <?= $c === $cat ? 'selected' : '' ?>><?= e($c) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn">Φίλτρο</button>
</form>

<?php if (!$products): ?>
  <p class="empty">Δεν βρέθηκαν προϊόντα.</p>
<?php else: ?>
<div class="grid">
  <?php foreach ($products as $p): ?>
    <article class="card">
      <a href="product.php?id=<?= (int) $p['id'] ?>" class="img"><?= product_image($p) ?></a>
      <div class="body">
        <span class="cat"><?= e($p['category']) ?></span>
        <h2><a href="product.php?id=<?= (int) $p['id'] ?>"><?= e($p['name']) ?></a></h2>
        <div class="row">
          <strong class="price"><?= money((float) $p['price']) ?></strong>
          <?php if ($p['stock'] > 0): ?>
            <form method="post" action="cart.php">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button class="btn small">Στο καλάθι</button>
            </form>
          <?php else: ?>
            <span class="out">Εξαντλήθηκε</span>
          <?php endif; ?>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
