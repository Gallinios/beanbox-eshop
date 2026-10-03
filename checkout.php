<?php
require __DIR__ . '/includes/bootstrap.php';

$items = cart_items();
if (!$items && !isset($_GET['done'])) {
    redirect('cart.php');
}

$errors = [];
$data = ['customer' => '', 'email' => '', 'phone' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach ($data as $k => $_) {
        $data[$k] = trim($_POST[$k] ?? '');
    }

    // Έλεγχος πεδίων στον server (ποτέ μην εμπιστεύεσαι μόνο τον browser)
    if (mb_strlen($data['customer']) < 3)                    $errors['customer'] = 'Γράψε ονοματεπώνυμο.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))  $errors['email']    = 'Μη έγκυρο email.';
    if (strlen(preg_replace('/\D/', '', $data['phone'])) < 10) $errors['phone']  = 'Το τηλέφωνο θέλει 10 ψηφία.';
    if (mb_strlen($data['address']) < 5)                     $errors['address']  = 'Γράψε διεύθυνση αποστολής.';

    if (!$errors) {
        $totals = cart_totals($items);
        $pdo = db();
        // Transaction: είτε αποθηκεύονται όλα (παραγγελία, είδη, απόθεμα) είτε τίποτα
        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO orders (customer, email, phone, address, subtotal, shipping, total) VALUES (?,?,?,?,?,?,?)')
                ->execute([$data['customer'], $data['email'], $data['phone'], $data['address'],
                           $totals['subtotal'], $totals['shipping'], $totals['total']]);
            $orderId = (int) $pdo->lastInsertId();

            $addItem   = $pdo->prepare('INSERT INTO order_items (order_id, product_id, name, price, qty) VALUES (?,?,?,?,?)');
            $takeStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($items as $it) {
                $takeStock->execute([$it['qty'], $it['id'], $it['qty']]);
                if ($takeStock->rowCount() !== 1) {
                    throw new RuntimeException('Δεν υπάρχει αρκετό απόθεμα για «' . $it['name'] . '».');
                }
                $addItem->execute([$orderId, $it['id'], $it['name'], $it['price'], $it['qty']]);
            }
            $pdo->commit();

            unset($_SESSION['cart']);
            $_SESSION['last_order'] = $orderId;
            redirect('checkout.php?done=1');
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            $errors['stock'] = $ex->getMessage();
        }
    }
}

$title = 'Ολοκλήρωση';
require __DIR__ . '/includes/header.php';

if (isset($_GET['done'])): ?>
  <section class="done">
    <h1>Ευχαριστούμε! 🎉</h1>
    <p>Η παραγγελία <strong>#<?= (int) ($_SESSION['last_order'] ?? 0) ?></strong> καταχωρήθηκε.</p>
    <p class="muted">Αυτό είναι demo: δεν γίνεται χρέωση ούτε αποστολή.</p>
    <a class="btn" href="index.php">Πίσω στο κατάστημα</a>
  </section>
<?php else:
  $totals = cart_totals($items); ?>
  <h1>Στοιχεία αποστολής</h1>
  <?php if (isset($errors['stock'])): ?><p class="error-box"><?= e($errors['stock']) ?></p><?php endif; ?>
  <div class="checkout">
    <form method="post" class="form" novalidate>
      <?= csrf_field() ?>
      <?php
      $fields = ['customer' => ['Ονοματεπώνυμο', 'text'], 'email' => ['Email', 'email'],
                 'phone' => ['Τηλέφωνο', 'tel'], 'address' => ['Διεύθυνση', 'text']];
      foreach ($fields as $name => [$label, $type]): ?>
        <label><?= $label ?>
          <input type="<?= $type ?>" name="<?= $name ?>" value="<?= e($data[$name]) ?>" required>
          <?php if (isset($errors[$name])): ?><span class="err"><?= e($errors[$name]) ?></span><?php endif; ?>
        </label>
      <?php endforeach; ?>
      <button class="btn">Υποβολή παραγγελίας</button>
    </form>
    <aside class="summary">
      <h2>Σύνοψη</h2>
      <?php foreach ($items as $it): ?>
        <p><?= (int) $it['qty'] ?> × <?= e($it['name']) ?> <span><?= money($it['line_total']) ?></span></p>
      <?php endforeach; ?>
      <p>Μεταφορικά <span><?= $totals['shipping'] ? money($totals['shipping']) : 'Δωρεάν' ?></span></p>
      <p class="grand">Σύνολο <span><?= money($totals['total']) ?></span></p>
    </aside>
  </div>
<?php endif;

require __DIR__ . '/includes/footer.php';
