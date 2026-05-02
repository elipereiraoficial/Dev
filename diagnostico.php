<?php
// Diagnostic page for Luxury CRM
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Luxury CRM</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #d4af37; }
        .status { padding: 15px; border-radius: 8px; margin: 10px 0; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Diagnóstico do Luxury CRM</h1>
        
        <h2>1. Extensões PHP Disponíveis</h2>
        <?php
        $extensions = get_loaded_extensions();
        $pgsql_extensions = array_filter($extensions, function($ext) {
            return stripos($ext, 'pgsql') !== false || stripos($ext, 'pdo') !== false;
        });
        
        if (count($pgsql_extensions) > 0) {
            echo '<div class="status success">✅ Extensões PostgreSQL encontradas:</div>';
            echo '<ul>';
            foreach ($pgsql_extensions as $ext) {
                echo '<li>' . $ext . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<div class="status error">❌ Extensões PostgreSQL NÃO encontradas!</div>';
            echo '<p><strong>Solução:</strong> O servidor de hosting não suporta conexão direta com PostgreSQL.</p>';
        }
        ?>
        
        <h2>2. Informações do PHP</h2>
        <table>
            <tr>
                <th>Versão PHP</th>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <th>Sistema Operativo</th>
                <td><?php echo php_uname(); ?></td>
            </tr>
        </table>
        
        <h2>3. Recomendação</h2>
        <div class="status warning">
            <strong>Problema:</strong> O plano de hospedagem partilhada (Shared Hosting) da Hostinger 
            não inclui o driver PostgreSQL (pdo_pgsql).
            <br><br>
            <strong>Soluções possíveis:</strong>
            <ol>
                <li>Migrar para um VPS (tens controlo total)</li>
                <li>Usar outro serviço de hosting com PostgreSQL (ex: Railway, Render)</li>
                <li>Converter o projeto para usar a API REST do Supabase</li>
            </ol>
        </div>
        
        <h2>4. Próximos Passos</h2>
        <p>Se quiseres continuar com a Hostinger, a melhor opção é:</p>
        <ol>
            <li>Exportar os dados do Supabase</li>
            <li>Criar um banco MySQL na Hostinger</li>
            <li>Converter o código de PostgreSQL para MySQL</li>
        </ol>
        <p>Isso requer alterações significativas no código. Queres que eu faça?</p>
        
        <br>
        <a href="https://github.com/elipereiraoficial/Dev" style="color: #d4af37;">Ver projeto no GitHub</a>
    </div>
</body>
</html>