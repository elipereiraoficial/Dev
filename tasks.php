<?php
require_once 'includes/auth.php';
requireAuth();

$page = 'Tarefas';
$action = $_GET['action'] ?? 'list';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        setFlash('error', 'Token de segurança inválido.');
        header('Location: tasks.php');
        exit;
    }

    $id = intval($_POST['id'] ?? 0);
    $title = clean($_POST['title'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $related_to = $_POST['related_to'] ?: null;
    $related_id = intval($_POST['related_id'] ?? 0) ?: null;
    $due_date = $_POST['due_date'] ?: null;
    $priority = $_POST['priority'] ?? 'medium';
    $status = $_POST['status'] ?? 'pending';
    $assigned_to = intval($_POST['assigned_to'] ?? 0) ?: null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE tasks SET title=?, description=?, related_to=?, related_id=?, due_date=?, priority=?, status=?, assigned_to=? WHERE id=?");
        $stmt->execute([$title, $description, $related_to, $related_id, $due_date, $priority, $status, $assigned_to, $id]);
        logActivity('updated', "Tarefa atualizada: {$title}", 'task', $id);
        setFlash('success', 'Tarefa atualizada com sucesso.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, related_to, related_id, due_date, priority, status, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $related_to, $related_id, $due_date, $priority, $status, $assigned_to, $_SESSION['user_id']]);
        logActivity('created', "Nova tarefa: {$title}", 'task', $pdo->lastInsertId());
        setFlash('success', 'Tarefa criada com sucesso.');
    }
    header('Location: tasks.php');
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM tasks WHERE id = ?")->execute([$id]);
    setFlash('success', 'Tarefa removida.');
    header('Location: tasks.php');
    exit;
}

// Toggle status (AJAX)
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $task = $pdo->prepare("SELECT status FROM tasks WHERE id = ?")->execute([$id])->fetch();
    $newStatus = $task['status'] === 'completed' ? 'pending' : 'completed';
    $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
    header('Location: tasks.php');
    exit;
}

// Fetch
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT t.*, u.name as assigned_name, c.name as related_client, p.title as related_property, d.title as related_deal
        FROM tasks t 
        LEFT JOIN users u ON t.assigned_to = u.id 
        LEFT JOIN clients c ON t.related_to = 'client' AND t.related_id = c.id
        LEFT JOIN properties p ON t.related_to = 'property' AND t.related_id = p.id
        LEFT JOIN deals d ON t.related_to = 'deal' AND t.related_id = d.id
        WHERE 1=1";

if ($filter === 'pending') $sql .= " AND t.status = 'pending'";
if ($filter === 'completed') $sql .= " AND t.status = 'completed'";
if ($filter === 'overdue') $sql .= " AND t.status != 'completed' AND t.due_date < CURRENT_DATE";

$sql .= " ORDER BY t.priority DESC, t.due_date ASC, t.created_at DESC";

$tasks = $pdo->query($sql)->fetchAll();

$users = $pdo->query("SELECT id, name FROM users WHERE active = true ORDER BY name ASC")->fetchAll();
$clients = $pdo->query("SELECT id, name FROM clients ORDER BY name ASC")->fetchAll();
$properties = $pdo->query("SELECT id, title FROM properties ORDER BY title ASC")->fetchAll();
$deals = $pdo->query("SELECT id, title FROM deals WHERE status = 'open' ORDER BY title ASC")->fetchAll();

$editTask = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editTask = $stmt->fetch();
}

