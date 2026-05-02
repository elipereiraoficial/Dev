<?php
require_once __DIR__ . '/../config.php';

// Generate CSRF Token
function csrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// Verify CSRF Token
function verifyCsrf($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Format currency (EUR)
function formatCurrency($amount) {
    return '€ ' . number_format($amount, 2, ',', '.');
}

// Format date (Portuguese style)
function formatDate($date, $withTime = false) {
    if (!$date) return '-';
    $format = $withTime ? 'd/m/Y H:i' : 'd/m/Y';
    return date($format, strtotime($date));
}

// Generate unique reference
function generateReference($prefix) {
    return strtoupper($prefix) . '-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

// Clean input
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone (Portuguese format)
function isValidPhone($phone) {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    return strlen($clean) >= 9 && strlen($clean) <= 15;
}

// Sanitize filename
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
    $filename = substr($filename, 0, 255);
    return $filename ?: 'file';
}

// Security logging
function securityLog($event, $details = []) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userId = $_SESSION['user_id'] ?? 'guest';
    $email = $_SESSION['user_email'] ?? 'unknown';
    
    $log = [
        'time' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $ip,
        'user_id' => $userId,
        'email' => $email,
        'details' => $details
    ];
    
    error_log("[SECURITY] " . json_encode($log));
}

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Activity logger
function logActivity($type, $description, $related_to = null, $related_id = null) {
    global $pdo;
    $user_id = $_SESSION['user_id'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO activities (type, description, related_to, related_id, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$type, $description, $related_to, $related_id, $user_id]);
}

// Whitelist of allowed tables for getCount
function getCount($table, $where = '', $params = []) {
    global $pdo;
    
    // Whitelist allowed tables to prevent SQL injection
    $allowedTables = ['users', 'clients', 'properties', 'deals', 'tasks', 'activities', 'deal_stages'];
    $table = strtolower(trim($table));
    
    if (!in_array($table, $allowedTables)) {
        error_log("Security: Invalid table in getCount: $table");
        return 0;
    }
    
    $sql = "SELECT COUNT(*) FROM {$table}";
    if ($where) $sql .= " WHERE {$where}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// Get single value
function getValue($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// Deal stages
function getStages() {
    global $pdo;
    return $pdo->query("SELECT * FROM deal_stages ORDER BY stage_order ASC")->fetchAll();
}

// Count deals by stage
function countDealsByStage($stage_id) {
    return getValue("SELECT COUNT(*) FROM deals WHERE stage_id = ? AND status = 'open'", [$stage_id]);
}

// Audit Trail Functions
function logAudit($action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    global $pdo;
    
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $old_json = $old_values ? json_encode($old_values) : null;
    $new_json = $new_values ? json_encode($new_values) : null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $table_name, $record_id, $old_json, $new_json, $ip, $user_agent]);
    } catch (Exception $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}

// Audit helpers for common actions
function auditCreate($table, $record_id, $data) {
    logAudit('create', $table, $record_id, null, $data);
}

function auditUpdate($table, $record_id, $old_data, $new_data) {
    logAudit('update', $table, $record_id, $old_data, $new_data);
}

function auditDelete($table, $record_id, $data) {
    logAudit('delete', $table, $record_id, $data, null);
}

function auditLogin($success = true) {
    $action = $success ? 'login_success' : 'login_failed';
    logAudit($action, 'users', $_SESSION['user_id'] ?? null);
}

