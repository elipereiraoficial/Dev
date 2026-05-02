<?php
require_once 'includes/auth.php';
requireAuth();

$page = 'Clientes';
$action = $_GET['action'] ?? 'list';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        setFlash('error', 'Token de segurança inválido.');
        header('Location: clients.php');
        exit;
    }
    $id = intval($_POST['id'] ?? 0);
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $type = $_POST['type'] ?? 'buyer';
    $source = clean($_POST['source'] ?? '');
    $budget_min = floatval($_POST['budget_min'] ?? 0);
    $budget_max = floatval($_POST['budget_max'] ?? 0);
    $preferences = clean($_POST['preferences'] ?? '');
    $notes = clean($_POST['notes'] ?? '');
    $status = $_POST['status'] ?? 'prospect';
    $assigned_to = intval($_POST['assigned_to'] ?? 0) ?: null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE clients SET name=?, email=?, phone=?, type=?, source=?, budget_min=?, budget_max=?, preferences=?, notes=?, status=?, assigned_to=? WHERE id=?");
        $stmt->execute([$name, $email, $phone, $type, $source, $budget_min, $budget_max, $preferences, $notes, $status, $assigned_to, $id]);
        logActivity('updated', "Cliente atualizado: {$name}", 'client', $id);
        setFlash('success', 'Cliente atualizado com sucesso.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, type, source, budget_min, budget_max, preferences, notes, status, assigned_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $type, $source, $budget_min, $budget_max, $preferences, $notes, $status, $assigned_to]);
        logActivity('created', "Novo cliente: {$name}", 'client', $pdo->lastInsertId());
        setFlash('success', 'Cliente criado com sucesso.');
    }
    header('Location: clients.php');
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM clients WHERE id = ?")->execute([$id]);
    setFlash('success', 'Cliente removido.');
    header('Location: clients.php');
    exit;
}

// Fetch
$search = clean($_GET['search'] ?? '');
$sql = "SELECT c.*, u.name as agent_name FROM clients c LEFT JOIN users u ON c.assigned_to = u.id WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
}
$sql .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();

$agents = $pdo->query("SELECT id, name FROM users WHERE active = true ORDER BY name ASC")->fetchAll();

