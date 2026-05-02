<?php
// Setup script - run once to create tables
require_once 'config.php';

echo "Connecting to Supabase...\n";

try {
    // Create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'agent',
        phone VARCHAR(50),
        avatar VARCHAR(255),
        active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Users table created\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        phone VARCHAR(50),
        type VARCHAR(20) DEFAULT 'buyer',
        source VARCHAR(100),
        budget_min DECIMAL(15,2),
        budget_max DECIMAL(15,2),
        preferences TEXT,
        notes TEXT,
        status VARCHAR(20) DEFAULT 'prospect',
        assigned_to INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Clients table created\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS properties (
        id SERIAL PRIMARY KEY,
        reference VARCHAR(50) UNIQUE NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        address VARCHAR(255),
        city VARCHAR(100),
        region VARCHAR(100),
        postal_code VARCHAR(20),
        price DECIMAL(15,2) NOT NULL,
        type VARCHAR(20) DEFAULT 'apartment',
        status VARCHAR(20) DEFAULT 'available',
        bedrooms INTEGER DEFAULT 0,
        bathrooms INTEGER DEFAULT 0,
        area_m2 INTEGER,
        featured BOOLEAN DEFAULT FALSE,
        images TEXT,
        owner_id INTEGER,
        agent_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES clients(id) ON DELETE SET NULL,
        FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Properties table created\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS deal_stages (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        stage_order INTEGER NOT NULL,
        color VARCHAR(20) DEFAULT '#64748b',
        is_closed BOOLEAN DEFAULT FALSE,
        is_won BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Deal stages table created\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS deals (
        id SERIAL PRIMARY KEY,
        reference VARCHAR(50) UNIQUE NOT NULL,
        title VARCHAR(255) NOT NULL,
        client_id INTEGER NOT NULL,
        property_id INTEGER,
        stage_id INTEGER NOT NULL,
        value DECIMAL(15,2),
        commission_percent DECIMAL(5,2) DEFAULT 5.00,
        probability INTEGER DEFAULT 0,
        expected_close DATE,
        actual_close DATE,
        status VARCHAR(20) DEFAULT 'open',
        notes TEXT,
        agent_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
        FOREIGN KEY (stage_id) REFERENCES deal_stages(id) ON DELETE RESTRICT,
        FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Deals table created\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        related_to VARCHAR(20),
        related_id INTEGER,
        due_date DATE,
        priority VARCHAR(20) DEFAULT 'medium',
        status VARCHAR(20) DEFAULT 'pending',
        assigned_to INTEGER,
        created_by INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Tasks table created\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS activities (
        id SERIAL PRIMARY KEY,
        type VARCHAR(20) DEFAULT 'note',
        description TEXT NOT NULL,
        related_to VARCHAR(20),
        related_id INTEGER,
        user_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Activities table created\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS media (
        id SERIAL PRIMARY KEY,
        property_id INTEGER,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255),
        file_type VARCHAR(50),
        uploaded_by INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Media table created\n";

    // Insert default data
    $pdo->exec("INSERT INTO deal_stages (name, stage_order, color, is_closed, is_won) VALUES
        ('Novo Lead', 1, '#64748b', FALSE, FALSE),
        ('Contacto Inicial', 2, '#3b82f6', FALSE, FALSE),
        ('Visita Agendada', 3, '#8b5cf6', FALSE, FALSE),
        ('Em Negociação', 4, '#f59e0b', FALSE, FALSE),
        ('Proposta Submetida', 5, '#f97316', FALSE, FALSE),
        ('Contrato', 6, '#ec4899', FALSE, FALSE),
        ('Fechado Ganho', 7, '#10b981', TRUE, TRUE),
        ('Fechado Perdido', 8, '#ef4444', TRUE, FALSE)
        ON CONFLICT DO NOTHING");
    echo "✓ Default deal stages inserted\n";

    $pdo->exec("INSERT INTO users (name, email, password, role) VALUES
        ('Administrador', 'admin@luxury.pt', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
        ON CONFLICT DO NOTHING");
    echo "✓ Default admin user inserted\n";

    echo "\n✅ Setup completo! Agora podes aceder ao CRM.\n";
    echo "   Login: admin@luxury.pt / admin123\n";

} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}