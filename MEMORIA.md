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
- **Status**: Ready for deployment

---

## 👥 Users Created

| Email | Name | Role | Password |
|-------|------|------|----------|
| admin@luxury.pt | Administrador | admin | admin123 |
| maria@luxury.pt | Maria Silva | agent | 123456 |
| joao@luxury.pt | João Santos | agent | 123456 |
| ana@luxury.pt | Ana Costa | manager | 123456 |

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

---

## 📦 Files Updated

- config.php - MySQL connection
- database_mysql.sql - MySQL schema
- index.php - MySQL queries
- deals.php - MySQL queries
- calendar.php - MySQL queries
- tasks.php - MySQL queries
- clients.php - MySQL queries
- properties.php - MySQL queries
- activities.php - MySQL queries
- includes/auth.php - MySQL queries

---

## 🌍 GitHub

- **URL**: https://github.com/elipereiraoficial/Dev
- **Branch**: master

---

## 📅 Last Update

**02/05/2026** - MySQL conversion completed