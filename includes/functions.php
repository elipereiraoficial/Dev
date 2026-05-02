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
