<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
function requireAuth() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    // Check for session timeout (30 minutes)
    if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity'] > 1800)) {
        logout();
    }
    $_SESSION['_last_activity'] = time();
}

// Check admin role
function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        setFlash('error', 'Acesso restrito a administradores.');
        header('Location: index.php');
        exit;
    }
}

// Get current user
function currentUser() {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Check rate limiting
function checkLoginRateLimit($email) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'login_attempts_' . md5($email . $ip);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
    }
    
    $attempt = $_SESSION[$key];
    $window = defined('LOGIN_ATTEMPTS_WINDOW') ? LOGIN_ATTEMPTS_WINDOW : 900;
    $max = defined('LOGIN_ATTEMPTS_MAX') ? LOGIN_ATTEMPTS_MAX : 5;
    
    // Reset if window expired
    if (time() - $attempt['first_attempt'] > $window) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
        return true;
    }
    
    // Check limit
    if ($attempt['attempts'] >= $max) {
        $remaining = $window - (time() - $attempt['first_attempt']);
        if ($remaining > 0) {
            return ['locked' => true, 'remaining' => $remaining];
        }
    }
    
    return true;
}

function recordLoginAttempt($email) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'login_attempts_' . md5($email . $ip);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
    }
    $_SESSION[$key]['attempts']++;
}

// Login user
function login($email, $password) {
    global $pdo;
    
    // Check rate limit first
    $rateCheck = checkLoginRateLimit($email);
    if (isset($rateCheck['locked'])) {
        setFlash('error', 'Demasiadas tentativas. Tente novamente em ' . ceil($rateCheck['remaining']/60) . ' minutos.');
        return 'rate_limited';
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['_last_activity'] = time();
        $_SESSION['_login_time'] = time();
        
        // Clear failed attempt counter
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'login_attempts_' . md5($email . $ip);
        unset($_SESSION[$key]);
        
        // Audit login success
        auditLogin(true);
        
        seedTestDataIfNeeded();
        
        return true;
    }
    
    // Record failed attempt
    recordLoginAttempt($email);
    
    // Audit login failed
    auditLogin(false);
    
    // Log failed login attempt
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    error_log("[SECURITY] Failed login attempt for email: $email from IP: $ip");
    
    return false;
}

