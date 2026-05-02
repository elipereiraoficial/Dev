<?php
require_once 'includes/auth.php';
requireAuth();

$page = 'Atividades';

$typeFilter = $_GET['type'] ?? 'all';
$userFilter = $_GET['user'] ?? 'all';

$sql = "SELECT a.*, u.name as user_name, c.name as client_name, p.title as property_name, d.title as deal_name
        FROM activities a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN clients c ON a.related_to = 'client' AND a.related_id = c.id
        LEFT JOIN properties p ON a.related_to = 'property' AND a.related_id = p.id
        LEFT JOIN deals d ON a.related_to = 'deal' AND a.related_id = d.id
        WHERE 1=1";

$params = [];

if ($typeFilter !== 'all') {
    $sql .= " AND a.type = ?";
    $params[] = $typeFilter;
}

if ($userFilter !== 'all') {
    $sql .= " AND a.user_id = ?";
    $params[] = $userFilter;
}

$sql .= " ORDER BY a.created_at DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll();

$users = $pdo->query("SELECT id, name FROM users WHERE active = true ORDER BY name ASC")->fetchAll();

$typeLabels = [
    'call' => 'Chamada',
    'meeting' => 'Reunião',
    'email' => 'Email',
    'visit' => 'Visita',
    'note' => 'Nota',
    'task' => 'Tarefa',
    'status_change' => 'Mudança de Estado',
    'created' => 'Criado',
    'updated' => 'Atualizado'
];

$typeIcons = [
    'call' => 'fa-phone',
    'meeting' => 'fa-users',
    'email' => 'fa-envelope',
    'visit' => 'fa-home',
    'note' => 'fa-sticky-note',
    'task' => 'fa-tasks',
    'status_change' => 'fa-exchange-alt',
    'created' => 'fa-plus-circle',
    'updated' => 'fa-edit'
];

$typeColors = [
    'call' => 'bg-blue-100 text-blue-600',
    'meeting' => 'bg-purple-100 text-purple-600',
    'email' => 'bg-slate-100 text-slate-600',
    'visit' => 'bg-emerald-100 text-emerald-600',
    'note' => 'bg-amber-100 text-amber-600',
    'task' => 'bg-cyan-100 text-cyan-600',
    'status_change' => 'bg-orange-100 text-orange-600',
    'created' => 'bg-emerald-100 text-emerald-600',
    'updated' => 'bg-blue-100 text-blue-600'
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-wrap">
            <h3 class="font-semibold text-luxury-900">Histórico de Atividades</h3>
            <a href="activities.php?type=all&user=<?php echo $userFilter; ?>" class="px-2 py-1 text-xs rounded-full <?php echo $typeFilter === 'all' ? 'bg-luxury-gold text-luxury-900' : 'bg-slate-100 text-slate-600'; ?>">Todas</a>
            <?php foreach (['call', 'meeting', 'email', 'visit', 'note', 'status_change'] as $t): ?>
            <a href="activities.php?type=<?php echo $t; ?>&user=<?php echo $userFilter; ?>" class="px-2 py-1 text-xs rounded-full <?php echo $typeFilter === $t ? 'bg-luxury-gold text-luxury-900' : 'bg-slate-100 text-slate-600'; ?>"><?php echo $typeLabels[$t]; ?></a>
            <?php endforeach; ?>
        </div>
        <div class="flex items-center gap-2">
            <select onchange="window.location.href='activities.php?type=<?php echo $typeFilter; ?>&user='+this.value" class="px-3 py-2 text-sm rounded-lg border border-slate-200 focus:border-luxury-gold outline-none">
                <option value="all" <?php echo $userFilter === 'all' ? 'selected' : ''; ?>>Todos os utilizadores</option>
                <?php foreach ($users as $u): ?>
                <option value="<?php echo $u['id']; ?>" <?php echo $userFilter == $u['id'] ? 'selected' : ''; ?>><?php echo clean($u['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="divide-y divide-slate-100">
        <?php foreach ($activities as $act): ?>
        <div class="p-4 hover:bg-slate-50/50 transition-colors">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full <?php echo $typeColors[$act['type']]; ?> flex items-center justify-center flex-shrink-0">
                    <i class="fas <?php echo $typeIcons[$act['type']]; ?>"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-luxury-900"><?php echo clean($act['user_name'] ?? 'Sistema'); ?></span>
                        <span class="text-xs px-1.5 py-0.5 rounded <?php echo $typeColors[$act['type']]; ?>"><?php echo $typeLabels[$act['type']]; ?></span>
                    </div>
                    <p class="text-sm text-slate-600 mb-1"><?php echo clean($act['description']); ?></p>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                        <span><i class="far fa-clock mr-1"></i><?php echo formatDate($act['created_at'], true); ?></span>
                        <?php if ($act['deal_name']): ?>
                        <span><i class="fas fa-handshake mr-1"></i><?php echo clean($act['deal_name']); ?></span>
                        <?php elseif ($act['client_name']): ?>
                        <span><i class="fas fa-user mr-1"></i><?php echo clean($act['client_name']); ?></span>
                        <?php elseif ($act['property_name']): ?>
                        <span><i class="fas fa-building mr-1"></i><?php echo clean($act['property_name']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($activities)): ?>
        <div class="p-12 text-center text-slate-400">Nenhuma atividade encontrada.</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>