<?php
/**
 * Hostinger Database Setup Script
 * Run via browser: https://crm.elipereira.com/scripts/hostinger_install.php
 * Or via CLI on server: php scripts/hostinger_install.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Luxury CRM - Hostinger Setup ===\n\n";

require_once __DIR__ . '/../config.php';

try {
    // Create property_images table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS property_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size INT DEFAULT 0,
        mime_type VARCHAR(100) DEFAULT 'image/jpeg',
        is_primary TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    )");
    echo "[OK] Tabela property_images\n";

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@luxurycrm.com']);
    
    if (!$stmt->fetch()) {
        // Create admin user (password: admin123 - CHANGE THIS!)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Admin User', 'admin@luxurycrm.com', $adminPassword, 'admin', 1]);
        echo "[OK] Utilizador admin criado\n";
    } else {
        echo "[OK] Utilizador admin já existe\n";
    }

    // Insert sample properties if none exist
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM properties");
    $stmt->execute();
    $count = $stmt->fetch()['cnt'];

    if ($count == 0) {
        $properties = [
            ['ref' => 'LIS-002', 'title' => 'Apartamento T3 Centro Histórico', 'desc' => 'Belíssimo apartamento T3 no coração de Lisboa, com vistas desafogadas para o Rio Tejo. Renovado com materiais de alta qualidade.', 'address' => 'Rua do Carmo, 45', 'city' => 'Lisboa', 'region' => 'Lisboa', 'postal' => '1200-087', 'price' => 650000, 'bed' => 3, 'bath' => 2, 'area' => 120],
            ['ref' => 'CAS-002', 'title' => 'Moradia V4 Cascais Orla Mar', 'desc' => 'Exclusiva moradia V4 em Cascais, com piscina e jardim privativo. A 100m da praia. Acabamentos premium.', 'address' => 'Avenida Marginal, 2345', 'city' => 'Cascais', 'region' => 'Lisboa', 'postal' => '2750-053', 'price' => 1850000, 'bed' => 4, 'bath' => 3, 'area' => 280],
            ['ref' => 'POR-002', 'title' => 'Penthouse Porto Centro', 'desc' => 'Penthouse moderno no centro do Porto, com terraço panorâmico. Acabamentos de designer.', 'address' => 'Rua de Santa Catarina, 300', 'city' => 'Porto', 'region' => 'Porto', 'postal' => '4000-461', 'price' => 920000, 'bed' => 2, 'bath' => 2, 'area' => 95]
        ];

        foreach ($properties as $p) {
            $stmt = $pdo->prepare("INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'apartment', 'available', ?, ?, ?, 1, NOW(), NOW())");
            $stmt->execute([$p['ref'], $p['title'], $p['desc'], $p['address'], $p['city'], $p['region'], $p['postal'], $p['price'], $p['bed'], $p['bath'], $p['area']]);
            echo "[OK] Imóvel: {$p['ref']} - {$p['title']}\n";
        }
    } else {
        echo "[OK] Imóveis já existem ($count registos)\n";
    }

    echo "\n=== Setup Concluído! ===\n";
    echo "Login: admin@luxurycrm.com\n";
    echo "Password: admin123\n";
    echo "\n⚠️  ALTERE A PASSWORD APÓS O PRIMEIRO LOGIN!\n";
    echo "URL: https://crm.elipereira.com\n";

} catch (Exception $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}