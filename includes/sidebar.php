<?php
$menu = [
    ['index.php', 'fas fa-th-large', 'Dashboard'],
    ['kanban.php', 'fas fa-columns', 'Pipeline Kanban'],
    ['clients.php', 'fas fa-users', 'Clientes'],
    ['properties.php', 'fas fa-building', 'Imóveis'],
    ['tasks.php', 'fas fa-tasks', 'Tarefas'],
    ['activities.php', 'fas fa-history', 'Atividades'],
    ['calendar.php', 'fas fa-calendar', 'Calendário'],
    ['settings.php', 'fas fa-cog', 'Definições'],
];
$user = currentUser();
?>
<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-luxury-900 text-white flex flex-col border-r border-luxury-800 transition-transform duration-300 fixed md:relative z-40 h-full -translate-x-full md:translate-x-0">
    <div class="h-16 flex items-center px-6 border-b border-luxury-800">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg gold-gradient flex items-center justify-center">
                <i class="fas fa-gem text-white text-sm"></i>
            </div>
            <span class="font-serif text-lg tracking-wide">Luxury<span class="text-luxury-gold">CRM</span></span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        <ul class="space-y-1">
            <?php foreach ($menu as $item):
                $isActive = basename($_SERVER['PHP_SELF']) === $item[0];
                $href = $item[0];
            ?>
            <li>
                <a href="<?php echo $href; ?>" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium <?php echo $isActive ? 'text-luxury-gold active' : 'text-slate-400 hover:text-white'; ?>">
                    <i class="<?php echo $item[1]; ?> w-5 text-center"></i>
                    <?php echo $item[2]; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="p-4 border-t border-luxury-800">
        <div class="bg-luxury-800/50 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-3">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name'] ?? 'User'); ?>&background=d4af37&color=0f172a" alt="" class="w-10 h-10 rounded-full">
                <div class="overflow-hidden">
                    <p class="text-sm font-semibold truncate"><?php echo clean($user['name'] ?? 'Utilizador'); ?></p>
                    <p class="text-xs text-slate-500 capitalize"><?php echo $user['role'] ?? 'agent'; ?></p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-2 text-xs font-medium text-red-400 bg-red-400/10 rounded-lg hover:bg-red-400/20 transition-colors">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>
</aside>

<!-- Mobile overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden')"></div>

<!-- Main Content Wrapper -->
<div class="flex-1 flex flex-col h-screen overflow-hidden md:ml-0">
    <!-- Top bar -->
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0">
        <div class="flex items-center gap-4">
            <button id="sidebar-toggle" class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="font-serif text-xl text-luxury-900"><?php echo clean($page); ?></h1>
        </div>
        <div class="flex items-center gap-4">
            <?php 
            $pageUrl = basename($_SERVER['PHP_SELF']);
            if ($pageUrl === 'deals.php' || $pageUrl === 'kanban.php'): ?>
            <a href="deals.php?action=new" class="hidden sm:flex items-center gap-2 px-4 py-2 gold-gradient text-luxury-900 text-sm font-semibold rounded-lg hover:opacity-90 shadow-md">
                <i class="fas fa-plus"></i> Novo Negócio
            </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Scrollable content -->
    <main class="flex-1 overflow-y-auto p-6">
