-- Test Data for Luxury CRM
-- Run this in phpMyAdmin on Hostinger

-- 1. Create 10 Users
INSERT INTO users (name, email, password, role, department, active, created_at) VALUES 
('Maria Santos', 'maria.santos@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'vendas', 'user', 1, NOW()),
('Pedro Costa', 'pedro.costa@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'vendas', 'user', 1, NOW()),
('Sofia Ferreira', 'sofia.ferreira@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'gerente', 'manager', 1, NOW()),
('João Lima', 'joao.lima@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'vendas', 'user', 1, NOW()),
('Ana Rodrigues', 'ana.rodrigues@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'vendas', 'user', 1, NOW()),
('Miguel Santos', 'miguel.santos@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'suporte', 'user', 1, NOW()),
('Laura Martins', 'laura.martins@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'gerente', 'manager', 1, NOW()),
('Tiago Almeida', 'tiago.almeida@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'vendas', 'user', 1, NOW()),
('Carla Sousa', 'carla.sousa@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5h5uKf0vW5Lxk2aS', 'vendas', 'user', 1, NOW()),
('Rui Ferreira', 'rui.ferreira@luxury.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lC8yQ5uKf0vW5Lxk2aS', 'admin', 'admin', 1, NOW());

-- 2. Create 5 Properties
INSERT INTO properties (title, type, location, price, status, description, created_at) VALUES 
('Apartamento T2 Centro', 'apartment', 'Lisboa', 450000, 'available', 'Apartamento T2 no centro de Lisboa com vista panorâmica', NOW()),
('Moradia Moderna Cascais', 'house', 'Cascais', 1250000, 'available', 'Moradia moderna com piscina e vista mar em Cascais', NOW()),
('Loft Industrial Alfama', 'apartment', 'Lisboa', 380000, 'available', 'Loft industrial renovado no coração de Alfama', NOW()),
('Penthouse Vista Rio', 'apartment', 'Porto', 890000, 'reserved', 'Penthouse de luxo com vista para o Rio Douro', NOW()),
('Quinta com Vinha Douro', 'land', 'Douro', 2500000, 'available', 'Quinta histórica com vinha e casa de campo', NOW());

-- 3. Create Clients
INSERT INTO clients (name, email, phone, status, source, created_at) VALUES 
('Carlos Silva', 'carlos.silva@email.pt', '+351 912 345 678', 'active', 'website', NOW()),
('Patricia Gomes', 'patricia.gomes@email.pt', '+351 933 456 789', 'active', 'website', NOW()),
('Manuel Ferreira', 'manuel.ferreira@email.pt', '+351 914 567 890', 'active', 'referral', NOW()),
('Isabel Costa', 'isabel.costa@email.pt', '+351 965 678 901', 'active', 'website', NOW()),
('Jorge Martins', 'jorge.martins@email.pt', '+351 926 789 012', 'inactive', 'website', NOW()),
('Susana Almeida', 'susana.almeida@email.pt', '+351 937 890 123', 'active', 'website', NOW()),
('Paulo Rodrigues', 'paulo.rodrigues@email.pt', '+351 948 901 234', 'active', 'referral', NOW()),
('Renata Sousa', 'renata.sousa@email.pt', '+351 959 012 345', 'active', 'website', NOW());

-- 4. Create Deals (Pipeline stages)
INSERT INTO deals (reference, property_id, client_id, stage_id, status, value, title, created_at, updated_at, actual_close) VALUES 
-- Stage 1 - Novo Lead
('2025-001', 1, 1, 1, 'open', 420000, 'Apartamento T2 - Primeira visita agendada', NOW(), NOW(), NULL),
('2025-009', 5, 1, 1, 'open', 2450000, 'Quinta Douro - Novo lead', NOW(), NOW(), NULL),
-- Stage 2 - Qualificação
('2025-002', 2, 2, 2, 'open', 1200000, 'Moradia Cascais - Em negociação', NOW(), NOW(), NULL),
('2025-010', 2, 2, 2, 'open', 1180000, 'Moradia - Segunda visita', NOW(), NOW(), NULL),
-- Stage 3 - Proposta
('2025-003', 3, 3, 3, 'open', 350000, 'Loft - Proposta enviada', NOW(), NOW(), NULL),
('2025-011', 1, 3, 3, 'open', 435000, 'Apartamento - Negociação preço', NOW(), NOW(), NULL),
-- Stage 4 - Negociação
('2025-004', 4, 4, 4, 'open', 850000, 'Penthouse - Awaiting resposta', NOW(), NOW(), NULL),
('2025-013', 4, 5, 4, 'open', 870000, 'Penthouse - Em análise', NOW(), NOW(), NULL),
-- Stage 5 - Due Diligence
('2025-005', 5, 5, 5, 'open', 2300000, 'Quinta Douro - Due diligence', NOW(), NOW(), NULL),
('2025-014', 5, 6, 5, 'open', 2350000, 'Quinta - Discussão financiamento', NOW(), NOW(), NULL),
-- Stage 6 - Contrato
('2025-006', 1, 6, 6, 'open', 410000, 'Apartamento - Documentação', NOW(), NOW(), NULL),
('2025-007', 2, 7, 6, 'open', 1150000, 'Moradia - Contrato promessa', NOW(), NOW(), NULL),
-- Stage 7 - Closing
('2025-008', 3, 8, 7, 'won', 380000, 'Loft - FECHADO!', NOW(), NOW(), '2025-04-15'),
('2025-015', 2, 7, 7, 'won', 1200000, 'Moradia - FECHADO!', NOW(), NOW(), '2025-04-28'),
-- Lost
('2025-012', 3, 4, 3, 'lost', 360000, 'Loft - Perdeu', NOW(), NOW(), '2025-03-20');

-- 5. Create Tasks
INSERT INTO tasks (title, status, priority, assigned_to, due_date, created_at) VALUES 
('Visitar apartamento Lisboa', 'pending', 'high', 2, DATE_ADD(NOW(), INTERVAL 2 DAY), NOW()),
('Preparar proposta moradia', 'in_progress', 'urgent', 3, DATE_ADD(NOW(), INTERVAL 1 DAY), NOW()),
('Contactar cliente penthouse', 'pending', 'medium', 4, DATE_ADD(NOW(), INTERVAL 3 DAY), NOW()),
('Reunião quinta Douro', 'completed', 'high', 2, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
('Atualizar CRM leads', 'pending', 'low', 5, DATE_ADD(NOW(), INTERVAL 5 DAY), NOW()),
('Follow-up Loft Industrial', 'pending', 'high', 3, DATE_ADD(NOW(), INTERVAL 1 DAY), NOW()),
('Apresentação imóveis novo cliente', 'pending', 'medium', 4, DATE_ADD(NOW(), INTERVAL 4 DAY), NOW()),
('Revisão contratos', 'in_progress', 'urgent', 6, DATE_ADD(NOW(), INTERVAL 2 DAY), NOW());

-- 6. Create Activities
INSERT INTO activities (type, description, related_to, related_id, user_id, created_at) VALUES 
('deal_created', 'Novo negócio criado - 2025-001', 'deal', 1, 2, NOW()),
('deal_updated', 'Negócio atualizado para fase de negociação', 'deal', 2, 3, NOW()),
('client_created', 'Novo cliente adicionado - Carlos Silva', 'client', 1, 2, NOW()),
('property_created', 'Novo imóvel adicionado - Penthouse', 'property', 4, 4, NOW()),
('task_completed', 'Tarefa concluída - Reunião quinta Douro', 'task', 4, 2, NOW()),
('deal_won', 'Negócio FECHADO! - 2025-008', 'deal', 8, 3, NOW()),
('property_viewing', 'Visita agendada - Apartamento Lisboa', 'property', 1, 2, NOW()),
('client_contact', 'Contacto com cliente - Patricia Gomes', 'client', 2, 3, NOW());