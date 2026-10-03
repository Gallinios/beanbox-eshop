-- BeanBox mini e-shop: βάση δεδομένων (MySQL / MariaDB)
CREATE DATABASE IF NOT EXISTS beanbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE beanbox;

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)  NOT NULL,
    category    VARCHAR(60)   NOT NULL,
    description TEXT          NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    stock       INT           NOT NULL DEFAULT 0,
    color       VARCHAR(7)    NOT NULL DEFAULT '#6b4f3a',
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    customer    VARCHAR(120)  NOT NULL,
    email       VARCHAR(160)  NOT NULL,
    phone       VARCHAR(30)   NOT NULL,
    address     VARCHAR(255)  NOT NULL,
    subtotal    DECIMAL(10,2) NOT NULL,
    shipping    DECIMAL(10,2) NOT NULL,
    total       DECIMAL(10,2) NOT NULL,
    status      VARCHAR(20)   NOT NULL DEFAULT 'new',
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT           NOT NULL,
    product_id  INT           NOT NULL,
    name        VARCHAR(120)  NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    qty         INT           NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT INTO products (name, category, description, price, stock, color) VALUES
('Espresso Blend', 'Καφές', 'Χαρμάνι 80% Arabica / 20% Robusta, πλούσια κρέμα. Συσκευασία 500 g.', 11.90, 25, '#4a2c1d'),
('Ethiopia Sidamo', 'Καφές', 'Single origin με νότες κίτρου και μελιού. Ιδανικός για φίλτρο. 250 g.', 9.50, 18, '#8a5a2b'),
('Colombia Supremo', 'Καφές', 'Ισορροπημένος, με νότες σοκολάτας και καραμέλας. 250 g.', 8.90, 30, '#6b3e26'),
('Decaf Brazil', 'Καφές', 'Χωρίς καφεΐνη, απαλός και γλυκός. 250 g.', 8.50, 12, '#9c7a5b'),
('Μύλος χειρός', 'Εξοπλισμός', 'Κωνικά κεραμικά μαχαίρια, ρυθμιζόμενο άλεσμα.', 34.00, 8, '#2f4858'),
('French press 600 ml', 'Εξοπλισμός', 'Γυάλινο δοχείο με ανοξείδωτο φίλτρο.', 22.50, 10, '#33658a'),
('V60 dripper', 'Εξοπλισμός', 'Κεραμικό φίλτρο για pour-over καφέ.', 18.00, 15, '#86bbd8'),
('Φίλτρα χάρτινα x100', 'Αξεσουάρ', 'Λευκά φίλτρα για V60 μεγέθους 02.', 4.90, 40, '#b5a28a'),
('Κούπα BeanBox', 'Αξεσουάρ', 'Κεραμική κούπα 350 ml με το λογότυπο.', 9.00, 0, '#c96f3b');
