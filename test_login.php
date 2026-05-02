<?php
// Direct test and fix
$host = 'localhost';
$db   = 'u415107443_luxury_crm';
$user = 'u415107443_luxury_user';
$pass = 'Cadu5540!!';

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;font-family:monospace;'>";
echo "=== TESTING LOGIN SYSTEM ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    
    // 1. Check if table exists
    echo "1. Checking users table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->fetch()) {
        echo "   ✅ Table 'users' exists\n";
    } else {
        echo "   ❌ Table 'users' does NOT exist!\n";
        exit;
    }
    
    // 2. Check all users
    echo "\n2. All users in database:\n";
    $stmt = $pdo->query("SELECT id, email, name, active, role FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    // 3. Try the exact query from auth.php
    echo "\n3. Testing login query (email = 'admin@luxury.pt', active = 1):\n";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
    $stmt->execute(['admin@luxury.pt']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "   ✅ User found!\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Active: " . $user['active'] . "\n";
        echo "   Password hash: " . substr($user['password'], 0, 40) . "...\n";
        
        // Test password
        echo "\n4. Testing password 'admin123':\n";
        if (password_verify('admin123', $user['password'])) {
            echo "   ✅ PASSWORD VERIFIED!\n";
        } else {
            echo "   ❌ PASSWORD WRONG!\n";
            echo "   Generating new hash...\n";
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$newHash, 'admin@luxury.pt']);
            echo "   ✅ Password updated!\n";
        }
    } else {
        echo "   ❌ User NOT found with active=1\n";
        echo "   Trying with active=anything...\n";
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['admin@luxury.pt']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "   Found user! Active value: '" . $user['active'] . "'\n";
            echo "   Fixing...\n";
            $stmt = $pdo->prepare("UPDATE users SET active = 1 WHERE email = ?");
            $stmt->execute(['admin@luxury.pt']);
            echo "   ✅ Fixed active to 1!\n";
        }
    }
    
    echo "\n=== FINAL RESULT ===\n";
    echo "URL: https://crm.elipereira.com\n";
    echo "Email: admin@luxury.pt\n";
    echo "Password: admin123\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";