<?php
require_once 'includes/auth.php';
requireAuth();

$page = 'Definições';

$user = currentUser();
$isAdmin = $_SESSION['user_role'] === 'admin';

$message = '';
$error = '';

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        $error = 'Token de segurança inválido.';
    } else {
        $name = clean($_POST['name'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $phone = clean($_POST['phone'] ?? '');

        if (empty($name) || empty($email)) {
            $error = 'Nome e email são obrigatórios.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
            $_SESSION['user_name'] = $name;
            $message = 'Perfil atualizado com sucesso.';
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        $error = 'Token de segurança inválido.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $error = 'Todos os campos de palavra-passe são obrigatórios.';
        } elseif ($new !== $confirm) {
            $error = 'As palavras-passe não coincidem.';
        } elseif (strlen($new) < 8) {
            $error = 'A palavra-passe deve ter pelo menos 8 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $new)) {
            $error = 'A palavra-passe deve ter pelo menos uma maiúscula.';
        } elseif (!preg_match('/[0-9]/', $new)) {
            $error = 'A palavra-passe deve ter pelo menos um número.';
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $userData = $stmt->fetch();

            if (!password_verify($current, $userData['password'])) {
                $error = 'Palavra-passe atual incorreta.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$hash, $_SESSION['user_id']]);
                error_log("[SECURITY] Password changed for user ID: " . $_SESSION['user_id']);
                $message = 'Palavra-passe alterada com sucesso.';
            }
        }
    }
}

$user = currentUser();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
    </div>
    <?php endif; ?>

    <?php if ($message): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
        <i class="fas fa-check-circle mr-2"></i><?php echo $message; ?>
    </div>
    <?php endif; ?>

    <!-- Profile Settings -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-luxury-900">Perfil</h3>
        </div>
        <form method="POST" class="p-6 space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="update_profile" value="1">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome</label>
                    <input type="text" name="name" value="<?php echo clean($user['name'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo clean($user['email'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Telefone</label>
                    <input type="tel" name="phone" value="<?php echo clean($user['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cargo</label>
                    <input type="text" value="<?php echo ucfirst($user['role'] ?? 'agent'); ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500" disabled>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 gold-gradient text-luxury-900 font-semibold rounded-xl hover:opacity-90 shadow-md transition-opacity">Guardar Alterações</button>
            </div>
        </form>
    </div>

    <!-- Password Change -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-luxury-900">Alterar Palavra-passe</h3>
        </div>
        <form method="POST" class="p-6 space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="change_password" value="1">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Palavra-passe Atual</label>
                    <input type="password" name="current_password" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nova Palavra-passe</label>
                    <input type="password" name="new_password" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar</label>
                    <input type="password" name="confirm_password" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-luxury-gold focus:ring-2 focus:ring-luxury-gold/20 outline-none transition-all">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-slate-800 text-white font-semibold rounded-xl hover:bg-slate-700 shadow-md transition-colors">Alterar Palavra-passe</button>
            </div>
        </form>
    </div>

    <!-- App Info -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-luxury-900">Informações do Sistema</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-slate-400 mb-1">Versão</p>
                    <p class="font-medium text-luxury-900"><?php echo APP_VERSION; ?></p>
                </div>
                <div>
                    <p class="text-slate-400 mb-1">PHP</p>
                    <p class="font-medium text-luxury-900"><?php echo phpversion(); ?></p>
                </div>
                <div>
                    <p class="text-slate-400 mb-1">Utilizador</p>
                    <p class="font-medium text-luxury-900"><?php echo clean($user['name']); ?></p>
                </div>
                <div>
                    <p class="text-slate-400 mb-1">Último Acesso</p>
                    <p class="font-medium text-luxury-900"><?php echo isset($user['updated_at']) ? formatDate($user['updated_at'], true) : '-'; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>