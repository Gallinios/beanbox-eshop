<?php
require __DIR__ . '/../includes/bootstrap.php';
$base = '../';

// --- Σύνδεση / αποσύνδεση διαχειριστή ---
if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    redirect('index.php');
}

if (empty($_SESSION['admin'])) {
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        if (password_verify($_POST['password'] ?? '', ADMIN_PASSWORD_HASH)) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            redirect('index.php');
        }
        $error = 'Λάθος κωδικός.';
    }
    $title = 'Διαχείριση';
    require __DIR__ . '/../includes/header.php'; ?>
    <form method="post" class="form narrow">
      <h1>Είσοδος διαχειριστή</h1>
      <?= csrf_field() ?>
      <label>Κωδικός <input type="password" name="password" required autofocus></label>
      <?php if ($error): ?><span class="err"><?= e($error) ?></span><?php endif; ?>
      <button class="btn">Είσοδος</button>
    </form>
    <?php require __DIR__ . '/../includes/footer.php';
    exit;
}

// --- Ενέργειες διαχειριστή ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $pdo = db();
    switch ($_POST['action'] ?? '') {
        case 'add_product':
            $name  = trim($_POST['name'] ?? '');
            $price = (float) str_replace(',', '.', $_POST['price'] ?? '0');
            if ($name === '' || $price <= 0) {
                flash('Συμπλήρωσε όνομα και τιμή.');
                break;
            }
            $color = preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#6b4f3a';
            $pdo->prepare('INSERT INTO products (name, category, description, price, stock, color) VALUES (?,?,?,?,?,?)')
                ->execute([$name, trim($_POST['category'] ?? 'Άλλο') ?: 'Άλλο', trim($_POST['description'] ?? ''),
                           $price, max(0, (int) ($_POST['stock'] ?? 0)), $color]);
            flash('Το προϊόν προστέθηκε.');
            break;

        case 'update_stock':
            $pdo->prepare('UPDATE products SET stock = ? WHERE id = ?')
                ->execute([max(0, (int) $_POST['stock']), (int) $_POST['id']]);
            flash('Το απόθεμα ενημερώθηκε.');
            break;

        case 'delete_product':
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([(int) $_POST['id']]);
            flash('Το προϊόν διαγράφηκε.');
            break;

        case 'order_status':
            $status = in_array($_POST['status'] ?? '', ['new', 'shipped', 'cancelled'], true) ? $_POST['status'] : 'new';
            $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, (int) $_POST['id']]);
            flash('Η κατάσταση της παραγγελίας άλλαξε.');
            break;
    }
    redirect('index.php');
}

$products = db()->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
$orders   = db()->query('SELECT * FROM orders ORDER BY id DESC LIMIT 50')->fetchAll();
$revenue  = (float) db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn();
$statusLabel = ['new' => 'Νέα', 'shipped' => 'Στάλθηκε', 'cancelled' => 'Ακυρώθηκε'];

$title = 'Διαχείριση';
require __DIR__ . '/../includes/header.php';
?>
<div class="admin-head">
  <h1>Διαχείριση</h1>
  <a href="?logout=1">Αποσύνδεση</a>
</div>

<div class="stats">
  <div><span>Προϊόντα</span><strong><?= count($products) ?></strong></div>
  <div><span>Παραγγελίες</span><strong><?= count($orders) ?></strong></div>
  <div><span>Έσοδα</span><strong><?= money($revenue) ?></strong></div>
</div>

<h2>Παραγγελίες</h2>
<?php if (!$orders): ?>
  <p class="empty">Δεν υπάρχουν παραγγελίες ακόμα.</p>
<?php else: ?>
<table class="cart">
  <thead><tr><th>#</th><th>Πελάτης</th><th>Σύνολο</th><th>Ημερομηνία</th><th>Κατάσταση</th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o): ?>
    <tr>
      <td><?= (int) $o['id'] ?></td>
      <td><?= e($o['customer']) ?><br><small class="muted"><?= e($o['email']) ?> · <?= e($o['phone']) ?></small></td>
      <td><?= money((float) $o['total']) ?></td>
      <td><?= e($o['created_at']) ?></td>
      <td>
        <form method="post" class="inline">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="order_status">
          <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
          <select name="status" onchange="this.form.submit()">
            <?php foreach ($statusLabel as $k => $label): ?>
              <option value="<?= $k ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<h2>Νέο προϊόν</h2>
<form method="post" class="form grid-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add_product">
  <label>Όνομα <input name="name" required></label>
  <label>Κατηγορία <input name="category" placeholder="π.χ. Καφές"></label>
  <label>Τιμή (€) <input name="price" inputmode="decimal" required></label>
  <label>Απόθεμα <input type="number" name="stock" value="10" min="0"></label>
  <label>Χρώμα <input type="color" name="color" value="#6b4f3a"></label>
  <label class="full">Περιγραφή <textarea name="description" rows="2"></textarea></label>
  <button class="btn">Προσθήκη</button>
</form>

<h2>Προϊόντα</h2>
<table class="cart">
  <thead><tr><th>Προϊόν</th><th>Τιμή</th><th>Απόθεμα</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($products as $p): ?>
    <tr>
      <td><?= e($p['name']) ?><br><small class="muted"><?= e($p['category']) ?></small></td>
      <td><?= money((float) $p['price']) ?></td>
      <td>
        <form method="post" class="inline">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_stock">
          <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
          <input type="number" name="stock" value="<?= (int) $p['stock'] ?>" min="0">
          <button class="btn small line">OK</button>
        </form>
      </td>
      <td>
        <form method="post" onsubmit="return confirm('Διαγραφή προϊόντος;')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete_product">
          <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
          <button class="link-danger">Διαγραφή</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
