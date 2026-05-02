# 🏠 Luxury CRM

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/PostgreSQL-Supabase-green.svg" alt="Database">
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
  <img src="https://img.shields.io/badge/Version-1.0.0-purple.svg" alt="Version">
</p>

---

## 📌 Visão Geral

**Luxury CRM** é um sistema de gestão de relacionamento com clientes (CRM) moderno e completo, desenvolvido especificamente para o segmento de imóveis de luxo. O sistema permite gerenciar clientes, imóveis, negócios pipeline e tarefas de forma eficiente e intuitiva.

Este projeto foi desenvolvido com foco em usabilidade, performance e design premium, utilizando PHP moderno e banco de dados PostgreSQL.

---

## ✨ Funcionalidades Principais

### 📊 Dashboard
- **KPIs Dinâmicos**: Visualização em tempo real de negócios ativos, fechados ganho, valor do pipeline e imóveis disponíveis
- **Pipeline Kanban Mini**: Visão geral do pipeline diretamente no dashboard
- **Navegação Inteligente**: Clique nos cartões para acessar as respectivas páginas
- **Histórico de Atividades**: Feed automático de últimas ações no sistema

### 💼 Pipeline Kanban (Negócios)
- **Drag & Drop**: Arraste e solte cartões entre as etapas do pipeline
- **8 Etapas do Pipeline**:
  - Novo Lead → Contacto Inicial → Visita Agendada → Em Negociação → Proposta Submetida → Contrato → Fechado Ganho/Fechado Perdido
- **Associação Automática**: Ao selecionar um imóvel, o valor do negócio é preenchido automaticamente
- **Imóvel Externo**: Possibilidade de criar imóveis de outras agências
- **Restrição Inteligente**: Imóvel em Proposta Submetida não pode ter outro negócio ativo simultaneamente

### 👥 Gestão de Clientes
- Cadastro completo de clientes (compradores/vendedores/investidores)
- Controle de orçamento mínimo e máximo
- Histórico de preferências e notas
- Status ativo/inativo

### 🏡 Gestão de Imóveis
- Multiple tipos de imóvel: Apartamento, Moradia, Vivenda, Terreno, Comercial
- Estados: Disponível, Reservado, Vendido, Arrendado, Indisponível
- Galeria de imagens e documentos
- Featured (destaque)
- Imóveis externos (de outras agências)

### ✅ Gestão de Tarefas
- Prioridades: Urgente, Alta, Média, Baixa
- Status: Pendente, Em Andamento, Concluído
- Datas limite e notificações
- Associação com clientes e imóveis

### 📅 Calendário
- Visualização mensal com eventos
- Tarefas e prazos de negócios integrados
- Lista de próximos eventos

### 📈 Sistema de Relatórios
- Contadores em tempo real
- Pipeline value automático
- Fechados por mês

---

## 🛠️ Tecnologias Utilizadas

| Categoria | Tecnologia |
|-----------|------------|
| **Backend** | PHP 8.2 |
| **Database** | PostgreSQL (Supabase) |
| **Frontend** | HTML5, Tailwind CSS |
| **Servidor** | XAMPP (Apache) |
| **Autenticação** | Session-based com CSRF Protection |

---

## 📋 Estrutura do Banco de Dados

```
users          → Utilizadores do sistema
clients        → Clientes (compradores/vendedores)
properties     → Imóveis
deal_stages    → Etapas do pipeline
deals          → Negócios/oportunidades
tasks          → Tarefas
activities     → Histórico de atividades
media          → Anexos de imóveis
```

---

## 🚀 Instalação e Configuração

### Pré-requisitos
- PHP 8.2 ou superior
- XAMPP ou similar (Apache)
- Extensões PHP: pdo_pgsql, pgsql
- Conta no Supabase ( PostgreSQL )

### Passos de Instalação

1. **Clone o repositório**
   ```bash
   git clone https://github.com/elipereiraoficial/Dev.git
   ```

2. **Configure o banco de dados**
   - Crie uma conta no [Supabase](https://supabase.com)
   - Crie um novo projeto
   - Execute o script `setup.php` para criar as tabelas

3. **Configure o arquivo config.php**
   ```php
   define('DB_HOST', 'seu-host-supabase');
   define('DB_PORT', '5432');
   define('DB_NAME', 'postgres');
   define('DB_USER', 'postgres');
   define('DB_PASS', 'sua-senha');
   ```

4. **Inicie o servidor**
   - Inicie o Apache no XAMPP
   - Acesse: `http://localhost/luxury-crm/`

### Credenciais Padrão
- **Email**: admin@luxury.pt
- **Senha**: admin123

---

## 🎨 Design e UI/UX

O sistema foi desenvolvido com um design premium e elegante:

- **Paleta de Cores**: Dourado (#d4af37) como cor principal, com tons de cinza e azul
- **Tipografia**: Fontes modernas e legíveis
- **Componentes**: Cards com hover effects, transições suaves
- **Responsividade**: Totalmente responsivo para desktop e mobile

---

## 📱 Screenshots

O sistema inclui:
- Dashboard com KPIs interativos
- Pipeline Kanban visual
- Calendário mensal
- Listas filtráveis
- Formulários de edição completa

---

## 🔒 Segurança

- ✅ Proteção CSRF em todos os formulários
- ✅ Passwords hasheadas com `password_hash()`
- ✅ Sessões seguras com nome customizado
- ✅ Limpeza de inputs contra XSS
- ✅ Prepared statements contra SQL Injection

---

## 📄 Licença

Este projeto está licenciado sob a licença MIT.

---

## 👨‍💻 Autor

**Eli Pereira**
- GitHub: [@elipereiraoficial](https://github.com/elipereiraoficial)
- Email: eli@luxurycrm.com

---

## 🙏 Agradecimentos

Agradecimento especial ao OpenCode AI pela assistência no desenvolvimento deste projeto.

---

<p align="center">
  <sub>Desenvolvido com ❤️ e ☕</sub>
</p>