$editClient = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editClient = $stmt->fetch();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($action === 'new' || $editClient): ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-luxury-900"><?php echo $editClient ? 'Editar Cliente' : 'Novo Cliente'; ?></h3>
        </div>
        <form method="POST" action="" class="p-6 space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $editClient['id'] ?? 0; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome Completo *</label>
                    <input type="text" name="name" required value="<?php echo clean($editClient['name'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo clean($editClient['email'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Telefone</label>
                    <input type="tel" name="phone" value="<?php echo clean($editClient['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                    <select name="type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="buyer" <?php echo ($editClient['type'] ?? '') === 'buyer' ? 'selected' : ''; ?>>Comprador</option>
                        <option value="seller" <?php echo ($editClient['type'] ?? '') === 'seller' ? 'selected' : ''; ?>>Vendedor</option>
                        <option value="investor" <?php echo ($editClient['type'] ?? '') === 'investor' ? 'selected' : ''; ?>>Investidor</option>
                        <option value="both" <?php echo ($editClient['type'] ?? '') === 'both' ? 'selected' : ''; ?>>Ambos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Origem</label>
                    <select name="source" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="">--</option>
                        <option value="website" <?php echo ($editClient['source'] ?? '') === 'website' ? 'selected' : ''; ?>>Website</option>
                        <option value="referral" <?php echo ($editClient['source'] ?? '') === 'referral' ? 'selected' : ''; ?>>Referência</option>
                        <option value="social" <?php echo ($editClient['source'] ?? '') === 'social' ? 'selected' : ''; ?>>Redes Sociais</option>
                        <option value="portal" <?php echo ($editClient['source'] ?? '') === 'portal' ? 'selected' : ''; ?>>Portal Imobiliário</option>
                        <option value="walkin" <?php echo ($editClient['source'] ?? '') === 'walkin' ? 'selected' : ''; ?>>Presencial</option>
                        <option value="other" <?php echo ($editClient['source'] ?? '') === 'other' ? 'selected' : ''; ?>>Outro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Orçamento Mín (€)</label>
                    <input type="number" name="budget_min" step="0.01" value="<?php echo $editClient['budget_min'] ?? ''; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Orçamento Máx (€)</label>
                    <input type="number" name="budget_max" step="0.01" value="<?php echo $editClient['budget_max'] ?? ''; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="prospect" <?php echo ($editClient['status'] ?? '') === 'prospect' ? 'selected' : ''; ?>>Prospecto</option>
                        <option value="active" <?php echo ($editClient['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="inactive" <?php echo ($editClient['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Agente Responsável</label>
                    <select name="assigned_to" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="">--</option>
                        <?php foreach ($agents as $a): ?>
                        <option value="<?php echo $a['id']; ?>" <?php echo ($editClient['assigned_to'] ?? '') == $a['id'] ? 'selected' : ''; ?>><?php echo clean($a['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Preferências</label>
                    <textarea name="preferences" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all resize-none" placeholder="Tipo de imóvel, localização, etc."><?php echo clean($editClient['preferences'] ?? ''); ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all resize-none"><?php echo clean($editClient['notes'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="clients.php" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-xl font-medium transition-colors">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 gold-gradient text-luxury-900 font-semibold rounded-xl hover:opacity-90 shadow-md transition-opacity">Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h3 class="font-semibold text-luxury-900">Lista de Clientes</h3>
        <div class="flex items-center gap-3">
            <form method="GET" action="" class="flex items-center gap-2">
                <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Pesquisar..." class="px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                <button type="submit" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors"><i class="fas fa-search"></i></button>
            </form>
            <a href="clients.php?action=new" class="flex items-center gap-2 px-4 py-2 gold-gradient text-luxury-900 text-sm font-semibold rounded-lg hover:opacity-90 shadow-md">
                <i class="fas fa-plus"></i> Novo
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-left px-6 py-3 font-medium">Nome</th>
                    <th class="text-left px-6 py-3 font-medium">Contacto</th>
                    <th class="text-left px-6 py-3 font-medium">Tipo</th>
                    <th class="text-left px-6 py-3 font-medium">Orçamento</th>
                    <th class="text-left px-6 py-3 font-medium">Agente</th>
                    <th class="text-center px-6 py-3 font-medium">Estado</th>
                    <th class="text-center px-6 py-3 font-medium">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($clients as $c): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-luxury-gold/10 flex items-center justify-center text-luxury-gold font-bold text-xs"><?php echo strtoupper(substr($c['name'], 0, 2)); ?></div>
                            <div>
                                <p class="font-medium text-luxury-900"><?php echo clean($c['name']); ?></p>
                                <?php if ($c['email']): ?><p class="text-xs text-slate-400"><?php echo clean($c['email']); ?></p><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-slate-600"><?php echo clean($c['phone'] ?? '-'); ?></td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 capitalize">
                            <?php echo $c['type'] === 'buyer' ? 'Comprador' : ($c['type'] === 'seller' ? 'Vendedor' : ($c['type'] === 'investor' ? 'Investidor' : 'Ambos')); ?>
                        </span>
                    </td>
                    <td class="px-6 py-3 text-slate-600">
                        <?php if ($c['budget_min'] || $c['budget_max']): ?>
                            <?php echo $c['budget_min'] ? formatCurrency($c['budget_min']) : ''; ?>
                            <?php if ($c['budget_min'] && $c['budget_max']) echo ' - '; ?>
                            <?php echo $c['budget_max'] ? formatCurrency($c['budget_max']) : ''; ?>
                        <?php else: echo '-'; endif; ?>
                    </td>
                    <td class="px-6 py-3 text-slate-600"><?php echo clean($c['agent_name'] ?? '-'); ?></td>
                    <td class="px-6 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?php echo $c['status'] === 'active' ? 'bg-emerald-100 text-emerald-600' : ($c['status'] === 'prospect' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500'); ?>">
                            <?php echo $c['status'] === 'active' ? 'Ativo' : ($c['status'] === 'prospect' ? 'Prospecto' : 'Inativo'); ?>
                        </span>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="clients.php?edit=<?php echo $c['id']; ?>" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-luxury-gold/20 hover:text-luxury-gold flex items-center justify-center transition-colors"><i class="fas fa-pen text-xs"></i></a>
                            <a href="clients.php?delete=<?php echo $c['id']; ?>" onclick="return confirm('Tem a certeza que deseja eliminar este cliente?')" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors"><i class="fas fa-trash text-xs"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Nenhum cliente encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