function seedTestDataIfNeeded() {
    try {
        global $pdo;
        
        $userCount = $pdo->query("SELECT COUNT(*) FROM users WHERE id > 1")->fetchColumn();
        if ($userCount > 0) return;
        
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
        
        $pdo->exec("INSERT INTO properties (reference, title, type, address, city, region, price, status, description, bedrooms, bathrooms, area_m2, featured, created_at) VALUES 
            ('LIS-001', 'Apartamento T2 Centro', 'apartment', 'Prça do Rossio, 25', 'Lisboa', 'Lisboa', 450000, 'available', 'Apartamento T2 no centro de Lisboa', 2, 1, 85, 1, NOW()),
            ('CAS-001', 'Moradia Moderna Cascais', 'house', 'Rua da Praia, 45', 'Cascais', 'Lisboa', 1250000, 'available', 'Moradia moderna com piscina', 4, 3, 250, 1, NOW()),
            ('LIS-003', 'Loft Industrial Alfama', 'apartment', 'Rua de Santa Cruz, 12', 'Lisboa', 'Lisboa', 380000, 'available', 'Loft industrial renovado', 1, 1, 65, 0, NOW()),
            ('POR-001', 'Penthouse Vista Rio', 'apartment', 'Av. da Boavista, 500', 'Porto', 'Porto', 890000, 'reserved', 'Penthouse de luxo', 3, 2, 150, 1, NOW()),
            ('DOU-001', 'Quinta com Vinha Douro', 'land', 'Estrada Nacional 222', 'Douro', 'Norte', 2500000, 'available', 'Quinta histórica com vinha', 0, 0, 5000, 0, NOW())");
        
        $pdo->exec("INSERT INTO clients (name, email, phone, status, source, created_at) VALUES 
            ('Carlos Silva', 'carlos.silva@email.pt', '+351 912 345 678', 'active', 'website', NOW()),
            ('Patricia Gomes', 'patricia.gomes@email.pt', '+351 933 456 789', 'active', 'website', NOW()),
            ('Manuel Ferreira', 'manuel.ferreira@email.pt', '+351 914 567 890', 'active', 'referral', NOW()),
            ('Isabel Costa', 'isabel.costa@email.pt', '+351 965 678 901', 'active', 'website', NOW()),
            ('Jorge Martins', 'jorge.martins@email.pt', '+351 926 789 012', 'inactive', 'website', NOW()),
            ('Susana Almeida', 'susana.almeida@email.pt', '+351 937 890 123', 'active', 'website', NOW()),
            ('Paulo Rodrigues', 'paulo.rodrigues@email.pt', '+351 948 901 234', 'active', 'referral', NOW()),
            ('Renata Sousa', 'renata.sousa@email.pt', '+351 959 012 345', 'active', 'website', NOW())");
        
        $stages = $pdo->query("SELECT id FROM deal_stages ORDER BY stage_order")->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($stages) >= 8) {
            $pdo->exec("INSERT INTO deals (reference, property_id, client_id, stage_id, status, value, title, created_at, updated_at) VALUES 
                ('2025-001', 1, 1, {$stages[0]}, 'open', 420000, 'Apartamento T2 - Primeira visita', NOW(), NOW()),
                ('2025-002', 2, 2, {$stages[1]}, 'open', 1200000, 'Moradia Cascais - Negociação', NOW(), NOW()),
                ('2025-003', 3, 3, {$stages[2]}, 'open', 350000, 'Loft - Proposta enviada', NOW(), NOW()),
                ('2025-004', 4, 4, {$stages[3]}, 'open', 850000, 'Penthouse - Resposta pendente', NOW(), NOW()),
                ('2025-005', 5, 5, {$stages[4]}, 'open', 2300000, 'Quinta Douro - Due diligence', NOW(), NOW()),
                ('2025-006', 1, 6, {$stages[5]}, 'open', 410000, 'Apartamento - Documentação', NOW(), NOW()),
                ('2025-007', 2, 7, {$stages[6]}, 'open', 1150000, 'Moradia - Contrato promessa', NOW(), NOW()),
                ('2025-008', 3, 8, {$stages[7]}, 'won', 380000, 'Loft - FECHADO!', NOW(), NOW()),
                ('2025-012', 3, 4, {$stages[2]}, 'lost', 360000, 'Loft - Perdeu', NOW(), NOW())");
        }
        
        $pdo->exec("INSERT INTO tasks (title, status, priority, assigned_to, due_date, created_at) VALUES 
            ('Visitar apartamento Lisboa', 'pending', 'high', 2, DATE_ADD(NOW(), INTERVAL 2 DAY), NOW()),
            ('Preparar proposta moradia', 'in_progress', 'urgent', 3, DATE_ADD(NOW(), INTERVAL 1 DAY), NOW()),
            ('Contactar cliente penthouse', 'pending', 'medium', 4, DATE_ADD(NOW(), INTERVAL 3 DAY), NOW()),
            ('Reunião quinta Douro', 'completed', 'high', 2, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
            ('Atualizar CRM leads', 'pending', 'low', 5, DATE_ADD(NOW(), INTERVAL 5 DAY), NOW())");
        
        $pdo->exec("INSERT INTO activities (type, description, related_to, related_id, user_id, created_at) VALUES 
            ('deal_created', 'Novo negócio criado - 2025-001', 'deal', 1, 1, NOW()),
            ('deal_updated', 'Negócio atualizado para negociação', 'deal', 2, 1, NOW()),
            ('client_created', 'Novo cliente adicionado', 'client', 1, 1, NOW()),
            ('property_created', 'Novo imóvel adicionado', 'property', 1, 1, NOW()),
            ('task_completed', 'Tarefa concluída', 'task', 4, 1, NOW()),
            ('deal_won', 'Negócio FECHADO!', 'deal', 8, 1, NOW())");
    } catch (Exception $e) {
        error_log("Seed error: " . $e->getMessage());
    }
}

// Logout
function logout() {
    // Clear session data
    $_SESSION = [];
    
    // Delete session cookie
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, 
        $params['path'], $params['domain'], 
        $params['secure'], $params['httponly']);
    
    session_destroy();
    header('Location: login.php');
    exit;
}

// Two-Factor Authentication Functions
function generateTwoFactorCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendTwoFactorCode($email) {
    global $pdo;
    
    $code = generateTwoFactorCode();
    $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    // Store code temporarily (in session, not database for security)
    $_SESSION['2fa_pending'] = [
        'code' => $code,
        'email' => $email,
        'expires' => strtotime($expires)
    ];
    
    // In production, send via email/SMS
    // For now, we'll show it in development
    error_log("[2FA] Code for $email: $code");
    
    return $code;
}

function verifyTwoFactorCode($code) {
    if (!isset($_SESSION['2fa_pending'])) {
        return false;
    }
    
    $pending = $_SESSION['2fa_pending'];
    
    // Check expiration
    if (time() > $pending['expires']) {
        unset($_SESSION['2fa_pending']);
        return false;
    }
    
    // Verify code
    if ($code === $pending['code']) {
        unset($_SESSION['2fa_pending']);
        return true;
    }
    
    return false;
}

function isTwoFactorEnabled($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result && $result['two_factor_enabled'] == 1;
}

function enableTwoFactor($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET two_factor_enabled = 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    logAudit('2fa_enabled', 'users', $user_id);
}

function disableTwoFactor($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET two_factor_enabled = 0, two_factor_secret = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    logAudit('2fa_disabled', 'users', $user_id);
}
