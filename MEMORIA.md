# Luxury CRM - Technical Documentation

> **OBRIGATÓRIO**: Toda correção ou melhoria deve ser documentada aqui.

---

## 📋 Project Overview

**Luxury CRM** is a complete Customer Relationship Management system for luxury real estate, developed in PHP 8.2 with PostgreSQL (Supabase).

---

## 🚀 Current Status

### Environment
- **Server**: XAMPP (Apache)
- **Database**: Supabase PostgreSQL
- **Project Path**: `C:\xampp\htdocs\luxury-crm`
- **URL**: http://localhost/luxury-crm/

---

## 👥 Users Created

| Email | Name | Role | Password |
|-------|------|------|----------|
| admin@luxury.pt | Administrador | admin | admin123 |
| maria@luxury.pt | Maria Silva | agent | 123456 |
| joao@luxury.pt | João Santos | agent | 123456 |
| ana@luxury.pt | Ana Costa | manager | 123456 |

---

## 📦 Database Tables

1. **users** - System users
2. **clients** - Clients (buyer/seller)
3. **properties** - Properties
4. **deal_stages** - Pipeline stages (8 stages)
5. **deals** - Business opportunities
6. **tasks** - Tasks
7. **activities** - Activity history
8. **media** - Property attachments

---

## ✅ Implemented Features

### Dashboard
- [x] Interactive KPIs (clickable)
- [x] Mini Kanban Pipeline
- [x] Clickable references (go to deal)
- [x] Clickable clients (go to client)
- [x] Task list
- [x] Activity feed

### Pipeline Kanban (deals.php)
- [x] Drag & drop between stages
- [x] Auto-fill value from property
- [x] External properties (other agencies)
- [x] Restriction: Property in Proposal Submitted cannot have another deal
- [x] Property title in deal card

### Calendar
- [x] Monthly view
- [x] Clickable tasks (go to edit)
- [x] Clickable deals (go to edit)
- [x] Upcoming events list

### Clients
- [x] List with search
- [x] Create/edit client
- [x] Type: buyer/seller/both

### Properties
- [x] List of properties
- [x] Create/edit property
- [x] Multiple types

### Tasks
- [x] Task list with filters
- [x] Create/edit task
- [x] Priority levels

---

## 🔧 Technical Fixes Applied

> **OBRIGATÓRIO**: Document all fixes here.

1. **[DONE]** PostgreSQL driver - Enabled pdo_pgsql and pgsql in php.ini
2. **[DONE]** PostgreSQL syntax - Changed MONTH(), YEAR(), CURDATE() to EXTRACT(), CURRENT_DATE
3. **[DONE]** Boolean comparison - Changed `active = 1` to `active = true`
4. **[DONE]** Duplicate stages - Removed duplicate stages (16 → 8)
5. **[DONE]** Duplicate buttons - Removed duplicate buttons in header
6. **[DONE]** Null property_id - Changed property_id = 0 to NULL
7. **[DONE]** Won status logic - Fixed logic when moving to "Fechado Ganho"
8. **[DONE]** Calendar events - Added type field and made clickable

---

## 🌍 GitHub Repository

- **URL**: https://github.com/elipereiraoficial/Dev
- **Branch**: master
- **Files committed**: 22 files

---

## 📅 Version History

**v1.0.0** - 02/05/2026
- Initial release
- All core modules: Dashboard, Pipeline, Clients, Properties, Tasks, Calendar, Activities, Settings

---

## 🔄 Update Log

| Date | Change | Status |
|------|--------|--------|
| 02/05/2026 | Initial project setup | DONE |
| 02/05/2026 | PostgreSQL fixes | DONE |
| 02/05/2026 | Dashboard improvements | DONE |
| 02/05/2026 | Kanban restrictions | DONE |
| 02/05/2026 | GitHub upload | DONE |
| 02/05/2026 | English README | DONE |

---

**Last Updated**: 02/05/2026