<?php
/**
 * Seed Properties - Execute via URL após login
 * URL: https://crm.elipereira.com/scripts/seed-data.php?key=luxury2026
 */

header('Content-Type: text/plain');

$key = $_GET['key'] ?? '';
if ($key !== 'luxury2026') {
    echo "Acesso não autorizado";
    exit;
}

require_once __DIR__ . '/../config.php';

echo "=== Seed de Imóveis ===\n\n";

try {
    // Criar tabela de imagens
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

    $properties = [
        ['ref' => 'LIS-002', 'title' => 'Apartamento T3 Centro Histórico', 'desc' => 'Belíssimo apartamento T3 no coração de Lisboa.', 'city' => 'Lisboa', 'price' => 650000],
        ['ref' => 'CAS-002', 'title' => 'Moradia V4 Cascais Orla Mar', 'desc' => 'Exclusiva moradia V4 em Cascais, com piscina.', 'city' => 'Cascais', 'price' => 1850000],
        ['ref' => 'POR-002', 'title' => 'Penthouse Porto Centro', 'desc' => 'Penthouse moderno no centro do Porto.', 'city' => 'Porto', 'price' => 920000]
    ];

    foreach ($properties as $p) {
        $stmt = $pdo->prepare("SELECT id FROM properties WHERE reference = ?");
        $stmt->execute([$p['ref']]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $stmt = $pdo->prepare("INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'Lisboa', '', ?, 'apartment', 'available', 3, 2, 100, 1, NOW(), NOW())");
            $stmt->execute([$p['ref'], $p['title'], $p['desc'], '', $p['city'], $p['price']]);
            echo "[OK] Criado: {$p['ref']}\n";
        } else {
            echo "[SKIP] Já existe: {$p['ref']}\n";
        }
    }

    echo "\n=== Concluído! ===\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}