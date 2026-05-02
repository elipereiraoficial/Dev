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

// Get count for dashboard
function getCount($table, $where = '', $params = []) {
    global $pdo;
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
