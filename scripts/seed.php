<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain');

echo "=== TABLE STRUCTURES ===\n\n";

echo "USERS columns:\n";
$cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
print_r($cols);

echo "\n=== CORRECTING SEED ===\n";

$password = password_hash('123456', PASSWORD_DEFAULT);

$pdo->exec("INSERT INTO users (name, email, password, role, active, created_at) VALUES 
    ('Maria Santos', 'maria.santos@luxury.pt', '$password', 'vendas', 1, NOW()),
    ('Pedro Costa', 'pedro.costa@luxury.pt', '$password', 'vendas', 1, NOW()),
    ('Sofia Ferreira', 'sofia.ferreira@luxury.pt', '$password', 'gerente', 1, NOW()),
    ('João Lima', 'joao.lima@luxury.pt', '$password', 'vendas', 1, NOW()),
    ('Ana Rodrigues', 'ana.rodrigues@luxury.pt', '$password', 'vendas', 1, NOW()),
    ('Miguel Santos', 'miguel.santos@luxury.pt', '$password', 'suporte', 1, NOW()),
    ('Laura Martins', 'laura.martins@luxury.pt', '$password', 'gerente', 1, NOW()),
    ('Tiago Almeida', 'tiago.almeida@luxury.pt', '$password', 'vendas', 1, NOW()),
    ('Carla Sousa', 'carla.sousa@luxury.pt', '$password', 'vendas', 1, NOW())");
echo "Users: OK\n";

$pdo->exec("INSERT INTO properties (title, type, location, price, status, description, created_at) VALUES 
    ('Apartamento T2 Centro', 'apartment', 'Lisboa', 450000, 'available', 'Apartamento T2 no centro de Lisboa', NOW()),
    ('Moradia Moderna Cascais', 'house', 'Cascais', 1250000, 'available', 'Moradia moderna com piscina', NOW()),
    ('Loft Industrial Alfama', 'apartment', 'Lisboa', 380000, 'available', 'Loft industrial renovado', NOW()),
    ('Penthouse Vista Rio', 'apartment', 'Porto', 890000, 'reserved', 'Penthouse de luxo', NOW()),
    ('Quinta com Vinha Douro', 'land', 'Douro', 2500000, 'available', 'Quinta histórica com vinha', NOW())");
echo "Properties: OK\n";

$pdo->exec("INSERT INTO clients (name, email, phone, status, source, created_at) VALUES 
    ('Carlos Silva', 'carlos.silva@email.pt', '+351 912 345 678', 'active', 'website', NOW()),
    ('Patricia Gomes', 'patricia.gomes@email.pt', '+351 933 456 789', 'active', 'website', NOW()),
    ('Manuel Ferreira', 'manuel.ferreira@email.pt', '+351 914 567 890', 'active', 'referral', NOW()),
    ('Isabel Costa', 'isabel.costa@email.pt', '+351 965 678 901', 'active', 'website', NOW()),
    ('Jorge Martins', 'jorge.martins@email.pt', '+351 926 789 012', 'inactive', 'website', NOW()),
    ('Susana Almeida', 'susana.almeida@email.pt', '+351 937 890 123', 'active', 'website', NOW()),
    ('Paulo Rodrigues', 'paulo.rodrigues@email.pt', '+351 948 901 234', 'active', 'referral', NOW()),
    ('Renata Sousa', 'renata.sousa@email.pt', '+351 959 012 345', 'active', 'website', NOW())");
echo "Clients: OK\n";

$stages = $pdo->query("SELECT id FROM deal_stages ORDER BY stage_order")->fetchAll(PDO::FETCH_COLUMN);

$pdo->exec("INSERT INTO deals (reference, property_id, client_id, stage_id, status, value, title, created_at, updated_at) VALUES 
    ('2025-001', 1, 1, {$stages[0]}, 'open', 420000, 'Apartamento T2 - Primeira visita', NOW(), NOW()),
    ('2025-002', 2, 2, {$stages[1]}, 'open', 1200000, 'Moradia Cascais - Negociação', NOW(), NOW()),
    ('2025-003', 3, 3, {$stages[2]}, 'open', 350000, 'Loft - Proposta enviada', NOW(), NOW()),
    ('2025-004', 4, 4, {$stages[3]}, 'open', 850000, 'Penthouse - Resposta pendente', NOW(), NOW()),
    ('2025-005', 5, 5, {$stages[4]}, 'open', 2300000, 'Quinta Douro - Due diligence', NOW(), NOW()),
    ('2025-006', 1, 6, {$stages[5]}, 'open', 410000, 'Apartamento - Documentação', NOW(), NOW()),
    ('2025-007', 2, 7, {$stages[6]}, 'open', 1150000, 'Moradia - Contrato promessa', NOW(), NOW()),
    ('2025-008', 3, 8, {$stages[7]}, 'won', 380000, 'Loft - FECHADO!', NOW(), NOW()),
    ('2025-012', 3, 4, 8, 'lost', 360000, 'Loft - Perdeu', NOW(), NOW())");
echo "Deals: OK\n";

$pdo->exec("INSERT INTO tasks (title, status, priority, assigned_to, due_date, created_at) VALUES 
    ('Visitar apartamento Lisboa', 'pending', 'high', 2, DATE_ADD(NOW(), INTERVAL 2 DAY), NOW()),
    ('Preparar proposta moradia', 'in_progress', 'urgent', 3, DATE_ADD(NOW(), INTERVAL 1 DAY), NOW()),
    ('Contactar cliente penthouse', 'pending', 'medium', 4, DATE_ADD(NOW(), INTERVAL 3 DAY), NOW()),
    ('Reunião quinta Douro', 'completed', 'high', 2, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
    ('Atualizar CRM leads', 'pending', 'low', 5, DATE_ADD(NOW(), INTERVAL 5 DAY), NOW())");
echo "Tasks: OK\n";

$pdo->exec("INSERT INTO activities (type, description, related_to, related_id, user_id, created_at) VALUES 
    ('deal_created', 'Novo negócio criado - 2025-001', 'deal', 1, 1, NOW()),
    ('deal_updated', 'Negócio atualizado para negociação', 'deal', 2, 1, NOW()),
    ('client_created', 'Novo cliente adicionado', 'client', 1, 1, NOW()),
    ('property_created', 'Novo imóvel adicionado', 'property', 1, 1, NOW()),
    ('task_completed', 'Tarefa concluída', 'task', 4, 1, NOW()),
    ('deal_won', 'Negócio FECHADO!', 'deal', 8, 1, NOW())");
echo "Activities: OK\n";

echo "\n=== ✅ ALL DATA SEEDED! ===\n";