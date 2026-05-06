<?php
/**
 * Force Seed Properties - Execute via URL
 * https://crm.elipereira.com/seed.php?key=luxury2026
 */

$key = $_GET['key'] ?? '';
if ($key !== 'luxury2026') {
    http_response_code(403);
    exit('Acesso negado');
}

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain');

echo "=== SEED DE IMÓVEIS ===\n\n";

try {
    // Criar tabela de imagens se não existir
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
    echo "[OK] Tabela property_images criada/verificada\n";
    
    // Ver imóveis existentes
    $count = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    echo "Imóveis existentes: $count\n\n";
    
    // Lista de imóveis para criar
    $properties = [
        ['ref' => 'LIS-001', 'title' => 'Apartamento T2 Centro', 'desc' => 'Apartamento T2 no centro de Lisboa com vista panorâmica.', 'addr' => 'Prça do Rossio, 25', 'city' => 'Lisboa', 'region' => 'Lisboa', 'postal' => '1100-200', 'price' => 450000, 'type' => 'apartment', 'bed' => 2, 'bath' => 1, 'area' => 85, 'featured' => 1],
        ['ref' => 'CAS-001', 'title' => 'Moradia Moderna Cascais', 'desc' => 'Moradia moderna V4 com piscina e jardim privativo.', 'addr' => 'Rua da Praia, 45', 'city' => 'Cascais', 'region' => 'Lisboa', 'postal' => '2750-053', 'price' => 1250000, 'type' => 'house', 'bed' => 4, 'bath' => 3, 'area' => 250, 'featured' => 1],
        ['ref' => 'LIS-003', 'title' => 'Loft Industrial Alfama', 'desc' => 'Loft industrial renovado com karakter.', 'addr' => 'Rua de Santa Cruz, 12', 'city' => 'Lisboa', 'region' => 'Lisboa', 'postal' => '1100-456', 'price' => 380000, 'type' => 'apartment', 'bed' => 1, 'bath' => 1, 'area' => 65, 'featured' => 0],
        ['ref' => 'POR-001', 'title' => 'Penthouse Vista Rio', 'desc' => 'Penthouse de luxo com terraço.', 'addr' => 'Av. da Boavista, 500', 'city' => 'Porto', 'region' => 'Porto', 'postal' => '4100-123', 'price' => 890000, 'type' => 'apartment', 'bed' => 3, 'bath' => 2, 'area' => 150, 'featured' => 1],
        ['ref' => 'DOU-001', 'title' => 'Quinta com Vinha Douro', 'desc' => 'Quinta histórica com vinha e adega.', 'addr' => 'Estrada Nacional 222', 'city' => 'Douro', 'region' => 'Norte', 'postal' => '5000-001', 'price' => 2500000, 'type' => 'land', 'bed' => 0, 'bath' => 0, 'area' => 5000, 'featured' => 0]
    ];
    
    $created = 0;
    foreach ($properties as $p) {
        // Verificar se já existe
        $stmt = $pdo->prepare("SELECT id FROM properties WHERE reference = ?");
        $stmt->execute([$p['ref']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $propId = $existing['id'];
            echo "[JÁ EXISTE] {$p['ref']} - {$p['title']} (ID: $propId)\n";
            
            // Verificar se tem imagens
            $imgCount = $pdo->query("SELECT COUNT(*) FROM property_images WHERE property_id = $propId")->fetchColumn();
            if ($imgCount == 0) {
                // Adicionar imagens
                for ($i = 1; $i <= 2; $i++) {
                    $img = $pdo->prepare("INSERT INTO property_images (property_id, filename, original_name, file_path, file_size, mime_type, is_primary, sort_order) VALUES (?, ?, ?, ?, ?, 'image/jpeg', ?, ?)");
                    $img->execute([$propId, "prop_{$p['ref']}_$i.jpg", "foto$i.jpg", "uploads/properties/prop_{$p['ref']}_$i.jpg", rand(100000,300000), $i==1?1:0, $i-1]);
                }
                echo "  → Imagens adicionadas\n";
            }
        } else {
            // Criar imóvel
            $stmt = $pdo->prepare("INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                $p['ref'], $p['title'], $p['desc'], $p['addr'], $p['city'], $p['region'], 
                $p['postal'], $p['price'], $p['type'], $p['bed'], $p['bath'], $p['area'], $p['featured']
            ]);
            $propId = $pdo->lastInsertId();
            
            // Adicionar imagens
            for ($i = 1; $i <= 2; $i++) {
                $img = $pdo->prepare("INSERT INTO property_images (property_id, filename, original_name, file_path, file_size, mime_type, is_primary, sort_order) VALUES (?, ?, ?, ?, ?, 'image/jpeg', ?, ?)");
                $img->execute([$propId, "prop_{$p['ref']}_$i.jpg", "foto$i.jpg", "uploads/properties/prop_{$p['ref']}_$i.jpg", rand(100000,300000), $i==1?1:0, $i-1]);
            }
            
            echo "[CRIADO] {$p['ref']} - {$p['title']} (€" . number_format($p['price'], 0, ',', '.') . ")\n";
            $created++;
        }
    }
    
    // Contagem final
    $total = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $imgs = $pdo->query("SELECT COUNT(*) FROM property_images")->fetchColumn();
    
    echo "\n=== RESUMO ===\n";
    echo "Total de imóveis: $total\n";
    echo "Total de imagens: $imgs\n";
    echo "\nAceda a: https://crm.elipereira.com/properties.php\n";

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}