# Luxury CRM - Technical Documentation

> **OBRIGATÓRIO**: Toda correção ou melhoria deve ser documentada aqui.
> **IMPORTANTE**: Antes de CADA commit/push, devo:
> 1. Testar o código
> 2. Atualizar MEMORIA.md
> 3. Remover ficheiros de debug
> 4. Verificar WORKFLOW.md para guidelines

---

## 📋 Project Overview

**Luxury CRM** - Complete CRM for luxury real estate
- **Stack**: PHP 8.2 + MySQL
- **Hostinger**: crm.elipereira.com

---

## 🏗️ Build Info
- **Last deploy test**: 04/05/2026
- **Auto-deploy**: ✅ FTP GitHub Actions working
- **FTP Status**: Working - Automatic deploy on every push

---

## 🚀 Current Status

- **Domain**: https://crm.elipereira.com
- **Database**: MySQL (Hostinger)
- **Status**: ✅ Fully deployed and working with test data
- **FTP Deploy**: ✅ Working - Auto-deploy on push
- **Last Update**: 04/05/2026

---

## 🔧 Recent Fixes

### 2026-05-04 - FTP Deploy Working ✅
- GitHub Actions FTP deploy now working correctly
- Automatic deployment on every push to master
- Workflow file: `.github/workflows/deploy.yml`
- FTP credentials configured in GitHub secrets

### 2026-05-04 - Kanban Auto-Refresh
- Added automatic page refresh (500ms) after dropping a card in kanban
- Solves issue where column counts weren't updating in real-time
- Backend was already working correctly (confirmed by F5 refresh)

---

## 👥 Users Created

| Email | Name | Role | Password |
|-------|------|------|----------|
| admin@luxury.pt | Administrador | admin | admin123 |
| maria.santos@luxury.pt | Maria Santos | vendas | 123456 |
| pedro.costa@luxury.pt | Pedro Costa | vendas | 123456 |
| sofia.ferreira@luxury.pt | Sofia Ferreira | gerente | 123456 |
| joao.lima@luxury.pt | João Lima | vendas | 123456 |
| ana.rodrigues@luxury.pt | Ana Rodrigues | vendas | 123456 |
| miguel.santos@luxury.pt | Miguel Santos | suporte | 123456 |
| laura.martins@luxury.pt | Laura Martins | gerente | 123456 |
| tiago.almeida@luxury.pt | Tiago Almeida | vendas | 123456 |
| carla.sousa@luxury.pt | Carla Sousa | vendas | 123456 |
| ricardo.mendes@luxury.pt | Ricardo Mendes | vendas | 123456 |
| patricia.silva@luxury.pt | Patrícia Silva | gerente | 123456 |

---

## 🏠 Properties Created

| Reference | Title | City | Price | Status |
|-----------|-------|------|-------|--------|
| LIS-001 | Apartamento T2 Centro | Lisboa | €450.000 | available |
| CAS-001 | Moradia Moderna Cascais | Cascais | €1.250.000 | available |
| LIS-002 | Loft Industrial Alfama | Lisboa | €380.000 | available |
| POR-001 | Penthouse Vista Rio | Porto | €890.000 | reserved |
| DOU-001 | Quinta com Vinha Douro | Douro | €2.500.000 | available |

---

## 🤝 Clients Created

- Carlos Silva, Patricia Gomes, Manuel Ferreira, Isabel Costa
- Jorge Martins, Susana Almeida, Paulo Rodrigues, Renata Sousa

---

## 💼 Deals (Pipeline) Created

| Reference | Stage | Value | Status |
|-----------|-------|-------|--------|
| 2025-001 | Novo Lead | €420.000 | open |
| 2025-002 | Contacto Inicial | €1.200.000 | open |
| 2025-003 | Visita Agendada | €350.000 | open |
| 2025-004 | Em Negociação | €850.000 | open |
| 2025-005 | Proposta Submetida | €2.300.000 | open |
| 2025-006 | Contrato | €410.000 | open |
| 2025-008 | Fechado Ganho | €380.000 | won |
| 2025-012 | Fechado Perdido | €360.000 | lost |

---

## ✅ Implemented Features

### Dashboard
- [x] Interactive KPIs (clickable)
- [x] Mini Kanban Pipeline
- [x] Clickable references
- [x] Clickable clients

### Pipeline Kanban (kanban.php)
- [x] Drag & drop between stages
- [x] Auto-fill value from property
- [x] External properties
- [x] Property title in cards
- [x] Auto-refresh after drop

### Calendar
- [x] Monthly view
- [x] Clickable tasks and deals
- [x] Upcoming events

