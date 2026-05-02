<?php
require_once 'includes/auth.php';
requireAuth();

$page = 'Pipeline Kanban';
$action = $_GET['action'] ?? 'list';

// Handle AJAX stage update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update_stage'])) {
    header('Content-Type: application/json');
    $deal_id = intval($_POST['deal_id']);
    $stage_id = intval($_POST['stage_id']);

    // Get stage info
    $stageStmt = $pdo->prepare("SELECT * FROM deal_stages WHERE id = ?");
    $stageStmt->execute([$stage_id]);
    $stage = $stageStmt->fetch();

    $status = 'open';
    $actual_close = null;
    if (!empty($stage['is_closed'])) {
        $status = !empty($stage['is_won']) ? 'won' : 'lost';
        if (!empty($stage['is_won'])) {
            $actual_close = date('Y-m-d');
        }
    }

    if ($actual_close) {
        $stmt = $pdo->prepare("UPDATE deals SET stage_id = ?, status = ?, actual_close = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$stage_id, $status, $actual_close, $deal_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE deals SET stage_id = ?, status = ?, actual_close = NULL, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$stage_id, $status, $deal_id]);
    }

    logActivity('status_change', "Negócio #{$deal_id} movido para {$stage['name']}", 'deal', $deal_id);
    echo json_encode(['success' => true]);
    exit;
}

