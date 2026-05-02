<?php
// Debug error after login
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'u415107443_luxury_crm';
$user = 'u415107443_luxury_user';
$pass = 'Cadu5540!!';

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== DEBUGGING DASHBOARD ERRORS ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test each query from index.php
    
    // 1. Total deals
    echo "1. Total Deals (status != 'lost'):\n";
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM deals WHERE status != 'lost'")->fetchColumn();
        echo "   Result: $result\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
    
    // 2. Won deals
    echo "\n2. Won Deals this month:\n";
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM deals WHERE status = 'won' AND MONTH(actual_close) = MONTH(CURDATE()) AND YEAR(actual_close) = YEAR(CURDATE())")->fetchColumn();
        echo "   Result: $result\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
    
    // 3. Properties
    echo "\n3. Available Properties:\n";
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn();
        echo "   Result: $result\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
    
    // 4. Clients
    echo "\n4. Active Clients:\n";
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM clients WHERE status = 'active'")->fetchColumn();
        echo "   Result: $result\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
    
    // 5. Pipeline value
    echo "\n5. Pipeline Value (status = 'open'):\n";
    try {
        $result = $pdo->query("SELECT COALESCE(SUM(value), 0) FROM deals WHERE status = 'open'")->fetchColumn();
        echo "   Result: $result\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
    
    // 6. Recent deals
    echo "\n6. Recent Deals:\n";
    try {
        $stmt = $pdo->query("
            SELECT d.*, c.name as client_name, p.title as property_title, s.name as stage_name, s.color as stage_color
            FROM deals d
            LEFT JOIN clients c ON d.client_id = c.id
            LEFT JOIN properties p ON d.property_id = p.id
            LEFT JOIN deal_stages s ON d.stage_id = s.id
            ORDER BY d.updated_at DESC LIMIT 5
        ");
        $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "   Found: " . count($deals) . " deals\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
    
    // 7. Deal stages
    echo "\n7. Deal Stages:\n";
    try {
        $stmt = $pdo->query("SELECT id, name, stage_order FROM deal_stages ORDER BY stage_order");
        $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "   Found: " . count($stages) . " stages\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== ALL QUERIES TESTED ===\n";
    echo "If all show 'Result: ...' then queries are OK!\n";
    echo "The error is in PHP code, not SQL.\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";