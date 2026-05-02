# Luxury CRM - Documentação Completa do Projeto

## 📋 Visão Geral do Projeto

**Luxury CRM** é um sistema de gestão de relacionamento com clientes para o segmento de imobiliário de luxo. Desenvolvido em PHP com banco de dados PostgreSQL (Supabase).

---

## 🚀 Configuração do Ambiente

### Ambiente de Desenvolvimento
- **Servidor**: XAMPP (Apache + MySQL)
- **PHP**: 8.2.12
- **Banco de Dados**: Supabase PostgreSQL
- **Path do Projeto**: `C:\xampp\htdocs\luxury-crm`

### Configurações Técnicas
- Extensões PHP ativadas: `pdo_pgsql`, `pgsql`
- Conexão configurada em `config.php`
- Timezone: Europe/Lisbon

---

## 📦 Estrutura de Arquivos

```
luxury-crm/
├── config.php           # Configuração da conexão com banco
├── setup.php            # Script de criação das tabelas
├── index.php            # Dashboard principal
├── login.php            # Página de login
├── logout.php           # Logout
├── deals.php            # Pipeline Kanban de negócios
├── clients.php          # Gestão de clientes
├── properties.php       # Gestão de imóveis
├── tasks.php            # Gestão de tarefas
├── activities.php       # Histórico de atividades
├── calendar.php         # Calendário
├── settings.php         # Configurações do usuário
├── includes/
│   ├── auth.php         # Funções de autenticação
│   ├── functions.php    # Funções auxiliares
│   ├── header.php       # Cabeçalho HTML
│   ├── sidebar.php      # Menu lateral
│   └── footer.php       # Rodapé
├── assets/              # Arquivos CSS/JS
├── uploads/             # Arquivos上传
└── MEMORIA.md           # Este documento
```

---

## 👥 Usuários Criados

| Email | Nome | Cargo | Password |
|-------|------|-------|----------|
| admin@luxury.pt | Administrador | admin | admin123 |
| maria@luxury.pt | Maria Silva | agent | 123456 |
| joao@luxury.pt | João Santos | agent | 123456 |
| ana@luxury.pt | Ana Costa | manager | 123456 |

---

## 🗄️ Estrutura do Banco de Dados

### Tabelas Criadas

1. **users** - Utilizadores do sistema
2. **clients** - Clientes (compradores/vendedores)
3. **properties** - Imóveis
4. **deal_stages** - Etapas do pipeline Kanban
5. **deals** - Negócios/oportunidades
6. **tasks** - Tarefas
7. **activities** - Histórico de atividades
8. **media** - Anexos de imóveis

### Stages do Pipeline (8 etapas)
1. Novo Lead
2. Contacto Inicial
3. Visita Agendada
4. Em Negociação
5. Proposta Submetida
6. Contrato
7. Fechado Ganho
8. Fechado Perdido

---

## ✨ Funcionalidades Implementadas

### Dashboard
- ✅ KPIs clicáveis (Negócios Ativos, Fechados Ganho, Valor Pipeline, Imóveis)
- ✅ Pipeline Kanban mini no dashboard
- ✅ Referências clicáveis que levam para a ficha do negócio
- ✅ Clientes clicáveis que levam para a ficha do cliente
- ✅ Lista de tarefas próximas
- ✅ Histórico de atividades recentes

### Pipeline Kanban (deals.php)
- ✅ Arrastar e soltar para mover negócios entre etapas
- ✅ Auto-preenchimento do valor do negócio ao selecionar imóvel
- ✅ Imóvel externo: criar imóvel de outra agência
- ✅ Restrição: Imóvel em Proposta Submetida não pode ter outro negócio ativo
- ✅ Título do negócio mostra o imóvel associado
- ✅ Botão "Novo Negócio" no header

### Clientes
- ✅ Lista de clientes com pesquisa
- ✅ Criar/editar cliente
- ✅ Tipo: buyer/seller/both
- ✅ Orçamento mínimo e máximo

### Imóveis
- ✅ Lista de imóveis
- ✅ Criar/editar imóvel
- ✅ Tipos: apartment, house, villa, land, commercial
- ✅ Estados: available, reserved, sold, rented, unavailable

### Tarefas
- ✅ Lista de tarefas com filtros
- ✅ Criar/editar tarefa
- ✅ Prioridades: urgent, high, medium, low
- ✅ Status: pending, in_progress, completed

### Calendário
- ✅ Visualização mensal
- ✅ Eventos de tarefas clicáveis (vai para edição da tarefa)
- ✅ Eventos de negócios (fecho previsto) clicáveis
- ✅ Próximos eventos listados

### Configurações
- ✅ Editar perfil
- ✅ Alterar password

---

## 🔧 Correções Técnicas Realizadas

1. **Driver PostgreSQL**: Ativadas extensões pdo_pgsql e pgsql no php.ini
2. **Sintaxe PostgreSQL**: Corrigidos MONTH(), YEAR(), CURDATE() → EXTRACT(), CURRENT_DATE
3. **Boolean no PostgreSQL**: Corrigido `active = 1` → `active = true`
4. **Stages Duplicados**: Eliminados stages duplicados (16 → 8)
5. **Botões Duplicados**: Removidos botões duplicados no header
6. **Valor Nulo**: Corrigido property_id = 0 → NULL
7. **Status Won**: Corrigida lógica de atualização de status ao mover para Fechado Ganho

---

## 🌐 URLs de Acesso

- **Aplicação**: http://localhost/luxury-crm/
- **Dashboard**: http://localhost/luxury-crm/index.php
- **Login**: http://localhost/luxury-crm/login.php

---

## 📝 Notas Importantes

1. O sistema usa autenticação por sessão
2. CSRF protection implementado
3. Passwords são hasheadas com password_hash()
4. Imóvel em etapa Proposta Submetida ou posterior bloqueia criação de novo negócio para o mesmo imóvel
5. Ao mover negócio para "Fechado Ganho", o campo actual_close é preenchido automaticamente
6. Ao mover negócio para "Fechado Perdido" ou "Contrato", status volta para "open"

---

## 📅 Histórico de Versão

**Versão 1.0.0** - 02/05/2026
- Lançamento inicial do Luxury CRM
- Módulos: Dashboard, Pipeline Kanban, Clientes, Imóveis, Tarefas, Calendário, Atividades, Configurações
- Integração com Supabase PostgreSQL

---

**Documento gerado em: 02/05/2026**
**Total de sessões: 1**