### Clients, Properties, Tasks, Settings
- [x] Full CRUD operations

---

## 🔧 MySQL Conversions Applied

1. PostgreSQL → MySQL connection
2. `EXTRACT()` → `MONTH()`, `YEAR()`
3. `CURRENT_DATE` → `CURDATE()`
4. `NOW() - INTERVAL` → `DATE_SUB(NOW(), INTERVAL)`
5. `active = true` → `active = 1`
6. New `database_mysql.sql` created
7. `INTERVAL '30 days'` → `INTERVAL 30 DAY` (FIX: index.php line 48)

---

## 🔐 Login Issues Fixed

1. **Password Hash Issue**: Password was incorrect in database
   - Fixed by updating password hash for admin@luxury.pt

2. **HTTP 500 on Dashboard**: MySQL syntax error in INTERVAL
   - Fixed in `index.php` line 48: changed `INTERVAL '30 days'` to `INTERVAL 30 DAY`

3. **Auto-Seed Script**: Creates test data on first login
   - Added in `includes/auth.php` function `seedTestDataIfNeeded()`
   - Removed non-existent `department` column from INSERT

---

## 🚫 Duplicate Prevention (02/05/2026)

### Database Level
- [x] Unique constraint on clients.email
- [x] Unique constraint on properties.reference
- [x] Unique constraint on users.email
- [x] Cleanup of existing duplicates

### Application Level
- [x] `clientEmailExists()` - Check for duplicate email
- [x] `propertyReferenceExists()` - Check for duplicate reference
- [x] `userEmailExists()` - Check for duplicate user email
- [x] `findClientDuplicates()` - Find potential duplicates

### Validation Flow
| Action | Check |
|--------|-------|
| New Client | Duplicate email → Block with message |
| Update Client | Duplicate email (other record) → Block |
| New Property | Duplicate reference → Block with message |
| Update Property | Duplicate reference (other record) → Block |

### Files
- `scripts/duplicate_fix.sql` - Database constraints + cleanup
- `clients.php` - Email validation on create/update
- `properties.php` - Reference validation on create/update
- `functions.php` - Helper functions

---

## 📱 WhatsApp Integration (02/05/2026)

### Features
- [x] Botão WhatsApp na lista de clientes
- [x] Formatação automática do número (formato internacional)
- [x] Link direto: wa.me/351XXXXXXXXX

### Implementation
- `formatWhatsApp()` - Converte número para link WhatsApp
- Adicionado nas tabelas de clientes

---

## 📧 Marketing Automation (02/05/2026)

### Features
- [x] Email de boas-vindas automático ao criar cliente
- [x] Funções: `sendWelcomeEmail()`, `sendFollowUpEmail()`
- [x] Notificações de negócio ganho/perdido
- [x] Tracking de emails enviados na BD
- [x] Função: `getAutomationStats()`

### Email Triggers
| Evento | Ação |
|--------|------|
| Novo cliente criado | Enviar email boas-vindas |
| 3 dias sem atividade | Enviar follow-up |
| Negócio fechado (won) | Notificar agente |
| Negócio perdido (lost) | Notificar agente |

### Files
- `scripts/marketing_automation.sql` - Schema
- `clients.php` - Auto-envio de email ao criar cliente

---

## 📊 Audit Trail (02/05/2026)

### Created Tables
- `audit_log` - Registo completo de todas as ações

### Audit Functions (functions.php)
- `logAudit()` - Regista ação na tabela
- `auditCreate()`, `auditUpdate()`, `auditDelete()` - Helpers
- `auditLogin()` - Regista tentativas de login
- `getAuditLog()` - Consulta logs (admin)

### Logged Actions
- Login bem-sucedido/falhado
- Criação/Update/Delete de registos
- Alterações de password
- 2FA enabled/disabled

---

## 🔐 Two-Factor Authentication (02/05/2026)

### Features
- [x] Código de 6 dígitos
- [x] Validade de 5 minutos
- [x] Armazenamento em sessão (não BD)
- [x] Funções: enableTwoFactor(), disableTwoFactor()
- [x] Verificação: verifyTwoFactorCode()

### Files Added
- `scripts/two_factor.sql` - Schema
- `includes/auth.php` - Funções 2FA

---

## 💾 Backup System (02/05/2026)

### Features
- Backup automático via mysqldump
- Compressão gzip
- Rotação: mantém últimos 7 backups
- Logging de sucesso/falha

### Files Added
- `scripts/backup.php` - Script de backup
- Execute via cron: `0 2 * * * php /path/to/backup.php`

---

## 🔒 Security Enhancements (02/05/2026)

