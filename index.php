<?php
require_once 'includes/auth.php';
requireAuth();

$page = 'Dashboard';

// Stats
$totalDeals = $pdo->query("SELECT COUNT(*) FROM deals WHERE status != 'lost'")->fetchColumn() ?: 0;
$wonDeals = $pdo->query("SELECT COUNT(*) FROM deals WHERE status = 'won' AND MONTH(actual_close) = MONTH(CURDATE()) AND YEAR(actual_close) = YEAR(CURDATE())")->fetchColumn() ?: 0;
$totalProps = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn() ?: 0;
$totalClients = $pdo->query("SELECT COUNT(*) FROM clients WHERE status = 'active'")->fetchColumn() ?: 0;
$pipelineValue = $pdo->query("SELECT COALESCE(SUM(value), 0) FROM deals WHERE status = 'open'")->fetchColumn() ?: 0;

// Recent deals
$recentDeals = $pdo->query("
    SELECT d.*, c.name as client_name, p.title as property_title, s.name as stage_name, s.color as stage_color
    FROM deals d
    LEFT JOIN clients c ON d.client_id = c.id
    LEFT JOIN properties p ON d.property_id = p.id
    LEFT JOIN deal_stages s ON d.stage_id = s.id
    ORDER BY d.updated_at DESC LIMIT 5
")->fetchAll();

// Upcoming tasks
$upcomingTasks = $pdo->query("
    SELECT t.*, u.name as assigned_name
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.id
    WHERE t.status != 'completed' AND (t.due_date IS NULL OR t.due_date >= CURDATE())
    ORDER BY t.due_date ASC LIMIT 5
")->fetchAll();

// Recent activities
$recentActivities = $pdo->query("
    SELECT a.*, u.name as user_name
    FROM activities a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC LIMIT 6
")->fetchAll();

// Kanban Pipeline Data
$stages = getStages();
$dealsData = $pdo->query("
    SELECT d.*, c.name as client_name, p.title as property_title
    FROM deals d
    LEFT JOIN clients c ON d.client_id = c.id
    LEFT JOIN properties p ON d.property_id = p.id
    WHERE d.status != 'lost' OR (d.status = 'lost' AND d.updated_at > NOW() - INTERVAL 30 DAY)
    ORDER BY d.updated_at DESC
")->fetchAll();

$dealsByStage = [];
foreach ($stages as $stage) {
    $dealsByStage[$stage['id']] = array_filter($dealsData, fn($d) => $d['stage_id'] == $stage['id']);
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <a href="deals.php" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 card-hover hover:border-luxury-gold transition-colors">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                <i class="fas fa-handshake text-blue-600 text-lg"></i>
            </div>
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Ativos</span>
        </div>
        <h3 class="text-2xl font-bold text-luxury-900"><?php echo $totalDeals; ?></h3>
        <p class="text-sm text-slate-500 mt-1">Negócios Ativos</p>
    </a>

    <a href="deals.php?filter=won" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 card-hover hover:border-luxury-gold transition-colors">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                <i class="fas fa-trophy text-emerald-600 text-lg"></i>
            </div>
            <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-full">Este Mês</span>
        </div>
        <h3 class="text-2xl font-bold text-luxury-900"><?php echo $wonDeals; ?></h3>
        <p class="text-sm text-slate-500 mt-1">Fechados Ganho</p>
    </a>

    <a href="deals.php" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 card-hover hover:border-luxury-gold transition-colors">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-luxury-gold/10 flex items-center justify-center">
                <i class="fas fa-euro-sign text-luxury-gold text-lg"></i>
            </div>
            <span class="text-xs font-medium text-luxury-gold bg-luxury-gold/10 px-2 py-1 rounded-full">Pipeline</span>
        </div>
        <h3 class="text-2xl font-bold text-luxury-900"><?php echo formatCurrency($pipelineValue); ?></h3>
        <p class="text-sm text-slate-500 mt-1">Valor em Aberto</p>
    </a>

    <a href="properties.php" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 card-hover hover:border-luxury-gold transition-colors">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center">
                <i class="fas fa-building text-violet-600 text-lg"></i>
            </div>
            <span class="text-xs font-medium text-violet-600 bg-violet-50 px-2 py-1 rounded-full">Ativos</span>
        </div>
        <h3 class="text-2xl font-bold text-luxury-900"><?php echo $totalProps; ?></h3>
        <p class="text-sm text-slate-500 mt-1">Imóveis</p>
    </a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Recent Deals -->
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-luxury-900">Negócios Recentes</h3>
            <a href="deals.php" class="text-sm text-luxury-gold hover:text-luxury-gold-dark font-medium">Ver Todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Referência</th>
                        <th class="text-left px-6 py-3 font-medium">Cliente</th>
                        <th class="text-left px-6 py-3 font-medium">Imóvel</th>
                        <th class="text-right px-6 py-3 font-medium">Valor</th>
                        <th class="text-center px-6 py-3 font-medium">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($recentDeals as $deal): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-3">
                            <a href="deals.php?edit=<?php echo $deal['id']; ?>" class="font-medium text-luxury-gold hover:text-luxury-gold-dark"><?php echo clean($deal['reference']); ?></a>
                        </td>
                        <td class="px-6 py-3">
                            <?php if ($deal['client_id']): ?>
                            <a href="clients.php?edit=<?php echo $deal['client_id']; ?>" class="text-slate-600 hover:text-luxury-gold"><?php echo clean($deal['client_name'] ?? '-'); ?></a>
                            <?php else: ?>
                            <span class="text-slate-600"><?php echo clean($deal['client_name'] ?? '-'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3 text-slate-600 truncate max-w-xs"><?php echo clean($deal['property_title'] ?? '-'); ?></td>
                        <td class="px-6 py-3 text-right font-semibold text-luxury-900"><?php echo formatCurrency($deal['value']); ?></td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium" style="background-color: <?php echo $deal['stage_color']; ?>20; color: <?php echo $deal['stage_color']; ?>">
                                <?php echo clean($deal['stage_name']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentDeals)): ?>
                    <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Nenhum negócio registado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Tasks -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-luxury-900">Próximas Tarefas</h3>
                <a href="tasks.php" class="text-sm text-luxury-gold hover:text-luxury-gold-dark font-medium">Ver Todas</a>
            </div>
            <div class="divide-y divide-slate-100">
                <?php foreach ($upcomingTasks as $task): ?>
                <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 w-4 h-4 rounded border-2 border-slate-300 flex-shrink-0 cursor-pointer hover:border-luxury-gold"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-luxury-900 truncate"><?php echo clean($task['title']); ?></p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-slate-500"><i class="far fa-clock mr-1"></i><?php echo $task['due_date'] ? formatDate($task['due_date']) : 'Sem data'; ?></span>
                                <span class="text-xs px-1.5 py-0.5 rounded <?php echo $task['priority'] === 'urgent' ? 'bg-red-100 text-red-600' : ($task['priority'] === 'high' ? 'bg-orange-100 text-orange-600' : 'bg-slate-100 text-slate-500'); ?>"><?php echo ucfirst($task['priority']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($upcomingTasks)): ?>
                <div class="px-6 py-8 text-center text-slate-400 text-sm">Nenhuma tarefa pendente.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-luxury-900">Atividade Recente</h3>
            </div>
            <div class="p-6 space-y-4">
                <?php foreach ($recentActivities as $act): ?>
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-luxury-gold/10 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-luxury-gold text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-700">
                            <span class="font-medium text-luxury-900"><?php echo clean($act['user_name'] ?? 'Sistema'); ?></span>
                            <?php echo clean($act['description']); ?>
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5"><?php echo formatDate($act['created_at'], true); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($recentActivities)): ?>
                <div class="text-center text-slate-400 text-sm">Sem atividade recente.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Pipeline Kanban -->
<div class="mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-lg text-luxury-900">Pipeline de Negócios</h3>
        <a href="deals.php" class="text-sm text-luxury-gold hover:text-luxury-gold-dark font-medium">Ver Pipeline Completo</a>
    </div>
    <div class="flex gap-4 overflow-x-auto pb-4">
        <?php foreach ($stages as $stage):
            $stageDeals = $dealsByStage[$stage['id']] ?? [];
            $stageValue = array_sum(array_column($stageDeals, 'value'));
        ?>
        <div class="flex-shrink-0 w-64 flex flex-col">
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" style="background-color: <?php echo $stage['color']; ?>"></div>
                    <h4 class="font-semibold text-sm text-luxury-900"><?php echo clean($stage['name']); ?></h4>
                    <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium"><?php echo count($stageDeals); ?></span>
                </div>
            </div>
            <div class="bg-slate-100/70 rounded-2xl p-3 space-y-2 min-h-[100px]">
                <?php foreach ($stageDeals as $deal): ?>
                <a href="deals.php?edit=<?php echo $deal['id']; ?>" class="block bg-white rounded-lg p-3 shadow-sm border border-slate-100 hover:border-luxury-gold transition-colors">
                    <div class="flex items-start justify-between mb-1">
                        <span class="text-xs font-bold text-luxury-gold"><?php echo clean($deal['reference']); ?></span>
                    </div>
                    <p class="text-xs text-slate-600 truncate"><?php echo clean($deal['property_title'] ?? $deal['title']); ?></p>
                    <p class="text-xs text-slate-400 truncate"><?php echo clean($deal['client_name'] ?? ''); ?></p>
                    <div class="mt-2 pt-2 border-t border-slate-50 flex justify-between items-center">
                        <span class="text-xs font-bold text-luxury-900"><?php echo formatCurrency($deal['value']); ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php if (empty($stageDeals)): ?>
                <p class="text-xs text-slate-400 text-center py-4">Sem negócios</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