$priorityLabels = ['low' => 'Baixa', 'medium' => 'Média', 'high' => 'Alta', 'urgent' => 'Urgente'];
$statusLabels = ['pending' => 'Pendente', 'in_progress' => 'Em Progresso', 'completed' => 'Concluída', 'cancelled' => 'Cancelada'];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($action === 'new' || $editTask): ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-luxury-900"><?php echo $editTask ? 'Editar Tarefa' : 'Nova Tarefa'; ?></h3>
        </div>
        <form method="POST" action="" class="p-6 space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $editTask['id'] ?? 0; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Título *</label>
                    <input type="text" name="title" required value="<?php echo clean($editTask['title'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Descrição</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all resize-none"><?php echo clean($editTask['description'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Data Limite</label>
                    <input type="date" name="due_date" value="<?php echo $editTask['due_date'] ?? ''; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Prioridade</label>
                    <select name="priority" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <?php foreach ($priorityLabels as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($editTask['priority'] ?? 'medium') === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($editTask['status'] ?? 'pending') === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Responsável</label>
                    <select name="assigned_to" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="">--</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($editTask['assigned_to'] ?? '') == $u['id'] ? 'selected' : ''; ?>><?php echo clean($u['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Relacionado com</label>
                    <select name="related_to" id="related_to" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white" onchange="updateRelatedOptions()">
                        <option value="">--</option>
                        <option value="deal" <?php echo ($editTask['related_to'] ?? '') === 'deal' ? 'selected' : ''; ?>>Negócio</option>
                        <option value="client" <?php echo ($editTask['related_to'] ?? '') === 'client' ? 'selected' : ''; ?>>Cliente</option>
                        <option value="property" <?php echo ($editTask['related_to'] ?? '') === 'property' ? 'selected' : ''; ?>>Imóvel</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Selecionar</label>
                    <select name="related_id" id="related_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="">--</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="tasks.php" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-xl font-medium transition-colors">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 gold-gradient text-luxury-900 font-semibold rounded-xl hover:opacity-90 shadow-md transition-opacity">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
const relatedData = {
    deal: <?php echo json_encode(array_map(fn($d) => ['id' => $d['id'], 'name' => $d['title']], $deals)); ?>,
    client: <?php echo json_encode(array_map(fn($c) => ['id' => $c['id'], 'name' => $c['name']], $clients)); ?>,
    property: <?php echo json_encode(array_map(fn($p) => ['id' => $p['id'], 'name' => $p['title']], $properties)); ?>
};

function updateRelatedOptions() {
    const type = document.getElementById('related_to').value;
    const select = document.getElementById('related_id');
    select.innerHTML = '<option value="">--</option>';
    
    if (type && relatedData[type]) {
        relatedData[type].forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    }
}

updateRelatedOptions();
<?php if (!empty($editTask['related_id'])): ?>
document.getElementById('related_id').value = '<?php echo $editTask['related_id']; ?>';
<?php endif; ?>
</script>

<?php else: ?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <h3 class="font-semibold text-luxury-900">Lista de Tarefas</h3>
            <a href="tasks.php?filter=all" class="px-2 py-1 text-xs rounded-full <?php echo $filter === 'all' ? 'bg-luxury-gold text-luxury-900' : 'bg-slate-100 text-slate-600'; ?>">Todas</a>
            <a href="tasks.php?filter=pending" class="px-2 py-1 text-xs rounded-full <?php echo $filter === 'pending' ? 'bg-luxury-gold text-luxury-900' : 'bg-slate-100 text-slate-600'; ?>">Pendentes</a>
            <a href="tasks.php?filter=completed" class="px-2 py-1 text-xs rounded-full <?php echo $filter === 'completed' ? 'bg-luxury-gold text-luxury-900' : 'bg-slate-100 text-slate-600'; ?>">Concluídas</a>
            <a href="tasks.php?filter=overdue" class="px-2 py-1 text-xs rounded-full <?php echo $filter === 'overdue' ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600'; ?>">Atrasadas</a>
        </div>
        <a href="tasks.php?action=new" class="flex items-center gap-2 px-4 py-2 gold-gradient text-luxury-900 text-sm font-semibold rounded-lg hover:opacity-90 shadow-md">
            <i class="fas fa-plus"></i> Nova Tarefa
        </a>
    </div>
    <div class="divide-y divide-slate-100">
        <?php foreach ($tasks as $task): ?>
        <div class="p-4 hover:bg-slate-50/50 transition-colors">
            <div class="flex items-start gap-4">
                <a href="tasks.php?toggle=<?php echo $task['id']; ?>" class="mt-1 w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center <?php echo $task['status'] === 'completed' ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 hover:border-luxury-gold'; ?>">
                    <?php if ($task['status'] === 'completed'): ?><i class="fas fa-check text-xs"></i><?php endif; ?>
                </a>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="font-medium text-luxury-900 <?php echo $task['status'] === 'completed' ? 'line-through text-slate-400' : ''; ?>"><?php echo clean($task['title']); ?></h4>
                        <span class="text-xs px-1.5 py-0.5 rounded <?php echo $task['priority'] === 'urgent' ? 'bg-red-100 text-red-600' : ($task['priority'] === 'high' ? 'bg-orange-100 text-orange-600' : ($task['priority'] === 'medium' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500')); ?>"><?php echo $priorityLabels[$task['priority']]; ?></span>
                        <?php if ($task['status'] === 'completed'): ?>
                        <span class="text-xs px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-600">Concluída</span>
                        <?php elseif ($task['due_date'] && $task['due_date'] < date('Y-m-d')): ?>
                        <span class="text-xs px-1.5 py-0.5 rounded bg-red-100 text-red-600">Atrasada</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($task['description']): ?>
                    <p class="text-sm text-slate-500 mb-2"><?php echo clean($task['description']); ?></p>
                    <?php endif; ?>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                        <?php if ($task['due_date']): ?>
                        <span><i class="far fa-calendar mr-1"></i><?php echo formatDate($task['due_date']); ?></span>
                        <?php endif; ?>
                        <?php if ($task['assigned_name']): ?>
                        <span><i class="fas fa-user mr-1"></i><?php echo clean($task['assigned_name']); ?></span>
                        <?php endif; ?>
                        <?php if ($task['related_deal']): ?>
                        <span><i class="fas fa-handshake mr-1"></i><?php echo clean($task['related_deal']); ?></span>
                        <?php elseif ($task['related_client']): ?>
                        <span><i class="fas fa-user mr-1"></i><?php echo clean($task['related_client']); ?></span>
                        <?php elseif ($task['related_property']): ?>
                        <span><i class="fas fa-building mr-1"></i><?php echo clean($task['related_property']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="tasks.php?edit=<?php echo $task['id']; ?>" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-luxury-gold/20 hover:text-luxury-gold flex items-center justify-center transition-colors"><i class="fas fa-pen text-xs"></i></a>
                    <a href="tasks.php?delete=<?php echo $task['id']; ?>" onclick="return confirm('Eliminar esta tarefa?')" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors"><i class="fas fa-trash text-xs"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($tasks)): ?>
        <div class="p-12 text-center text-slate-400">Nenhuma tarefa encontrada.</div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>