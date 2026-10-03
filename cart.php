<?php
require __DIR__ . '/includes/bootstrap.php';

// Ενέργειες καλαθιού (add / update / remove) γίνονται μόνο με POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id  = (int) ($_POST['id'] ?? 0);
    $qty = max(1, (int) ($_POST['qty'] ?? 1));

    $stmt = db()->prepare('SELECT id, name, stock FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    switch ($_POST['action'] ?? '') {
        case 'add':
            if ($product && $product['stock'] > 0) {
                $new = ($_SESSION['cart'][$id] ?? 0) + $qty;
                $_SESSION['cart'][$id] = min($new, (int) $product['stock']);
                flash('Το «' . $product['name'] . '» μπήκε στο καλάθι.');
            }
            redirect(isset($_POST['from']) && $_POST['from'] === 'product' ? 'product.php?id=' . $id : 'index.php');

        case 'update':
            if ($product) {
                $_SESSION['cart'][$id] = min($qty, (int) $product['stock']);
            }
            redirect('cart.php');

        case 'remove':
            unset($_SESSION['cart'][$id]);
            flash('Το προϊόν αφαιρέθηκε.');
            redirect('cart.php');
    }
    redirect('cart.php');
}

$items  = cart_items();
$totals = cart_totals($items);

$title = 'Καλάθι';
require __DIR__ . '/includes/header.php';
?>
<h1>Το καλάθι σου</h1>

<?php if (!$items): ?>
  <p class="empty">Το καλάθι είναι άδειο. <a href="index.php">Δες τα προϊόντα</a></p>
<?php else: ?>
<table class="cart">
  <thead><tr><th>Προϊόν</th><th>Τιμή</th><th>Ποσότητα</th><th>Σύνολο</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($items as $it): ?>
    <tr>
      <td><a href="product.php?id=<?= (int) $it['id'] ?>"><?= e($it['name']) ?></a></td>
      <td><?= money((float) $it['price']) ?></td>
      <td>
        <form method="post" class="inline">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
          <input type="number" name="qty" value="<?= (int) $it['qty'] ?>" min="1" max="<?= (int) $it['stock'] ?>">
          <button class="btn small line">Ενημέρωση</button>
        </form>
      </td>
      <td><?= money($it['line_total']) ?></td>
      <td>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="remove">
          <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
          <button class="link-danger" aria-label="Αφαίρεση">✕</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<div class="totals">
  <p>Υποσύνολο: <strong><?= money($totals['subtotal']) ?></strong></p>
  <p>Μεταφορικά: <strong><?= $totals['shipping'] ? money($totals['shipping']) : 'Δωρεάν' ?></strong></p>
  <p class="grand">Σύνολο: <strong><?= money($totals['total']) ?></strong></p>
  <a class="btn" href="checkout.php">Ολοκλήρωση παραγγελίας</a>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
