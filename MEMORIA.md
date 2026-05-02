# Luxury CRM - Technical Documentation

> **OBRIGATÓRIO**: Toda correção ou melhoria deve ser documentada aqui.

---

## 📋 Project Overview

**Luxury CRM** - Complete CRM for luxury real estate
- **Stack**: PHP 8.2 + MySQL
- **Hostinger**: crm.elipereira.com

---

## 🚀 Current Status

- **Domain**: https://crm.elipereira.com
- **Database**: MySQL (Hostinger)
- **Status**: ✅ Fully deployed and working with test data

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

### Pipeline Kanban
- [x] Drag & drop
- [x] Auto-fill value from property
- [x] External properties
- [x] Restriction for proposals
- [x] Property title in cards

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

## 📦 Files Updated

- `config.php` - MySQL connection
- `database_mysql.sql` - MySQL schema
- `index.php` - MySQL queries + INTERVAL fix
- `deals.php` - MySQL queries
- `calendar.php` - MySQL queries
- `tasks.php` - MySQL queries
- `clients.php` - MySQL queries
- `properties.php` - MySQL queries
- `activities.php` - MySQL queries
- `includes/auth.php` - MySQL queries + auto-seed

---

## 🌍 GitHub

- **URL**: https://github.com/elipereiraoficial/Dev
- **Branch**: master

---

## 📅 Last Update

**02/05/2026** - Complete system working with test data:
- 10 users seeded
- 5 properties seeded
- 8 clients seeded
- 9 deals (all pipeline stages) seeded
- 5 tasks seeded
- 6 activities seeded

---

## 🔧 Database Configuration

```
Host: localhost
Database: u415107443_luxury_crm
User: u415107443_luxury_user
Pass: Cadu5540!!
```

---

## 📝 Notes for Future Development

- Scripts folder contains seed.php for test data population
- Auto-seed runs on first login (checks if users > 1)
- All tables use proper MySQL syntax
- Passwords use PHP password_hash() with BCRYPT