### Session Security (config.php)
- [x] Secure session cookies (httponly, secure, samesite)
- [x] Session strict mode enabled
- [x] Session ID regeneration on login
- [x] Session timeout (30 min inactivity)
- [x] Session ID rotation every 5 min

### Security Headers (config.php)
- [x] X-Content-Type-Options: nosniff
- [x] X-Frame-Options: SAMEORIGIN
- [x] X-XSS-Protection
- [x] Referrer-Policy
- [x] Content-Security-Policy (CSP)

### Login Protection (auth.php)
- [x] Rate limiting: 5 tentativas em 15 min
- [x] Session fixation prevention (regenerate_id on login)
- [x] Failed attempt tracking by IP+email
- [x] Session timeout after inactivity
- [x] Security logging for failed logins

### Input Validation (functions.php)
- [x] SQL injection prevention in getCount() (whitelist tables)
- [x] Added validateEmail(), validatePhone() helpers
- [x] Added sanitizeFilename() for future uploads
- [x] Added securityLog() for audit trail

### Password Security (settings.php)
- [x] Minimum 8 characters required
- [x] Must contain uppercase letter
- [x] Must contain number
- [x] Security logging on password change

### Error Handling (config.php)
- [x] Removed detailed DB error messages in production
- [x] Errors logged to server log instead of display

---

## 🧹 Code Cleanup (02/05/2026)

**Arquivos Eliminados (debug/temporários):**
- `debug.php` - Debugger temporário
- `test_login.php` - Teste de login
- `fix.php`, `fix_complete.php`, `fix_admin.php` - Scripts de correção
- `diagnostico.php` - Diagnóstico
- `phpinfo.php` - Info PHP (risco segurança)
- `setup.php` - Setup temporário

**Scripts Eliminados:**
- `scripts/debug_seed.php`
- `scripts/insert_test_data.php`
- `scripts/tables.php`
- `scripts/verify.php`
- `scripts/test_data.sql`

**Scripts Mantidos:**
- `scripts/seed.php` - Seed de dados de teste (útil para futuras resets)

---

## 📦 Project Structure

```
luxury-crm/
├── index.php          # Dashboard principal
├── login.php          # Autenticação
├── logout.php         # Terminar sessão
├── config.php         # Configuração BD
├── deals.php          # Gestão de negócios
├── clients.php        # Gestão de clientes
├── properties.php     # Gestão de imóveis
├── tasks.php          # Gestão de tarefas
├── calendar.php       # Calendário
├── activities.php     # Registo de atividades
├── settings.php       # Configurações sistema
├── includes/
│   ├── auth.php       # Funções autenticação + auto-seed
│   ├── functions.php  # Funções utilitárias
│   ├── header.php     # Header HTML
│   ├── footer.php     # Footer HTML
│   └── sidebar.php    # Menu lateral
├── scripts/
│   └── seed.php       # Seed dados teste
├── database_mysql.sql  # Schema base dados
└── MEMORIA.md         # Documentação técnica
```

---

## 🌍 GitHub

- **URL**: https://github.com/elipereiraoficial/Dev
- **Branch**: master

---

## 📅 Last Update

**04/05/2026** - Auditoria & Correções ✅
- kanban.php now includes sidebar (full layout)
- Dashboard query fixed to show all 8 stages
- Seed.php: Fixed 2025-008 deal to use "Fechado Ganho" stage (was incorrectly using "Fechado Perdido")
- Added 2 new users: Ricardo Mendes, Patrícia Silva
- Fixed "Ver Pipeline Completo" link in dashboard to point to kanban.php

---

## 🔧 Database Configuration

```
Host: localhost
Database: u415107443_luxury_crm
User: u415107443_luxury_user
Pass: Cadu5540!!
```

---

## 📝 Development Workflow

### Before Any Commit
1. ✅ Test the code works
2. ✅ Run lint/typecheck if available
3. ✅ Update MEMORIA.md with changes
4. ✅ Review what files changed

### Commit Message Format
```
<type>: <description>

[optional: details]
```

Types: `feat`, `fix`, `refactor`, `docs`, `cleanup`, `deploy`

### Git Flow
```bash
# 1. Make changes
# 2. Test locally
# 3. Update MEMORIA.md
# 4. Commit with descriptive message
# 5. Push to remote
```

---

## 📝 Notes for Future Development

- Scripts folder contains seed.php for test data population
- Auto-seed runs on first login (checks if users > 1)
- All tables use proper MySQL syntax
- Passwords use PHP password_hash() with BCRYPT
- **IMPORTANT**: Always update MEMORIA.md before pushing!