<?php
/**
 * Execute Property Seed - Direct execution
 * Run: php scripts/apply_seed.php
 */

require_once __DIR__ . '/../config.php';

echo "=== Inserindo Imóveis de Exemplo ===\n\n";

try {
    // Create images table if not exists
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

    $properties = [
        ['ref' => 'LIS-002', 'title' => 'Apartamento T3 Centro Histórico', 'desc' => 'Belíssimo apartamento T3 no coração de Lisboa, com vistas desafogadas para o Rio Tejo. Renovado com materiais de alta qualidade.', 'address' => 'Rua do Carmo, 45', 'city' => 'Lisboa', 'region' => 'Lisboa', 'postal' => '1200-087', 'price' => 650000, 'bed' => 3, 'bath' => 2, 'area' => 120],
        ['ref' => 'CAS-002', 'title' => 'Moradia V4 Cascais Orla Mar', 'desc' => 'Exclusiva moradia V4 em Cascais, com piscina e jardim privativo. A 100m da praia. Acabamentos premium.', 'address' => 'Avenida Marginal, 2345', 'city' => 'Cascais', 'region' => 'Lisboa', 'postal' => '2750-053', 'price' => 1850000, 'bed' => 4, 'bath' => 3, 'area' => 280],
        ['ref' => 'POR-002', 'title' => 'Penthouse Porto Centro', 'desc' => 'Penthouse moderno no centro do Porto, com terraço panorâmico. Acabamentos de designer.', 'address' => 'Rua de Santa Catarina, 300', 'city' => 'Porto', 'region' => 'Porto', 'postal' => '4000-461', 'price' => 920000, 'bed' => 2, 'bath' => 2, 'area' => 95]
    ];

    $created = 0;
    foreach ($properties as $p) {
        $stmt = $pdo->prepare("SELECT id FROM properties WHERE reference = ?");
        $stmt->execute([$p['ref']]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $stmt = $pdo->prepare("INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'apartment', 'available', ?, ?, ?, 1, NOW(), NOW())");
            $stmt->execute([$p['ref'], $p['title'], $p['desc'], $p['address'], $p['city'], $p['region'], $p['postal'], $p['price'], $p['bed'], $p['bath'], $p['area']]);
            
            $propId = $pdo->lastInsertId();
            
            // Add sample images
            $images = [
                ['file' => 'prop_'.$p['ref'].'_1.jpg', 'orig' => 'foto1.jpg'],
                ['file' => 'prop_'.$p['ref'].'_2.jpg', 'orig' => 'foto2.jpg']
            ];
            foreach ($images as $i => $img) {
                $isPrimary = ($i === 0) ? 1 : 0;
                $stmtImg = $pdo->prepare("INSERT INTO property_images (property_id, filename, original_name, file_path, file_size, mime_type, is_primary, sort_order) VALUES (?, ?, ?, ?, ?, 'image/jpeg', ?, ?)");
                $stmtImg->execute([$propId, $img['file'], $img['orig'], 'uploads/properties/'.$img['file'], rand(100000,300000), $isPrimary, $i]);
            }
            
            echo "✓ Criado: {$p['ref']} - {$p['title']} (€" . number_format($p['price'], 0, ',', '.') . ")\n";
            $created++;
        } else {
            echo "○ Já existe: {$p['ref']}\n";
        }
    }

    echo "\n=== Concluído! $created imóvel(is) criado(s) ===\n";
    echo "Aceda a: https://crm.elipereira.com/properties.php\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}