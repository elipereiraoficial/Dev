<?php
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $loginResult = login($email, $password);
    
    if ($loginResult === true) {
        setFlash('success', 'Bem-vindo de volta!');
        header('Location: index.php');
        exit;
    } elseif ($loginResult === 'rate_limited') {
        $error = getFlash()['message'] ?? 'Demasiadas tentativas. Tente novamente mais tarde.';
    } else {
        $error = 'Email ou palavra-passe inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        luxury: {
                            900: '#0f172a',
                            800: '#1e293b',
                            gold: '#d4af37',
                            'gold-light': '#e8c84a',
                            'gold-dark': '#b8941f',
                            cream: '#faf9f6',
                            pearl: '#f0ede8'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .gold-gradient { background: linear-gradient(135deg, #d4af37 0%, #e8c84a 50%, #b8941f 100%); }
        .input-group:focus-within { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.15); }
    </style>
</head>
<body class="bg-luxury-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl gold-gradient mb-4 shadow-lg">
                <i class="fas fa-gem text-white text-2xl"></i>
            </div>
            <h1 class="font-serif text-3xl text-white mb-1">Luxury Estate <span class="text-luxury-gold">CRM</span></h1>
            <p class="text-slate-400 text-sm">Imobiliário de Luxo Portugal</p>
        </div>

        <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl p-8 shadow-2xl">
            <h2 class="text-white text-xl font-semibold mb-6">Iniciar Sessão</h2>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Email</label>
                    <div class="input-group flex items-center bg-luxury-800/50 border border-slate-700 rounded-xl px-4 py-3 transition-all">
                        <i class="fas fa-envelope text-slate-500 mr-3"></i>
                        <input type="email" name="email" required class="bg-transparent border-none outline-none text-white w-full placeholder-slate-500" placeholder="admin@luxury.pt">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Palavra-passe</label>
                    <div class="input-group flex items-center bg-luxury-800/50 border border-slate-700 rounded-xl px-4 py-3 transition-all">
                        <i class="fas fa-lock text-slate-500 mr-3"></i>
                        <input type="password" name="password" required class="bg-transparent border-none outline-none text-white w-full placeholder-slate-500" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="w-full gold-gradient text-luxury-900 font-semibold py-3.5 rounded-xl hover:opacity-90 transition-opacity shadow-lg shadow-luxury-gold/20">
                    Entrar
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-white/10 text-center">
                <p class="text-slate-500 text-xs">Credenciais padrão: admin@luxury.pt / [REDACTED]. See .env.example for setup.</p>
            </div>
        </div>

        <p class="text-center text-slate-600 text-xs mt-8">&copy; <?php echo date('Y'); ?> Luxury Estate CRM. Todos os direitos reservados.</p>
    </div>
</body>
</html>
