<?php
require_once 'includes/auth.php';
requireAuth();

$page = 'Imóveis';
$action = $_GET['action'] ?? 'list';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        setFlash('error', 'Token de segurança inválido.');
        header('Location: properties.php');
        exit;
    }
    $id = intval($_POST['id'] ?? 0);
    $reference = clean($_POST['reference'] ?? generateReference('IM'));
    $title = clean($_POST['title'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $address = clean($_POST['address'] ?? '');
    $city = clean($_POST['city'] ?? '');
    $region = clean($_POST['region'] ?? '');
    $postal_code = clean($_POST['postal_code'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $type = $_POST['type'] ?? 'apartment';
    $status = $_POST['status'] ?? 'available';
    $bedrooms = intval($_POST['bedrooms'] ?? 0);
    $bathrooms = intval($_POST['bathrooms'] ?? 0);
    $area_m2 = intval($_POST['area_m2'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $owner_id = intval($_POST['owner_id'] ?? 0) ?: null;
    $agent_id = intval($_POST['agent_id'] ?? 0) ?: null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE properties SET reference=?, title=?, description=?, address=?, city=?, region=?, postal_code=?, price=?, type=?, status=?, bedrooms=?, bathrooms=?, area_m2=?, featured=?, owner_id=?, agent_id=? WHERE id=?");
        $stmt->execute([$reference, $title, $description, $address, $city, $region, $postal_code, $price, $type, $status, $bedrooms, $bathrooms, $area_m2, $featured, $owner_id, $agent_id, $id]);
        logActivity('updated', "Imóvel atualizado: {$title}", 'property', $id);
        setFlash('success', 'Imóvel atualizado com sucesso.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, owner_id, agent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$reference, $title, $description, $address, $city, $region, $postal_code, $price, $type, $status, $bedrooms, $bathrooms, $area_m2, $featured, $owner_id, $agent_id]);
        logActivity('created', "Novo imóvel: {$title} ({$reference})", 'property', $pdo->lastInsertId());
        setFlash('success', 'Imóvel criado com sucesso.');
    }
    header('Location: properties.php');
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM properties WHERE id = ?")->execute([$id]);
    setFlash('success', 'Imóvel removido.');
    header('Location: properties.php');
    exit;
}

// Fetch
$search = clean($_GET['search'] ?? '');
$sql = "SELECT p.*, c.name as owner_name, u.name as agent_name FROM properties p LEFT JOIN clients c ON p.owner_id = c.id LEFT JOIN users u ON p.agent_id = u.id WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (p.title LIKE ? OR p.reference LIKE ? OR p.city LIKE ?)";
    $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
}
$sql .= " ORDER BY p.featured DESC, p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

$clients = $pdo->query("SELECT id, name FROM clients WHERE type IN ('seller','both') ORDER BY name ASC")->fetchAll();
$agents = $pdo->query("SELECT id, name FROM users WHERE active = true ORDER BY name ASC")->fetchAll();

$editProp = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editProp = $stmt->fetch();
}

$typeLabels = [
    'apartment' => 'Apartamento',
    'villa' => 'Moradia',
    'penthouse' => 'Penthouse',
    'land' => 'Terreno',
    'commercial' => 'Comercial',
    'estate' => 'Quinta/Herdade'
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($action === 'new' || $editProp): ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-luxury-900"><?php echo $editProp ? 'Editar Imóvel' : 'Novo Imóvel'; ?></h3>
        </div>
        <form method="POST" action="" class="p-6 space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $editProp['id'] ?? 0; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Referência</label>
                    <input type="text" name="reference" value="<?php echo clean($editProp['reference'] ?? generateReference('IM')); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-slate-50" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Título *</label>
                    <input type="text" name="title" required value="<?php echo clean($editProp['title'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Descrição</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all resize-none"><?php echo clean($editProp['description'] ?? ''); ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Morada</label>
                    <input type="text" name="address" value="<?php echo clean($editProp['address'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cidade</label>
                    <input type="text" name="city" value="<?php echo clean($editProp['city'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Região</label>
                    <select name="region" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="">--</option>
                        <option value="Lisboa" <?php echo ($editProp['region'] ?? '') === 'Lisboa' ? 'selected' : ''; ?>>Lisboa</option>
                        <option value="Porto" <?php echo ($editProp['region'] ?? '') === 'Porto' ? 'selected' : ''; ?>>Porto</option>
                        <option value="Algarve" <?php echo ($editProp['region'] ?? '') === 'Algarve' ? 'selected' : ''; ?>>Algarve</option>
                        <option value="Alentejo" <?php echo ($editProp['region'] ?? '') === 'Alentejo' ? 'selected' : ''; ?>>Alentejo</option>
                        <option value="Centro" <?php echo ($editProp['region'] ?? '') === 'Centro' ? 'selected' : ''; ?>>Centro</option>
                        <option value="Norte" <?php echo ($editProp['region'] ?? '') === 'Norte' ? 'selected' : ''; ?>>Norte</option>
                        <option value="Madeira" <?php echo ($editProp['region'] ?? '') === 'Madeira' ? 'selected' : ''; ?>>Madeira</option>
                        <option value="Açores" <?php echo ($editProp['region'] ?? '') === 'Açores' ? 'selected' : ''; ?>>Açores</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Código Postal</label>
                    <input type="text" name="postal_code" value="<?php echo clean($editProp['postal_code'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Preço (€) *</label>
                    <input type="number" name="price" step="0.01" required value="<?php echo $editProp['price'] ?? ''; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                    <select name="type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <?php foreach ($typeLabels as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($editProp['type'] ?? '') === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="available" <?php echo ($editProp['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Disponível</option>
                        <option value="reserved" <?php echo ($editProp['status'] ?? '') === 'reserved' ? 'selected' : ''; ?>>Reservado</option>
                        <option value="sold" <?php echo ($editProp['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Vendido</option>
                        <option value="rented" <?php echo ($editProp['status'] ?? '') === 'rented' ? 'selected' : ''; ?>>Arrendado</option>
                        <option value="unavailable" <?php echo ($editProp['status'] ?? '') === 'unavailable' ? 'selected' : ''; ?>>Indisponível</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Quartos</label>
                    <input type="number" name="bedrooms" value="<?php echo $editProp['bedrooms'] ?? '0'; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Casas de Banho</label>
                    <input type="number" name="bathrooms" value="<?php echo $editProp['bathrooms'] ?? '0'; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Área (m²)</label>
                    <input type="number" name="area_m2" value="<?php echo $editProp['area_m2'] ?? ''; ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="featured" id="featured" <?php echo ($editProp['featured'] ?? 0) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-slate-300 text-luxury-gold focus:ring-luxury-gold">
                    <label for="featured" class="text-sm font-medium text-slate-700">Destacar no site</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Proprietário</label>
                    <select name="owner_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="">--</option>
                        <?php foreach ($clients as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($editProp['owner_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>><?php echo clean($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Agente</label>
                    <select name="agent_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all bg-white">
                        <option value="">--</option>
                        <?php foreach ($agents as $a): ?>
                        <option value="<?php echo $a['id']; ?>" <?php echo ($editProp['agent_id'] ?? '') == $a['id'] ? 'selected' : ''; ?>><?php echo clean($a['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="properties.php" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-xl font-medium transition-colors">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 gold-gradient text-luxury-900 font-semibold rounded-xl hover:opacity-90 shadow-md transition-opacity">Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h3 class="font-semibold text-luxury-900">Lista de Imóveis</h3>
        <div class="flex items-center gap-3">
            <form method="GET" action="" class="flex items-center gap-2">
                <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Pesquisar..." class="px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                <button type="submit" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors"><i class="fas fa-search"></i></button>
            </form>
            <a href="properties.php?action=new" class="flex items-center gap-2 px-4 py-2 gold-gradient text-luxury-900 text-sm font-semibold rounded-lg hover:opacity-90 shadow-md">
                <i class="fas fa-plus"></i> Novo
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-left px-6 py-3 font-medium">Referência</th>
                    <th class="text-left px-6 py-3 font-medium">Título</th>
                    <th class="text-left px-6 py-3 font-medium">Localização</th>
                    <th class="text-left px-6 py-3 font-medium">Tipo</th>
                    <th class="text-right px-6 py-3 font-medium">Preço</th>
                    <th class="text-center px-6 py-3 font-medium">Estado</th>
                    <th class="text-center px-6 py-3 font-medium">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($properties as $p): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-3 font-medium text-luxury-gold"><?php echo clean($p['reference']); ?></td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2">
                            <?php if ($p['featured']): ?><i class="fas fa-star text-luxury-gold text-xs" title="Destacado"></i><?php endif; ?>
                            <span class="font-medium text-luxury-900"><?php echo clean($p['title']); ?></span>
                        </div>
                        <p class="text-xs text-slate-400"><?php echo $p['bedrooms']; ?> qt • <?php echo $p['bathrooms']; ?> wc • <?php echo $p['area_m2']; ?> m²</p>
                    </td>
                    <td class="px-6 py-3 text-slate-600"><?php echo clean($p['city'] ?? '-'); ?><?php if ($p['region']): ?>, <?php echo clean($p['region']); ?><?php endif; ?></td>
                    <td class="px-6 py-3 text-slate-600"><?php echo $typeLabels[$p['type']] ?? $p['type']; ?></td>
                    <td class="px-6 py-3 text-right font-bold text-luxury-900"><?php echo formatCurrency($p['price']); ?></td>
                    <td class="px-6 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?php echo $p['status'] === 'available' ? 'bg-emerald-100 text-emerald-600' : ($p['status'] === 'sold' ? 'bg-blue-100 text-blue-600' : ($p['status'] === 'reserved' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500')); ?>">
                            <?php echo $p['status'] === 'available' ? 'Disponível' : ($p['status'] === 'sold' ? 'Vendido' : ($p['status'] === 'reserved' ? 'Reservado' : ($p['status'] === 'rented' ? 'Arrendado' : 'Indisponível'))); ?>
                        </span>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="properties.php?edit=<?php echo $p['id']; ?>" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-luxury-gold/20 hover:text-luxury-gold flex items-center justify-center transition-colors"><i class="fas fa-pen text-xs"></i></a>
                            <a href="properties.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('Eliminar este imóvel?')" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors"><i class="fas fa-trash text-xs"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($properties)): ?>
                <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Nenhum imóvel encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
