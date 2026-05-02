<?php
require_once __DIR__ . '/../config.php';

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== INSERTING TEST DATA ===\n\n";

try {
    // 1. Create 10 Users
    echo "1. Creating 10 Users...\n";
    
    $users = [
        ['Maria Santos', 'maria.santos@luxury.pt', 'vendas', 'user'],
        ['Pedro Costa', 'pedro.costa@luxury.pt', 'vendas', 'user'],
        ['Sofia Ferreira', 'sofia.ferreira@luxury.pt', 'gerente', 'manager'],
        ['João Lima', 'joao.lima@luxury.pt', 'vendas', 'user'],
        ['Ana Rodrigues', 'ana.rodrigues@luxury.pt', 'vendas', 'user'],
        ['Miguel Santos', 'miguel.santos@luxury.pt', 'suporte', 'user'],
        ['Laura Martins', 'laura.martins@luxury.pt', 'gerente', 'manager'],
        ['Tiago Almeida', 'tiago.almeida@luxury.pt', 'vendas', 'user'],
        ['Carla Sousa', 'carla.sousa@luxury.pt', 'vendas', 'user'],
        ['Rui Ferreira', 'rui.ferreira@luxury.pt', 'admin', 'admin'],
    ];
    
    $userIds = [];
    foreach ($users as $i => $u) {
        $password = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, department, active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$u[0], $u[1], $password, $u[2], $u[3]]);
        $userIds[] = $pdo->lastInsertId();
        echo "   ✅ User: {$u[0]} ({$u[1]})\n";
    }

    // 2. Create 5 Properties
    echo "\n2. Creating 5 Properties...\n";
    
    $properties = [
        ['Apartamento T2 Centro', 'apartment', 'Lisboa', 450000, 'available'],
        ['Moradia Moderna Cascais', 'house', 'Cascais', 1250000, 'available'],
        ['Loft Industrial Alfama', 'apartment', 'Lisboa', 380000, 'available'],
        ['Penthouse Vista Rio', 'apartment', 'Porto', 890000, 'reserved'],
        ['Quinta com Vinha Douro', 'land', 'Douro', 2500000, 'available'],
    ];
    
    $propertyIds = [];
    foreach ($properties as $i => $p) {
        $stmt = $pdo->prepare("INSERT INTO properties (title, type, location, price, status, description, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], "Imóvel de luxo em {$p[2]}"]);
        $propertyIds[] = $pdo->lastInsertId();
        echo "   ✅ Property: {$p[0]} - €" . number_format($p[3], 0, ',', '.') . "\n";
    }

    // 3. Create Clients
    echo "\n3. Creating Clients...\n";
    
    $clients = [
        ['Carlos Silva', 'carlos.silva@email.pt', '+351 912 345 678', 'active'],
        ['Patricia Gomes', 'patricia.gomes@email.pt', '+351 933 456 789', 'active'],
        ['Manuel Ferreira', 'manuel.ferreira@email.pt', '+351 914 567 890', 'active'],
        ['Isabel Costa', 'isabel.costa@email.pt', '+351 965 678 901', 'active'],
        ['Jorge Martins', 'jorge.martins@email.pt', '+351 926 789 012', 'inactive'],
        ['Susana Almeida', 'susana.almeida@email.pt', '+351 937 890 123', 'active'],
        ['Paulo Rodrigues', 'paulo.rodrigues@email.pt', '+351 948 901 234', 'active'],
        ['Renata Sousa', 'renata.sousa@email.pt', '+351 959 012 345', 'active'],
    ];
    
    $clientIds = [];
    foreach ($clients as $i => $c) {
        $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, status, source, created_at) VALUES (?, ?, ?, ?, 'website', NOW())");
        $stmt->execute([$c[0], $c[1], $c[2], $c[3]]);
        $clientIds[] = $pdo->lastInsertId();
        echo "   ✅ Client: {$c[0]}\n";
    }

    // 4. Create Deals (Pipeline stages)
    echo "\n4. Creating Deals (all pipeline stages)...\n";
    
    $stages = $pdo->query("SELECT id FROM deal_stages ORDER BY stage_order")->fetchAll(PDO::FETCH_COLUMN);
    
    $deals = [
        ['2025-001', $propertyIds[0], $clientIds[0], $stages[0], 'open', 420000, 'Apartamento T2 - Primeira visita agendada'],
        ['2025-002', $propertyIds[1], $clientIds[1], $stages[1], 'open', 1200000, 'Moradia Cascais - Em negociação'],
        ['2025-003', $propertyIds[2], $clientIds[2], $stages[2], 'open', 350000, 'Loft - Proposta enviada'],
        ['2025-004', $propertyIds[3], $clientIds[3], $stages[3], 'open', 850000, 'Penthouse - Awaiting resposta'],
        ['2025-005', $propertyIds[4], $clientIds[4], $stages[4], 'open', 2300000, 'Quinta Douro - Due diligence'],
        ['2025-006', $propertyIds[0], $clientIds[5], $stages[5], 'open', 410000, 'Apartamento - документи'],
        ['2025-007', $propertyIds[1], $clientIds[6], $stages[6], 'open', 1150000, 'Moradia - Contrato promessa'],
        ['2025-008', $propertyIds[2], $clientIds[7], $stages[7], 'won', 380000, 'Loft - FECHADO!', '2025-04-15'],
        ['2025-009', $propertyIds[4], $clientIds[0], $stages[0], 'open', 2450000, 'Quinta - Novo lead'],
        ['2025-010', $propertyIds[1], $clientIds[1], $stages[1], 'open', 1180000, 'Moradia - Segunda visita'],
        ['2025-011', $propertyIds[0], $clientIds[2], $stages[2], 'open', 435000, 'Apartamento - Negociação preço'],
        ['2025-012', $propertyIds[2], $clientIds[3], $stages[0], 'lost', 360000, 'Loft - Perdeu', '2025-03-20'],
        ['2025-013', $propertyIds[3], $clientIds[4], $stages[3], 'open', 870000, 'Penthouse - Em análise'],
        ['2025-014', $propertyIds[4], $clientIds[5], $stages[4], 'open', 2350000, 'Quinta - Discussão financiamento'],
        ['2025-015', $propertyIds[1], $clientIds[6], $stages[5], 'won', 1200000, 'Moradia - FECHADO!', '2025-04-28'],
    ];
    
    foreach ($deals as $i => $d) {
        $actual_close = isset($d[7]) ? $d[7] : null;
        $stmt = $pdo->prepare("INSERT INTO deals (reference, property_id, client_id, stage_id, status, value, title, created_at, updated_at, actual_close) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)");
        $stmt->execute([$d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $actual_close]);
        echo "   ✅ Deal: {$d[0]} - €" . number_format($d[5], 0, ',', '.') . " ({$d[4]})\n";
    }

    // 5. Create Tasks
    echo "\n5. Creating Tasks...\n";
    
    $tasks = [
        ['Visitar apartamento Lisboa', 'pending', 'high', $userIds[0], date('Y-m-d', strtotime('+2 days'))],
        ['Preparar proposta moradia', 'in_progress', 'urgent', $userIds[1], date('Y-m-d', strtotime('+1 days'))],
        ['Contactar cliente penthouse', 'pending', 'medium', $userIds[2], date('Y-m-d', strtotime('+3 days'))],
        ['Reunião quinta Douro', 'completed', 'high', $userIds[0], date('Y-m-d', strtotime('-1 days'))],
        ['Atualizar CRM leads', 'pending', 'low', $userIds[3], date('Y-m-d', strtotime('+5 days'))],
        ['Follow-up Loft Industrial', 'pending', 'high', $userIds[1], date('Y-m-d', strtotime('+1 days'))],
        ['Apresentação imóveis novo cliente', 'pending', 'medium', $userIds[2], date('Y-m-d', strtotime('+4 days'))],
        ['Revisão contratos', 'in_progress', 'urgent', $userIds[4], date('Y-m-d', strtotime('+2 days'))],
    ];
    
    foreach ($tasks as $i => $t) {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, status, priority, assigned_to, due_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$t[0], $t[1], $t[2], $t[3], $t[4]]);
        echo "   ✅ Task: {$t[0]} ({$t[2]})\n";
    }

    // 6. Create Activities
    echo "\n6. Creating Activities...\n";
    
    $activities = [
        ['deal_created', 'Novo negócio criado - 2025-001', 'deal', null, $userIds[0]],
        ['deal_updated', 'Negócio atualizado para fase de negociação', 'deal', null, $userIds[1]],
        ['client_created', 'Novo cliente adicionado - Carlos Silva', 'client', null, $userIds[0]],
        ['property_created', 'Novo imóvel adicionado - Penthouse', 'property', null, $userIds[2]],
        ['task_completed', 'Tarefa concluída - Reunião quinta Douro', 'task', null, $userIds[0]],
        ['deal_won', 'Negócio FECHADO! - 2025-008', 'deal', null, $userIds[1]],
        ['property_viewing', 'Visita agendada - Apartamento Lisboa', 'property', null, $userIds[0]],
        ['client_contact', 'Contacto com cliente - Patricia Gomes', 'client', null, $userIds[1]],
    ];
    
    foreach ($activities as $i => $a) {
        $stmt = $pdo->prepare("INSERT INTO activities (type, description, related_to, related_id, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$a[0], $a[1], $a[2], $a[3], $a[4]]);
        echo "   ✅ Activity: {$a[1]}\n";
    }

    echo "\n=== ✅ ALL TEST DATA CREATED! ===\n";
    echo "\n📊 Summary:\n";
    echo "   - 10 Users (password: 123456)\n";
    echo "   - 5 Properties\n";
    echo "   - 8 Clients\n";
    echo "   - 15 Deals (all pipeline stages)\n";
    echo "   - 8 Tasks\n";
    echo "   - 8 Activities\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";