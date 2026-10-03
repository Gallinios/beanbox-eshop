<?php
/** @var string $title */
$base = $base ?? '';
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'BeanBox') ?> · BeanBox</title>
<link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head>
<body>
<header class="top">
  <div class="wrap">
    <a class="brand" href="<?= $base ?>index.php">☕ BeanBox</a>
    <nav>
      <a href="<?= $base ?>index.php">Προϊόντα</a>
      <a class="cart-link" href="<?= $base ?>cart.php">Καλάθι <span class="badge"><?= cart_count() ?></span></a>
    </nav>
  </div>
</header>
<main class="wrap">
<?php if ($msg = take_flash()): ?>
  <p class="flash"><?= e($msg) ?></p>
<?php endif; ?>