// Get audit log for admin
function getAuditLog($limit = 50, $user_id = null, $action = null) {
    global $pdo;
    
    $sql = "SELECT al.*, u.name as user_name, u.email as user_email 
            FROM audit_log al 
            LEFT JOIN users u ON al.user_id = u.id 
            WHERE 1=1";
    $params = [];
    
    if ($user_id) {
        $sql .= " AND al.user_id = ?";
        $params[] = $user_id;
    }
    
    if ($action) {
        $sql .= " AND al.action = ?";
        $params[] = $action;
    }
    
    $sql .= " ORDER BY al.created_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ============================================
// MARKETING AUTOMATION FUNCTIONS
// ============================================

// Format phone for WhatsApp (convert to international format)
function formatWhatsApp($phone) {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    
    // If starts with 351 (Portugal), keep it
    if (strpos($clean, '351') === 0) {
        return 'https://wa.me/' . $clean;
    }
    
    // If starts with 9 (mobile), add 351
    if (strlen($clean) === 9 && $clean[0] === '9') {
        return 'https://wa.me/351' . $clean;
    }
    
    // Otherwise just use as is
    return 'https://wa.me/' . $clean;
}

// Send welcome email to new client
function sendWelcomeEmail($client_id) {
    global $pdo;
    
    // Check if already sent
    $stmt = $pdo->prepare("SELECT email_sent_welcome, name, email FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    
    if (!$client || $client['email_sent_welcome']) {
        return false;
    }
    
    // In production, integrate with email service (SendGrid, Mailgun, etc)
    // For now, simulate and log
    $to = $client['email'];
    $name = $client['name'];
    
    $subject = "Bem-vindo ao Luxury Estate CRM";
    $body = "Caro(a) {$name},\n\n";
    $body .= "Obrigado por se juntar ao nosso programa de clientes!\n\n";
    $body .= "Estamos ansiosos para ajudá-lo(a) a encontrar o imóvel dos seus sonhos.\n\n";
    $body .= "Em breve, um dos nossos consultores entrará em contacto.\n\n";
    $body .= "Com os melhores cumprimentos,\n";
    $body .= "Luxury Estate CRM\n";
    
    // Log the email (in production, send via API)
    error_log("[EMAIL] Welcome email to: $to");
    
    // Update database
    $stmt = $pdo->prepare("UPDATE clients SET email_sent_welcome = 1, last_email_sent = NOW() WHERE id = ?");
    $stmt->execute([$client_id]);
    
    logActivity('email_sent', "Email de boas-vindas enviado para $name", 'client', $client_id);
    
    return true;
}

// Follow-up email after 3 days without activity
function sendFollowUpEmail($client_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT email_sent_followup, name, email, last_email_sent FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    
    if (!$client || $client['email_sent_followup']) {
        return false;
    }
    
    // Check if 3 days since welcome email
    if ($client['last_email_sent']) {
        $days_since = (time() - strtotime($client['last_email_sent'])) / 86400;
        if ($days_since < 3) {
            return false;
        }
    }
    
    $to = $client['email'];
    $name = $client['name'];
    
    error_log("[EMAIL] Follow-up email to: $to");
    
    $stmt = $pdo->prepare("UPDATE clients SET email_sent_followup = 1, last_email_sent = NOW() WHERE id = ?");
    $stmt->execute([$client_id]);
    
    logActivity('email_sent', "Email de follow-up enviado para $name", 'client', $client_id);
    
    return true;
}

// Deal status notification
function notifyDealStatus($deal_id, $status) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT d.*, c.name as client_name, c.email as client_email, 
               u.name as agent_name, u.email as agent_email
        FROM deals d
        LEFT JOIN clients c ON d.client_id = c.id
        LEFT JOIN users u ON d.agent_id = u.id
        WHERE d.id = ?
    ");
    $stmt->execute([$deal_id]);
    $deal = $stmt->fetch();
    
    if (!$deal) return false;
    
    if ($status === 'won') {
        error_log("[EMAIL] Deal WON notification: {$deal['reference']} - {$deal['client_name']}");
        $stmt = $pdo->prepare("UPDATE deals SET email_sent_client = 1 WHERE id = ?");
        $stmt->execute([$deal_id]);
        logActivity('email_sent', "Notificação de negócio ganho enviada", 'deal', $deal_id);
    } elseif ($status === 'lost') {
        error_log("[EMAIL] Deal LOST notification: {$deal['reference']} - {$deal['client_name']}");
        $stmt = $pdo->prepare("UPDATE deals SET email_sent_client = 1 WHERE id = ?");
        $stmt->execute([$deal_id]);
        logActivity('email_sent', "Notificação de negócio perdido enviada", 'deal', $deal_id);
    }
    
    return true;
}

// Get automation stats
function getAutomationStats() {
    global $pdo;
    
    $stats = [];
    
    $stats['welcome_emails'] = $pdo->query("SELECT COUNT(*) FROM clients WHERE email_sent_welcome = 1")->fetchColumn();
    $stats['followup_emails'] = $pdo->query("SELECT COUNT(*) FROM clients WHERE email_sent_followup = 1")->fetchColumn();
    $stats['total_clients'] = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    $stats['pending_followup'] = $pdo->query("SELECT COUNT(*) FROM clients WHERE email_sent_welcome = 1 AND email_sent_followup = 0")->fetchColumn();
    
    return $stats;
}
