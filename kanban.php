<?php
// Kanban Board - Fresh Version
require_once 'includes/auth.php';
requireAuth();

$page = 'Pipeline';

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

global $pdo;

// Get stages
$stages = $pdo->query("SELECT * FROM deal_stages ORDER BY stage_order")->fetchAll();

// Get deals
$deals = $pdo->query("
    SELECT d.*, p.title as property_title, p.address as property_address, 
           c.name as client_name, ds.name as stage_name
    FROM deals d
    LEFT JOIN properties p ON d.property_id = p.id
    LEFT JOIN clients c ON d.client_id = c.id
    LEFT JOIN deal_stages ds ON d.stage_id = ds.id
    ORDER BY d.updated_at DESC
")->fetchAll();

// Group deals by stage
$dealsByStage = [];
foreach ($deals as $deal) {
    $dealsByStage[$deal['stage_id']][] = $deal;
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="flex flex-col h-full">
    <div class="mb-4 shrink-0">
        <h2 class="text-xl font-bold text-luxury-900">Pipeline Kanban</h2>
        <p class="text-slate-500 text-sm">Arraste os cartões entre colunas.</p>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-4 flex-1" id="kanban-board">
        <?php foreach ($stages as $stage):
            $stageDeals = $dealsByStage[$stage['id']] ?? [];
        ?>
        <div class="flex-shrink-0 w-80 flex flex-col" data-stage-id="<?php echo $stage['id']; ?>">
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" style="background-color: <?php echo $stage['color']; ?>"></div>
                    <h4 class="font-semibold text-sm text-luxury-900"><?php echo htmlspecialchars($stage['name']); ?></h4>
                    <span class="count-badge text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium"><?php echo count($stageDeals); ?></span>
                </div>
            </div>

            <div class="kanban-column bg-slate-100/70 rounded-2xl p-3 flex-1 space-y-3 min-h-[200px]"
                 ondragover="allowDrop(event)"
                 ondrop="drop(event, <?php echo $stage['id']; ?>)"
                 ondragenter="dragEnter(event)"
                 ondragleave="dragLeave(event)"
                 data-stage="<?php echo $stage['id']; ?>">

                <?php foreach ($stageDeals as $deal): ?>
                <div class="kanban-card bg-white rounded-xl p-4 shadow-sm border border-slate-100 cursor-grab"
                     draggable="true"
                     ondragstart="drag(event, <?php echo $deal['id']; ?>)"
                     id="deal-<?php echo $deal['id']; ?>">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-xs font-bold text-luxury-gold"><?php echo htmlspecialchars($deal['reference']); ?></span>
                    </div>
                    <h5 class="font-semibold text-sm text-luxury-900 mb-1"><?php echo htmlspecialchars($deal['property_title'] ?? $deal['title']); ?></h5>
                    <p class="text-xs text-slate-500 mb-1"><?php echo htmlspecialchars($deal['client_name'] ?? 'Sem cliente'); ?></p>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                        <span class="text-sm font-bold text-luxury-900">€ <?php echo number_format($deal['value'], 0, ',', '.'); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// FRESH KANBAN v1
window.allowDrop = function(ev) { ev.preventDefault(); };
window.dragEnter = function(ev) {
    var col = ev.target.closest('.kanban-column');
    if (col) col.classList.add('kanban-drag-over');
};
window.dragLeave = function(ev) {
    var col = ev.target.closest('.kanban-column');
    if (col) col.classList.remove('kanban-drag-over');
};
window.drag = function(ev, dealId) {
    window.draggedDealId = dealId;
    ev.dataTransfer.setData('text/plain', dealId);
    ev.target.style.opacity = '0.5';
};
window.drop = function(ev, stageId) {
    ev.preventDefault();
    var col = ev.target.closest('.kanban-column');
    if (!col) return;
    col.classList.remove('kanban-drag-over');

    var dealId = window.draggedDealId;
    if (!dealId) return;

    var card = document.getElementById('deal-' + dealId);
    if (card) {
        card.style.opacity = '1';
        col.appendChild(card);
    }

    // Update counts immediately
    updateCounts();

    // Send to server
    var formData = new FormData();
    formData.append('deal_id', dealId);
    formData.append('stage_id', stageId);

    fetch('api/kanban.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        console.log('Updated:', data.success);
    })
    .catch(function(err) {
        console.error('Error:', err);
    });
};

function updateCounts() {
    document.querySelectorAll('.kanban-column').forEach(function(col) {
        var countEl = col.parentElement.querySelector('.count-badge');
        if (countEl) {
            var cards = col.querySelectorAll('.kanban-card');
            countEl.textContent = cards.length;
        }
    });
}

// Add dragend handlers
document.querySelectorAll('.kanban-card').forEach(function(card) {
    card.addEventListener('dragend', function() {
        card.style.opacity = '1';
    });
});

console.log('Kanban loaded');
</script>

<?php include 'includes/footer.php'; ?>