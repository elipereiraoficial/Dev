<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain');

echo "=== SEEDING TEST DATA ===\n\n";

$password = password_hash('123456', PASSWORD_DEFAULT);

try {
    echo "1. Users...\n";
    $users = [
        ['Maria Santos', 'maria.santos@luxury.pt', 'vendas', '+351 910 000 001'],
        ['Pedro Costa', 'pedro.costa@luxury.pt', 'vendas', '+351 910 000 002'],
        ['Sofia Ferreira', 'sofia.ferreira@luxury.pt', 'gerente', '+351 910 000 003'],
        ['João Lima', 'joao.lima@luxury.pt', 'vendas', '+351 910 000 004'],
        ['Ana Rodrigues', 'ana.rodrigues@luxury.pt', 'vendas', '+351 910 000 005'],
        ['Miguel Santos', 'miguel.santos@luxury.pt', 'suporte', '+351 910 000 006'],
        ['Laura Martins', 'laura.martins@luxury.pt', 'gerente', '+351 910 000 007'],
        ['Tiago Almeida', 'tiago.almeida@luxury.pt', 'vendas', '+351 910 000 008'],
        ['Carla Sousa', 'carla.sousa@luxury.pt', 'vendas', '+351 910 000 009'],
    ];
    
    foreach ($users as $u) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$u[0], $u[1], $password, $u[2], $u[3]]);
    }
    echo "   OK\n";
    
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

try {
    echo "2. Properties...\n";
    $props = [
        ['LIS-001', 'Apartamento T2 Centro', 'Apartamento T2 no centro de Lisboa', 'Rua das Flores 123', 'Lisboa', 'Lisboa', '1000-000', 450000, 'apartment', 'available', 2, 1, 95],
        ['CAS-001', 'Moradia Moderna Cascais', 'Moradia moderna com piscina', 'Av. Marginal 456', 'Cascais', 'Lisboa', '2750-000', 1250000, 'house', 'available', 4, 3, 350],
        ['LIS-002', 'Loft Industrial Alfama', 'Loft industrial renovado', 'Rua do-top 78', 'Lisboa', 'Lisboa', '1100-000', 380000, 'apartment', 'available', 1, 1, 80],
        ['POR-001', 'Penthouse Vista Rio', 'Penthouse de luxo', 'Av. da Boavista 100', 'Porto', 'Porto', '4100-000', 890000, 'apartment', 'reserved', 3, 2, 180],
        ['DOU-001', 'Quinta com Vinha Douro', 'Quinta histórica', 'Estrada da Vinha 50', 'Peso da Régua', 'Douro', '5050-000', 2500000, 'land', 'available', 0, 0, 5000],
    ];
    
    foreach ($props as $p) {
        $stmt = $pdo->prepare("INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute($p);
    }
    echo "   OK\n";
    
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

try {
    echo "3. Clients...\n";
    $clients = [
        ['Carlos Silva', 'carlos.silva@email.pt', '+351 912 345 678', 'website'],
        ['Patricia Gomes', 'patricia.gomes@email.pt', '+351 933 456 789', 'website'],
        ['Manuel Ferreira', 'manuel.ferreira@email.pt', '+351 914 567 890', 'referral'],
        ['Isabel Costa', 'isabel.costa@email.pt', '+351 965 678 901', 'website'],
        ['Jorge Martins', 'jorge.martins@email.pt', '+351 926 789 012', 'website'],
        ['Susana Almeida', 'susana.almeida@email.pt', '+351 937 890 123', 'website'],
        ['Paulo Rodrigues', 'paulo.rodrigues@email.pt', '+351 948 901 234', 'referral'],
        ['Renata Sousa', 'renata.sousa@email.pt', '+351 959 012 345', 'website'],
    ];
    
    foreach ($clients as $c) {
        $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, type, source, status, assigned_to, created_at) VALUES (?, ?, ?, 'individual', ?, 'active', 2, NOW())");
        $stmt->execute([$c[0], $c[1], $c[2], $c[3]]);
    }
    echo "   OK\n";
    
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

try {
    echo "4. Deals...\n";
    $stages = $pdo->query("SELECT id FROM deal_stages ORDER BY stage_order")->fetchAll(PDO::FETCH_COLUMN);
    
    $deals = [
        ['2025-001', 'Apartamento T2 - Primeira visita', 1, 1, $stages[0], 420000, 'open'],
        ['2025-002', 'Moradia Cascais - Negociação', 2, 2, $stages[1], 1200000, 'open'],
        ['2025-003', 'Loft - Proposta enviada', 3, 3, $stages[2], 350000, 'open'],
        ['2025-004', 'Penthouse - Resposta pendente', 4, 4, $stages[3], 850000, 'open'],
        ['2025-005', 'Quinta Douro - Due diligence', 5, 5, $stages[4], 2300000, 'open'],
        ['2025-006', 'Apartamento - Documentação', 6, 1, $stages[5], 410000, 'open'],
        ['2025-007', 'Moradia - Contrato promessa', 7, 2, $stages[6], 1150000, 'open'],
        ['2025-008', 'Loft - FECHADO!', 8, 3, $stages[7], 380000, 'won'],
        ['2025-012', 'Loft - Perdeu', 4, 3, 8, 360000, 'lost'],
    ];
    
    foreach ($deals as $d) {
        $stmt = $pdo->prepare("INSERT INTO deals (reference, title, client_id, property_id, stage_id, value, status, agent_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 2, NOW(), NOW())");
        $stmt->execute([$d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6]]);
    }
    echo "   OK\n";
    
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

try {
    echo "5. Tasks...\n";
    $tasks = [
        ['Visitar apartamento Lisboa', 'Primeira visita ao apartamento T2', 1, 2, 'high'],
        ['Preparar proposta moradia', 'Preparar proposta comercial', 2, 1, 'urgent'],
        ['Contactar cliente penthouse', 'Follow-up com cliente', 4, 3, 'medium'],
        ['Atualizar CRM leads', 'Atualizar novos leads', 1, 5, 'low'],
        ['Follow-up Loft Industrial', 'Verificar resposta do cliente', 3, 1, 'high'],
    ];
    
    foreach ($tasks as $t) {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, related_to, related_id, due_date, priority, status, assigned_to, created_by, created_at) VALUES (?, ?, 'deal', ?, DATE_ADD(NOW(), INTERVAL ? DAY), ?, 'pending', ?, 1, NOW())");
        $stmt->execute([$t[0], $t[1], $t[2], $t[3], $t[4], $t[2]]);
    }
    echo "   OK\n";
    
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

try {
    echo "6. Activities...\n";
    $activities = [
        ['deal_created', 'Novo negócio criado - 2025-001', 'deal', 1],
        ['deal_updated', 'Negócio atualizado para negociação', 'deal', 2],
        ['client_created', 'Novo cliente adicionado', 'client', 1],
        ['property_created', 'Novo imóvel adicionado', 'property', 4],
        ['task_completed', 'Tarefa concluída', 'task', 1],
        ['deal_won', 'Negócio FECHADO!', 'deal', 8],
    ];
    
    foreach ($activities as $a) {
        $stmt = $pdo->prepare("INSERT INTO activities (type, description, related_to, related_id, user_id, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$a[0], $a[1], $a[2], $a[3]]);
    }
    echo "   OK\n";
    
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== ✅ DONE! ===\n";