// Handle create/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_update_stage'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        setFlash('error', 'Token de segurança inválido.');
        header('Location: deals.php');
        exit;
    }
    $id = intval($_POST['id'] ?? 0);
    $client_id = intval($_POST['client_id'] ?? 0);
    $property_id = intval($_POST['property_id'] ?? 0);
    if ($property_id === 0) $property_id = null;
    $stage_id = intval($_POST['stage_id'] ?? 1);
    $value = floatval($_POST['value'] ?? 0);
    $commission_percent = floatval($_POST['commission_percent'] ?? 5);
    $expected_close = !empty($_POST['expected_close']) ? $_POST['expected_close'] : null;
    $notes = clean($_POST['notes'] ?? '');

    // Check if property already has an active deal (not lost, stage >= Proposta Submetida)
    if ($property_id > 0) {
        $existingDeal = $pdo->prepare("
            SELECT d.id, d.title, s.name as stage_name 
            FROM deals d 
            JOIN deal_stages s ON d.stage_id = s.id 
            WHERE d.property_id = ? 
            AND d.id != ? 
            AND d.status NOT IN ('lost')
            AND s.stage_order >= 5
            LIMIT 1
        ");
        $existingDeal->execute([$property_id, $id]);
        $conflict = $existingDeal->fetch();
        
        if ($conflict) {
            setFlash('error', 'Este imóvel já está em negociação/proposta submetida no negócio: ' . $conflict['title'] . ' (' . $conflict['stage_name'] . '). Não é possível criar outro negócio para o mesmo imóvel.');
            header('Location: deals.php' . ($id ? '?edit=' . $id : '?action=new'));
            exit;
        }
    }

    // Handle external property creation
    $external_title = clean($_POST['external_title'] ?? '');
    if ($property_id === 0 && !empty($external_title)) {
        $external_price = floatval($_POST['external_price'] ?? 0);
        $external_address = clean($_POST['external_address'] ?? '');
        $external_city = clean($_POST['external_city'] ?? '');
        
        $ref = generateReference('EX');
        $stmt = $pdo->prepare("INSERT INTO properties (reference, title, address, city, price, type, status, agent_id) VALUES (?, ?, ?, ?, ?, 'external', 'available', ?)");
        $stmt->execute([$ref, $external_title, $external_address, $external_city, $external_price, $_SESSION['user_id']]);
        $property_id = $pdo->lastInsertId();
        setFlash('success', 'Imóvel externo criado com sucesso.');
    }

    // Get property title for deal title
    $title = '';
    if ($property_id > 0) {
        $stmt = $pdo->prepare("SELECT title, price FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);
        $prop = $stmt->fetch();
        $title = $prop['title'] ?? '';
        if ($value === 0 && !empty($prop['price'])) {
            $value = $prop['price'];
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE deals SET title=?, client_id=?, property_id=?, stage_id=?, value=?, commission_percent=?, expected_close=?, notes=? WHERE id=?");
        $stmt->execute([$title, $client_id, $property_id, $stage_id, $value, $commission_percent, $expected_close, $notes, $id]);
        logActivity('updated', "Negócio atualizado: {$title}", 'deal', $id);
        setFlash('success', 'Negócio atualizado com sucesso.');
    } else {
        $ref = generateReference('DL');
        $stmt = $pdo->prepare("INSERT INTO deals (reference, title, client_id, property_id, stage_id, value, commission_percent, expected_close, notes, agent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ref, $title, $client_id, $property_id, $stage_id, $value, $commission_percent, $expected_close, $notes, $_SESSION['user_id']]);
        logActivity('created', "Novo negócio criado: {$title} ({$ref})", 'deal', $pdo->lastInsertId());
        setFlash('success', 'Negócio criado com sucesso.');
    }
    header('Location: deals.php');
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM deals WHERE id = ?")->execute([$id]);
    setFlash('success', 'Negócio removido.');
    header('Location: deals.php');
    exit;
}

// Fetch data
$stages = getStages();
$clients = $pdo->query("SELECT id, name FROM clients ORDER BY name ASC")->fetchAll();
$properties = $pdo->query("
    SELECT p.id, p.title, p.price 
    FROM properties p 
    WHERE p.status != 'sold'
    AND p.id NOT IN (
        SELECT d.property_id FROM deals d 
        JOIN deal_stages s ON d.stage_id = s.id 
        WHERE d.property_id IS NOT NULL 
        AND d.status NOT IN ('lost') 
        AND s.stage_order >= 5
    )
    ORDER BY p.title ASC
")->fetchAll();

// Fetch deals with related info
$dealsData = $pdo->query("
    SELECT d.*, c.name as client_name, p.title as property_title, p.address as property_address
    FROM deals d
    LEFT JOIN clients c ON d.client_id = c.id
    LEFT JOIN properties p ON d.property_id = p.id
    WHERE d.status != 'lost' OR (d.status = 'lost' AND d.updated_at > DATE_SUB(NOW(), INTERVAL 30 DAY))
    ORDER BY d.updated_at DESC
")->fetchAll();

$dealsByStage = [];
foreach ($stages as $stage) {
    $dealsByStage[$stage['id']] = array_filter($dealsData, fn($d) => $d['stage_id'] == $stage['id']);
}

// Edit mode
$editDeal = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM deals WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editDeal = $stmt->fetch();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($action === 'new' || $editDeal): ?>
<!-- Deal Form -->
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-luxury-900"><?php echo $editDeal ? 'Editar Negócio' : 'Novo Negócio'; ?></h3>
        </div>
        <form method="POST" action="" class="p-6 space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $editDeal['id'] ?? 0; ?>">

            

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cliente</label>
                    <select name="client_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="">Selecionar cliente...</option>
                        <?php foreach ($clients as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($editDeal['client_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>><?php echo clean($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Imóvel Associado</label>
                    <select name="property_id" id="propertySelect" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white" onchange="updatePropertyInfo()">
                        <option value="">Selecionar imóvel...</option>
                        <optgroup label="Imóveis da Agência">
                        <?php foreach ($properties as $p): ?>
                        <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" <?php echo ($editDeal['property_id'] ?? '') == $p['id'] ? 'selected' : ''; ?>><?php echo clean($p['title']); ?> - € <?php echo number_format($p['price'], 0, ',', '.'); ?></option>
                        <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Imóvel Externo / Outra Agência">
                        <option value="external">+ Adicionar imóvel externo</option>
                        </optgroup>
                    </select>
                </div>

                <div id="externalPropertyFields" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Título do Imóvel Externo</label>
                        <input type="text" name="external_title" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all" placeholder="Ex: Apartamento T2 em Lisboa">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Valor (€)</label>
                        <input type="number" name="external_price" step="0.01" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all" placeholder="0,00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Morada</label>
                        <input type="text" name="external_address" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all" placeholder="Morada completa">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Cidade</label>
                        <input type="text" name="external_city" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all" placeholder="Cidade">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Estado / Etapa</label>
                    <select name="stage_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <?php foreach ($stages as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($editDeal['stage_id'] ?? 1) == $s['id'] ? 'selected' : ''; ?>><?php echo clean($s['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Valor do Negócio (€)</label>
                    <input type="number" name="value" step="0.01" value="<?php echo $editDeal['value'] ?? ''; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all" placeholder="0,00">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Comissão (%)</label>
                    <input type="number" name="commission_percent" step="0.01" value="<?php echo $editDeal['commission_percent'] ?? '5.00'; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecho Previsto</label>
                    <input type="date" name="expected_close" value="<?php echo $editDeal['expected_close'] ?? ''; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all resize-none" placeholder="Observações importantes..."><?php echo clean($editDeal['notes'] ?? ''); ?></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="deals.php" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-xl font-medium transition-colors">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 gold-gradient text-luxury-900 font-semibold rounded-xl hover:opacity-90 shadow-md transition-opacity">
                    <?php echo $editDeal ? 'Guardar Alterações' : 'Criar Negócio'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- Kanban Board -->
<div class="flex flex-col h-full">
    <div class="mb-4 shrink-0">
        <p class="text-slate-500 text-sm">Arraste os cartões entre colunas para atualizar o estado do negócio.</p>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-4 flex-1" id="kanban-board">
        <?php foreach ($stages as $stage):
            $stageDeals = $dealsByStage[$stage['id']] ?? [];
            $stageValue = array_sum(array_column($stageDeals, 'value'));
        ?>
        <div class="flex-shrink-0 w-80 flex flex-col" data-stage-id="<?php echo $stage['id']; ?>">
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" style="background-color: <?php echo $stage['color']; ?>"></div>
                    <h4 class="font-semibold text-sm text-luxury-900"><?php echo clean($stage['name']); ?></h4>
                    <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium"><?php echo count($stageDeals); ?></span>
                </div>
                <span class="text-xs font-semibold text-slate-500"><?php echo formatCurrency($stageValue); ?></span>
            </div>

            <div class="kanban-column bg-slate-100/70 rounded-2xl p-3 flex-1 space-y-3 transition-all"
                 ondragover="allowDrop(event)"
                 ondrop="drop(event, <?php echo $stage['id']; ?>)"
                 ondragenter="dragEnter(event)"
                 ondragleave="dragLeave(event)">

                <?php foreach ($stageDeals as $deal): ?>
                <div class="kanban-card bg-white rounded-xl p-4 shadow-sm border border-slate-100 group"
                     draggable="true"
                     ondragstart="drag(event, <?php echo $deal['id']; ?>)"
                     id="deal-<?php echo $deal['id']; ?>">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-xs font-bold text-luxury-gold"><?php echo clean($deal['reference']); ?></span>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                            <a href="deals.php?edit=<?php echo $deal['id']; ?>" class="text-slate-400 hover:text-luxury-gold"><i class="fas fa-pen text-xs"></i></a>
                            <a href="deals.php?delete=<?php echo $deal['id']; ?>" onclick="return confirm('Tem a certeza?')" class="text-slate-400 hover:text-red-500"><i class="fas fa-trash text-xs"></i></a>
                        </div>
                    </div>
                    <h5 class="font-semibold text-sm text-luxury-900 mb-1 truncate"><?php echo clean($deal['property_title'] ?? $deal['title']); ?></h5>
                    <p class="text-xs text-slate-500 mb-1"><i class="fas fa-user mr-1 text-slate-400"></i><?php echo clean($deal['client_name'] ?? 'Sem cliente'); ?></p>
                    <?php if ($deal['property_address']): ?>
                    <p class="text-xs text-slate-400 mb-2 truncate"><i class="fas fa-map-marker-alt mr-1"></i><?php echo clean($deal['property_address']); ?></p>
                    <?php endif; ?>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                        <span class="text-sm font-bold text-luxury-900"><?php echo formatCurrency($deal['value']); ?></span>
                        <?php if ($deal['expected_close']): ?>
                        <span class="text-xs text-slate-400"><?php echo formatDate($deal['expected_close']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
let draggedDealId = null;

function drag(ev, dealId) {
    draggedDealId = dealId;
    ev.dataTransfer.setData("text/plain", dealId);
    ev.target.style.opacity = '0.5';
}

function allowDrop(ev) {
    ev.preventDefault();
}

function dragEnter(ev) {
    ev.preventDefault();
    const col = ev.target.closest('.kanban-column');
    if (col) col.classList.add('kanban-drag-over');
}

function dragLeave(ev) {
    const col = ev.target.closest('.kanban-column');
    if (col) col.classList.remove('kanban-drag-over');
}

function drop(ev, stageId) {
    ev.preventDefault();
    const col = ev.target.closest('.kanban-column');
    if (col) col.classList.remove('kanban-drag-over');

    const dealId = draggedDealId;
    if (!dealId) return;

    const card = document.getElementById('deal-' + dealId);
    if (card) {
        card.style.opacity = '1';
        // Move visually immediately for responsiveness
        col.appendChild(card);
    }

    // Send AJAX update
    const formData = new FormData();
    formData.append('ajax_update_stage', '1');
    formData.append('deal_id', dealId);
    formData.append('stage_id', stageId);

    fetch('deals.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert('Erro ao atualizar. Recarregue a página.');
            location.reload();
        }
    })
    .catch(() => {
        location.reload();
    });
}

document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragend', () => {
        card.style.opacity = '1';
    });
});

function updatePropertyInfo() {
    const select = document.getElementById('propertySelect');
    const valueInput = document.querySelector('input[name="value"]');
    const externalFields = document.getElementById('externalPropertyFields');
    
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    
    if (select.value === 'external') {
        externalFields.classList.remove('hidden');
        if (valueInput) valueInput.value = '';
    } else if (select.value && select.value !== 'external') {
        externalFields.classList.add('hidden');
        if (price && price > 0 && valueInput) {
            valueInput.value = price;
        }
    } else {
        externalFields.classList.add('hidden');
    }
}

// Auto-fill on page load if property is already selected
document.addEventListener('DOMContentLoaded', function() {
    updatePropertyInfo();
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
