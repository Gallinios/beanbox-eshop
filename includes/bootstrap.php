<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

session_start();

// Σύνδεση στη βάση με PDO (prepared statements => προστασία από SQL injection)
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = getenv('DB_DSN') ?: 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// Ασφαλής εμφάνιση κειμένου στο HTML (προστασία από XSS)
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' €';
}

// CSRF token: κάθε φόρμα POST στέλνει αυτό το κλειδί για να ξέρουμε ότι ήρθε από το site μας
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function check_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Μη έγκυρο αίτημα. Ανανεώστε τη σελίδα και δοκιμάστε ξανά.');
    }
}

// Μηνύματα που εμφανίζονται μία φορά μετά από ανακατεύθυνση
function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function take_flash(): ?string
{
    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// Καλάθι: αποθηκεύεται στο session ως [product_id => ποσότητα]
function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(cart());
}

// Φέρνει τα προϊόντα του καλαθιού από τη βάση, με τις τρέχουσες τιμές
function cart_items(): array
{
    $cart = cart();
    if (!$cart) {
        return [];
    }
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    $items = [];
    foreach ($stmt as $p) {
        $qty = min($cart[$p['id']], (int) $p['stock']);
        if ($qty < 1) {
            continue;
        }
        $p['qty'] = $qty;
        $p['line_total'] = $qty * (float) $p['price'];
        $items[] = $p;
    }
    return $items;
}

function cart_totals(array $items): array
{
    $subtotal = array_sum(array_column($items, 'line_total'));
    $shipping = ($subtotal >= FREE_SHIPPING_FROM || $subtotal == 0) ? 0.0 : SHIPPING_FEE;
    return ['subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping];
}

// Εικόνα προϊόντος χωρίς αρχείο: SVG με το αρχικό γράμμα και χρώμα
function product_image(array $p): string
{
    $letter = e(mb_substr($p['name'], 0, 1));
    $color = e($p['color'] ?: '#6b4f3a');
    return '<svg viewBox="0 0 200 150" role="img" aria-label="' . e($p['name']) . '">'
        . '<rect width="200" height="150" fill="' . $color . '"/>'
        . '<circle cx="100" cy="75" r="42" fill="rgba(255,255,255,.18)"/>'
        . '<text x="100" y="92" text-anchor="middle" font-size="48" font-weight="700" fill="#fff" font-family="Georgia,serif">' . $letter . '</text>'
        . '</svg>';
}
