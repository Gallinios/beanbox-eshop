<?php
// Ρυθμίσεις βάσης. Στο XAMPP ο χρήστης είναι "root" χωρίς κωδικό.
// Στο Docker οι τιμές έρχονται από το docker-compose.yml.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'beanbox');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Κωδικός διαχειριστή: admin123 (άλλαξέ τον με: php -r 'echo password_hash("νέος", PASSWORD_DEFAULT);')
define('ADMIN_PASSWORD_HASH', '$2y$12$BMAJFg0EZxb7cDIVFtbdo.7X.HgdTNHJnZ7lvRl6cwlZimDsLK062');

define('SHIPPING_FEE', 3.50);
define('FREE_SHIPPING_FROM', 40.00);
