<?php
/**
 * AGENT SQUAD - Main Menu
 * Run: https://crm.elipereira.com/scripts/agents/
 */

require_once __DIR__ . '/../../config.php';

$agent = $_GET['agent'] ?? 'menu';

if ($agent === 'menu') {
    ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Squad - Luxury CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-luxury-900 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl text-white font-serif mb-2">🛡️ <span class="text-luxury-gold">Agent Squad</span></h1>
        <p class="text-slate-400 mb-8">Luxury CRM - Automated Development Team</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Auditor -->
            <a href="?agent=auditor" class="block bg-red-500/10 border border-red-500/30 rounded-2xl p-6 hover:bg-red-500/20 transition-colors">
                <div class="text-4xl mb-3">🔴</div>
                <h2 class="text-xl font-bold text-white">Auditor Agent</h2>
                <p class="text-slate-400 text-sm mt-2">Security scan & code review</p>
            </a>
            
            <!-- Tester -->
            <a href="?agent=tester" class="block bg-blue-500/10 border border-blue-500/30 rounded-2xl p-6 hover:bg-blue-500/20 transition-colors">
                <div class="text-4xl mb-3">🔵</div>
                <h2 class="text-xl font-bold text-white">Tester Agent</h2>
                <p class="text-slate-400 text-sm mt-2">Functional testing & validation</p>
            </a>
            
            <!-- Pain Tester -->
            <a href="?agent=pain" class="block bg-green-500/10 border border-green-500/30 rounded-2xl p-6 hover:bg-green-500/20 transition-colors">
                <div class="text-4xl mb-3">🟢</div>
                <h2 class="text-xl font-bold text-white">Pain Tester Agent</h2>
                <p class="text-slate-400 text-sm mt-2">Stress tests & edge cases</p>
            </a>
            
            <!-- Deploy -->
            <a href="?agent=deploy" class="block bg-yellow-500/10 border border-yellow-500/30 rounded-2xl p-6 hover:bg-yellow-500/20 transition-colors">
                <div class="text-4xl mb-3">🟡</div>
                <h2 class="text-xl font-bold text-white">Deploy Agent</h2>
                <p class="text-slate-400 text-sm mt-2">Database upgrades & deployment</p>
            </a>
            
            <!-- Docs -->
            <a href="?agent=docs" class="block bg-purple-500/10 border border-purple-500/30 rounded-2xl p-6 hover:bg-purple-500/20 transition-colors col-span-2">
                <div class="text-4xl mb-3">🟣</div>
                <h2 class="text-xl font-bold text-white">Docs Agent</h2>
                <p class="text-slate-400 text-sm mt-2">Documentation & updates</p>
            </a>
        </div>
        
        <div class="mt-8 bg-luxury-800/50 rounded-xl p-6">
            <h3 class="text-white font-semibold mb-3">🚀 Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                <a href="../upgrade.php" class="px-4 py-2 bg-luxury-gold text-luxury-900 rounded-lg font-medium hover:opacity-90">Run Full Upgrade</a>
                <a href="?agent=tester" class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600">Run All Tests</a>
                <a href="?agent=auditor" class="px-4 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600">Security Scan</a>
            </div>
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}

// Route to specific agent
$scripts = [
    'auditor' => 'auditor.php',
    'tester' => 'tester.php',
    'pain' => 'pain_tester.php',
    'deploy' => 'deploy.php',
    'docs' => 'docs.php'
];

if (isset($scripts[$agent])) {
    require $scripts[$agent];
} else {
    echo "Unknown agent: $agent";
}