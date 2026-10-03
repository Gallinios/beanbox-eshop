# BeanBox – Mini e-shop σε PHP & MySQL

Μικρό ηλεκτρονικό κατάστημα καφέ, γραμμένο σε **καθαρή PHP (χωρίς framework)** και **MySQL**.

## Λειτουργίες

- Λίστα προϊόντων με **αναζήτηση** και **φίλτρο κατηγορίας**
- Σελίδα προϊόντος με απόθεμα
- **Καλάθι** (session): προσθήκη, αλλαγή ποσότητας, αφαίρεση
- **Checkout** με έλεγχο πεδίων στον server, δωρεάν μεταφορικά πάνω από 40 €
- Αποθήκευση παραγγελίας με **transaction** και μείωση αποθέματος
- **Panel διαχειριστή**: παραγγελίες και κατάσταση, προσθήκη/διαγραφή προϊόντων, απόθεμα, έσοδα

## Ασφάλεια

- **PDO prepared statements** (προστασία από SQL injection)
- `htmlspecialchars` σε κάθε έξοδο (προστασία από XSS)
- **CSRF token** σε όλες τις φόρμες POST
- Κωδικός διαχειριστή με `password_hash` / `password_verify`
- Οι τιμές υπολογίζονται πάντα από τη βάση, όχι από τον browser

## Τεχνολογίες

PHP 8, MySQL / MariaDB, HTML5, CSS, Docker

## Εκτέλεση με XAMPP

1. Αντέγραψε τον φάκελο στο `C:\xampp\htdocs\beanbox`
2. Ξεκίνα **Apache** και **MySQL** από το XAMPP Control Panel
3. Άνοιξε το `http://localhost/phpmyadmin`, πάτα **Εισαγωγή (Import)** και διάλεξε το `database.sql`
4. Άνοιξε το `http://localhost/beanbox`

## Εκτέλεση με Docker

```bash
docker compose up --build
```

Μετά άνοιξε το `http://localhost:8080`

## Διαχείριση

`/admin` · κωδικός demo: `admin123` (άλλαξέ τον στο `includes/config.php`)

## Δομή

```
index.php        λίστα προϊόντων
product.php      σελίδα προϊόντος
cart.php         καλάθι
checkout.php     ολοκλήρωση παραγγελίας
admin/index.php  διαχείριση
includes/        ρυθμίσεις, βοηθητικές συναρτήσεις, header/footer
assets/style.css εμφάνιση
database.sql     πίνακες και δείγματα προϊόντων
```

## Δημιουργός

Dimitris Tsikalas · [github.com/Gallinios](https://github.com/Gallinios)
