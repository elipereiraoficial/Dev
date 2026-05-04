<?php
/**
 * Kanban API - Dedicated endpoint for drag & drop updates
 * Avoids page caching issues
 */

// Prevent any caching
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: application/json');

// CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../includes/auth.php';

// Check auth
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Handle the AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $pdo;
    
    error_log("kanban.php: POST received - deal_id=" . ($_POST['deal_id'] ?? 'none') . ", stage_id=" . ($_POST['stage_id'] ?? 'none'));
    
    // Validate input
    if (!isset($_POST['deal_id']) || !isset($_POST['stage_id'])) {
        error_log("kanban.php: Missing parameters");
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit;
    }
    
    // CSRF check (optional for this endpoint - already authenticated)
    // if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
    //     echo json_encode(['success' => false, 'error' => 'Invalid token']);
    //     exit;
    // }
    
    $deal_id = intval($_POST['deal_id']);
    $stage_id = intval($_POST['stage_id']);
    
    // Get stage info
    $stageStmt = $pdo->prepare("SELECT * FROM deal_stages WHERE id = ?");
    $stageStmt->execute([$stage_id]);
    $stage = $stageStmt->fetch();
    
    if (!$stage) {
        echo json_encode(['success' => false, 'error' => 'Stage not found']);
        exit;
    }
    
    // Determine status
    $status = 'open';
    $actual_close = null;
    if (!empty($stage['is_closed'])) {
        $status = !empty($stage['is_won']) ? 'won' : 'lost';
        if (!empty($stage['is_won'])) {
            $actual_close = date('Y-m-d');
        }
    }
    
    // Update
    if ($actual_close) {
        $stmt = $pdo->prepare("UPDATE deals SET stage_id = ?, status = ?, actual_close = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$stage_id, $status, $actual_close, $deal_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE deals SET stage_id = ?, status = ?, actual_close = NULL, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$stage_id, $status, $deal_id]);
    }
    
    $affected = $stmt->rowCount();
    error_log("kanban.php: Updated deal $deal_id to stage $stage_id, affected rows: $affected");
    
    // Verify
    $verify = $pdo->prepare("SELECT stage_id FROM deals WHERE id = ?");
    $verify->execute([$deal_id]);
    $newStage = $verify->fetch();
    error_log("kanban.php: Verified stage_id = " . $newStage['stage_id']);
    
    echo json_encode([
        'success' => true, 
        'deal_id' => $deal_id,
        'new_stage_id' => $newStage['stage_id'],
        'affected' => $affected,
        'status' => $status
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);