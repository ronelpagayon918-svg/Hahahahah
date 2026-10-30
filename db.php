<?php
declare(strict_types=1);

$dbHost = getenv('THREADLINE_DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('THREADLINE_DB_PORT') ?: '3306';
$dbName = 'threadline_db';
$dbUser = getenv('THREADLINE_DB_USER') ?: 'root';
$dbPassword = getenv('THREADLINE_DB_PASS') ?: '';

$pdo = new PDO(
    "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4",
    $dbUser,
    $dbPassword,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$pdo->exec(
    "CREATE DATABASE IF NOT EXISTS `{$dbName}` "
    . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
);
$pdo->exec("USE `{$dbName}`");

$schema = [
    'CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        category VARCHAR(40) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        stock INT UNSIGNED NOT NULL DEFAULT 0,
        color VARCHAR(20) NOT NULL DEFAULT \'#b7a48d\',
        tag VARCHAR(40) NOT NULL DEFAULT \'\',
        type VARCHAR(30) NOT NULL DEFAULT \'shirt\',
        description TEXT NOT NULL,
        sizes TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB',
    'CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM(\'customer\', \'admin\') NOT NULL DEFAULT \'customer\',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB',
    'CREATE TABLE IF NOT EXISTS orders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(30) NOT NULL UNIQUE,
        user_id INT UNSIGNED NULL,
        customer_name VARCHAR(120) NOT NULL,
        customer_email VARCHAR(190) NOT NULL,
        status VARCHAR(40) NOT NULL DEFAULT \'Order Placed\',
        subtotal DECIMAL(10, 2) NOT NULL,
        shipping DECIMAL(10, 2) NOT NULL DEFAULT 0,
        total DECIMAL(10, 2) NOT NULL,
        address TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB',
    'CREATE TABLE IF NOT EXISTS order_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NULL,
        product_name VARCHAR(120) NOT NULL,
        size VARCHAR(30) NOT NULL,
        quantity INT UNSIGNED NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    ) ENGINE=InnoDB',
    'CREATE TABLE IF NOT EXISTS reviews (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        rating TINYINT UNSIGNED NOT NULL,
        comment VARCHAR(400) NOT NULL DEFAULT \'\',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
        CONSTRAINT fk_reviews_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB',
];

foreach ($schema as $statement) {
    $pdo->exec($statement);
}

$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
if ($productCount === 0) {
    $products = [
        [1, 'Everyday Heavy Tee', 'Tops', 699, 24, '#6d7464', 'BESTSELLER', 'shirt', 'A substantial, soft cotton tee with a relaxed fit. Built to be worn on repeat.', ['S', 'M', 'L', 'XL']],
        [2, 'Relaxed Utility Pants', 'Bottoms', 1299, 12, '#b7a48d', 'NEW', 'pants', 'Easy-moving utility pants with a clean profile and practical everyday pockets.', ['S', 'M', 'L', 'XL']],
        [3, 'Studio Overshirt', 'Outerwear', 1599, 8, '#7e817a', 'LIMITED', 'jacket', 'A versatile layer with a structured feel. Wear it open or buttoned up.', ['S', 'M', 'L', 'XL']],
        [4, 'Essential Cap', 'Accessories', 499, 31, '#c8bca8', '', 'cap', 'A simple everyday cap with an adjustable back strap.', ['ONE SIZE']],
        [5, 'Boxy Graphic Tee', 'Tops', 799, 18, '#c2b4a2', 'POPULAR', 'shirt', 'A boxy silhouette with a subtle graphic-inspired finish.', ['S', 'M', 'L', 'XL']],
        [6, 'Daily Straight Jeans', 'Bottoms', 1499, 15, '#596574', '', 'pants', 'A straight-leg fit that works with almost everything in your wardrobe.', ['S', 'M', 'L', 'XL']],
        [7, 'Canvas Work Jacket', 'Outerwear', 1899, 6, '#a6a18f', 'LOW STOCK', 'jacket', 'A sturdy canvas layer with a timeless shape and easy layering room.', ['S', 'M', 'L', 'XL']],
        [8, 'Everyday Tote', 'Accessories', 399, 40, '#b9ad9b', '', 'cap', 'A lightweight carry-all for daily essentials, books, and market finds.', ['ONE SIZE']],
    ];

    $insert = $pdo->prepare(
        'INSERT INTO products (id, name, category, price, stock, color, tag, type, description, sizes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($products as $product) {
        $product[9] = json_encode($product[9], JSON_THROW_ON_ERROR);
        $insert->execute($product);
    }
}
