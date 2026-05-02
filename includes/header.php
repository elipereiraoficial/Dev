<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$page = $page ?? 'Dashboard';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo clean($page); ?> | <?php echo APP_NAME; ?></title>
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
                            700: '#334155',
                            gold: '#d4af37',
                            'gold-light': '#e8c84a',
                            'gold-dark': '#b8941f',
                            cream: '#faf9f6',
                            pearl: '#f0ede8',
                            sand: '#e6e2dd'
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
        body { font-family: 'Inter', sans-serif; background-color: #faf9f6; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .gold-gradient { background: linear-gradient(135deg, #d4af37 0%, #e8c84a 50%, #b8941f 100%); }
        .gold-text { background: linear-gradient(135deg, #d4af37, #b8941f); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(15, 23, 42, 0.12); }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(212, 175, 55, 0.1); border-right: 3px solid #d4af37; color: #d4af37; }
        .kanban-column { min-height: 400px; }
        .kanban-card { cursor: grab; transition: all 0.2s; }
        .kanban-card:active { cursor: grabbing; }
        .kanban-card:hover { transform: scale(1.02); }
        .kanban-drag-over { background-color: rgba(212, 175, 55, 0.05); border: 2px dashed #d4af37; }
        .kanban-card.updating { opacity: 0.5; }
        .kanban-card.flash-success { animation: flashGreen 1s; }
        @keyframes flashGreen {
            0%, 100% { box-shadow: 0 0 0 0 transparent; }
            50% { box-shadow: 0 0 20px 5px rgba(34, 197, 94, 0.5); }
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #d4af37; border-radius: 3px; }
    </style>
</head>
<body class="bg-luxury-cream text-slate-800">
    <?php if ($flash): ?>
    <div id="flash-msg" class="fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-xl text-white <?php echo $flash['type'] === 'success' ? 'bg-emerald-600' : 'bg-red-600'; ?> transition-opacity duration-500">
        <div class="flex items-center gap-3">
            <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo clean($flash['message']); ?></span>
        </div>
    </div>
    <script>
        setTimeout(() => { document.getElementById('flash-msg').style.opacity = '0'; setTimeout(() => document.getElementById('flash-msg').remove(), 500); }, 4000);
    </script>
    <?php endif; ?>
    <div class="flex h-screen overflow-hidden">
