<?php
/**
 * Quick Seed - Execute via: https://crm.elipereira.com/run-seed.php?secret=luxury2026
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'luxury2026') {
    http_response_code(403);
    echo 'Acesso negado';
    exit;
}

require_once __DIR__ . '/config.php';

echo "=== Seed de Imóveis ===<br><br>";

try {
    // Create table
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
    echo "[OK] Tabela imagens<br>";

    $props = [
        ['ref' => 'LIS-002', 'title' => 'Apartamento T3 Centro Histórico', 'desc' => 'Belíssimo apartamento T3 no coração de Lisboa.', 'city' => 'Lisboa', 'price' => 650000],
        ['ref' => 'CAS-002', 'title' => 'Moradia V4 Cascais', 'desc' => 'Exclusiva moradia V4 em Cascais, com piscina.', 'city' => 'Cascais', 'price' => 1850000],
        ['ref' => 'POR-002', 'title' => 'Penthouse Porto Centro', 'desc' => 'Penthouse moderno no centro do Porto.', 'city' => 'Porto', 'price' => 920000]
    ];

    foreach ($props as $p) {
        $check = $pdo->prepare("SELECT id FROM properties WHERE reference = ?");
        $check->execute([$p['ref']]);
        if (!$check->fetch()) {
            $ins = $pdo->prepare("INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at, updated_at) VALUES (?, ?, ?, '', ?, 'Lisboa', '', ?, 'apartment', 'available', 3, 2, 100, 1, NOW(), NOW())");
            $ins->execute([$p['ref'], $p['title'], $p['desc'], $p['city'], $p['price']]);
            
            $pid = $pdo->lastInsertId();
            for ($i = 1; $i <= 2; $i++) {
                $img = $pdo->prepare("INSERT INTO property_images (property_id, filename, original_name, file_path, file_size, mime_type, is_primary, sort_order) VALUES (?, ?, ?, ?, ?, 'image/jpeg', ?, ?)");
                $img->execute([$pid, "prop_{$p['ref']}_$i.jpg", "foto$i.jpg", "uploads/properties/prop_{$p['ref']}_$i.jpg", rand(100000,300000), $i==1?1:0, $i-1]);
            }
            echo "[OK] {$p['ref']} criado<br>";
        } else {
            echo "[--] {$p['ref']} já existe<br>";
        }
    }
    echo "<br>=== Concluído! ===";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}