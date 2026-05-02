<?php
require_once 'includes/auth.php';
requireAuth();

$page = 'Calendário';

$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

$monthName = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$startDayOfWeek = date('N', $firstDay);

$tasks = $pdo->query("
    SELECT id, title, due_date, priority, related_to, 'task' as type
    FROM tasks 
    WHERE EXTRACT(MONTH FROM due_date) = $month AND EXTRACT(YEAR FROM due_date) = $year AND due_date IS NOT NULL
")->fetchAll();

$eventsByDay = [];
foreach ($tasks as $task) {
    $day = intval(date('j', strtotime($task['due_date'])));
    if (!isset($eventsByDay[$day])) $eventsByDay[$day] = [];
    $eventsByDay[$day][] = $task;
}

$deals = $pdo->query("
    SELECT id, title, expected_close, stage_id, 'deal' as type
    FROM deals 
    WHERE MONTH(expected_close) = $month AND YEAR(expected_close) = $year AND expected_close IS NOT NULL AND status = 'open'
")->fetchAll();

foreach ($deals as $deal) {
    $day = intval(date('j', strtotime($deal['expected_close'])));
    if (!isset($eventsByDay[$day])) $eventsByDay[$day] = [];
    $eventsByDay[$day][] = ['id' => $deal['id'], 'title' => $deal['title'], 'type' => 'deal', 'priority' => 'medium'];
}

$priorityColors = [
    'urgent' => 'bg-red-500 text-white',
    'high' => 'bg-orange-500 text-white',
    'medium' => 'bg-luxury-gold text-luxury-900',
    'low' => 'bg-slate-400 text-white'
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h3 class="font-serif text-xl text-luxury-900"><?php echo $monthName[$month]; ?> <?php echo $year; ?></h3>
            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" class="text-sm text-luxury-gold hover:text-luxury-gold-dark font-medium">Hoje</a>
    </div>

    <div class="grid grid-cols-7 border-b border-slate-100">
        <?php foreach (['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'] as $dayName): ?>
        <div class="py-3 text-center text-sm font-medium text-slate-500"><?php echo $dayName; ?></div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-7">
        <?php for ($i = 1; $i < $startDayOfWeek; $i++): ?>
        <div class="min-h-32 border-b border-r border-slate-100 bg-slate-50/50"></div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
        <?php 
            $isToday = $day == date('j') && $month == date('n') && $year == date('Y');
            $events = $eventsByDay[$day] ?? [];
        ?>
        <div class="min-h-32 border-b border-r border-slate-100 p-2 <?php echo $isToday ? 'bg-luxury-gold/5' : ''; ?>">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium <?php echo $isToday ? 'text-luxury-gold' : 'text-slate-600'; ?>"><?php echo $day; ?></span>
                <?php if ($isToday): ?>
                <span class="w-2 h-2 rounded-full bg-luxury-gold"></span>
                <?php endif; ?>
            </div>
            <div class="space-y-1">
                <?php foreach ($events as $event): 
                    $isDeal = ($event['type'] ?? 'task') === 'deal';
                    $link = $isDeal ? 'deals.php?edit=' . $event['id'] : 'tasks.php?edit=' . $event['id'];
                ?>
                <a href="<?php echo $link; ?>" class="block text-xs truncate px-1.5 py-0.5 rounded hover:opacity-80 <?php echo $priorityColors[$event['priority']] ?? $priorityColors['medium']; ?>" 
                     title="<?php echo clean($event['title']); ?>">
                    <?php echo clean($event['title']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endfor; ?>

        <?php $remainingCells = 7 - (($startDayOfWeek - 1 + $daysInMonth) % 7); ?>
        <?php if ($remainingCells < 7): ?>
        <?php for ($i = 0; $i < $remainingCells; $i++): ?>
        <div class="min-h-24 border-b border-slate-100 bg-slate-50/50"></div>
        <?php endfor; ?>
        <?php endif; ?>
    </div>

    <!-- Legend -->
    <div class="px-6 py-4 border-t border-slate-100 flex items-center gap-4 text-xs">
        <span class="text-slate-500">Legenda:</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-red-500"></span> Urgente</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-orange-500"></span> Alta</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-luxury-gold"></span> Média</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-slate-400"></span> Baixa</span>
    </div>
</div>

<div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <h4 class="font-semibold text-luxury-900 mb-4">Próximos Eventos</h4>
    <div class="space-y-3">
        <?php 
        $upcoming = $pdo->query("
            SELECT t.id, t.title, t.due_date, t.priority, 'task' as type FROM tasks t 
            WHERE t.due_date >= CURDATE() AND t.status != 'completed'
            UNION ALL
            SELECT d.id, d.title, d.expected_close as due_date, 'medium' as priority, 'deal' as type FROM deals d 
            WHERE d.expected_close >= CURDATE() AND d.status = 'open'
            ORDER BY due_date ASC LIMIT 5
        ")->fetchAll();
        
        foreach ($upcoming as $evt): 
            $isDeal = $evt['type'] === 'deal';
        ?>
        <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 transition-colors">
            <div class="w-10 h-10 rounded-xl <?php echo $isDeal ? 'bg-violet-100 text-violet-600' : 'bg-blue-100 text-blue-600'; ?> flex items-center justify-center">
                <i class="fas <?php echo $isDeal ? 'fa-handshake' : 'fa-tasks'; ?>"></i>
            </div>
            <div class="flex-1">
                <p class="font-medium text-luxury-900"><?php echo clean($evt['title']); ?></p>
                <p class="text-xs text-slate-400"><?php echo $isDeal ? 'Fecho previsto' : 'Data limite'; ?>: <?php echo formatDate($evt['due_date']); ?></p>
            </div>
            <a href="<?php echo $isDeal ? 'deals.php?edit=' . $evt['id'] : 'tasks.php?edit=' . $evt['id']; ?>" class="text-slate-400 hover:text-luxury-gold">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <?php endforeach; ?>
        <?php if (empty($upcoming)): ?>
        <p class="text-slate-400 text-sm text-center py-4">Nenhum evento próximo.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>