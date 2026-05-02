# 🏠 Luxury CRM

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/PostgreSQL-Supabase-green.svg" alt="Database">
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
  <img src="https://img.shields.io/badge/Version-1.0.0-purple.svg" alt="Version">
</p>

---

## 📌 Overview

**Luxury CRM** is a modern and complete customer relationship management (CRM) system, specifically developed for the luxury real estate segment. The system allows you to manage clients, properties, business pipelines, and tasks efficiently and intuitively.

This project was developed with a focus on usability, performance, and premium design, using modern PHP and PostgreSQL database.

---

## ✨ Key Features

### 📊 Dashboard
- **Dynamic KPIs**: Real-time view of active deals, closed won, pipeline value, and available properties
- **Mini Kanban Pipeline**: Overview of the pipeline directly on the dashboard
- **Smart Navigation**: Click on cards to access respective pages
- **Activity Feed**: Automatic feed of recent actions in the system

### 💼 Kanban Pipeline (Deals)
- **Drag & Drop**: Drag and drop cards between pipeline stages
- **8 Pipeline Stages**:
  - New Lead → Initial Contact → Scheduled Visit → In Negotiation → Proposal Submitted → Contract → Closed Won/Lost
- **Automatic Association**: When selecting a property, the deal value is automatically filled
- **External Properties**: Possibility to create properties from other agencies
- **Smart Restriction**: Property in "Proposal Submitted" stage cannot have another active deal simultaneously

### 👥 Client Management
- Complete client registration (buyers/sellers/investors)
- Minimum and maximum budget control
- Preferences and notes history
- Active/inactive status

### 🏡 Property Management
- Multiple property types: Apartment, House, Villa, Land, Commercial
- Status: Available, Reserved, Sold, Rented, Unavailable
- Image and document gallery
- Featured properties
- External properties (from other agencies)

### ✅ Task Management
- Priorities: Urgent, High, Medium, Low
- Status: Pending, In Progress, Completed
- Deadline dates and notifications
- Association with clients and properties

### 📅 Calendar
- Monthly view with events
- Tasks and deal deadlines integrated
- List of upcoming events

### 📈 Reporting System
- Real-time counters
- Automatic pipeline value
- Closed deals per month

---

## 🛠️ Technologies Used

| Category | Technology |
|-----------|------------|
| **Backend** | PHP 8.2 |
| **Database** | PostgreSQL (Supabase) |
| **Frontend** | HTML5, Tailwind CSS |
| **Server** | XAMPP (Apache) |
| **Authentication** | Session-based with CSRF Protection |

---

## 📋 Database Structure

```
users          → System users
clients        → Clients (buyers/sellers)
properties     → Properties
deal_stages    → Pipeline stages
deals          → Business opportunities
tasks          → Tasks
activities     → Activity history
media          → Property attachments
```

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- XAMPP or similar (Apache)
- PHP extensions: pdo_pgsql, pgsql
- Supabase account (PostgreSQL)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/elipereiraoficial/Dev.git
   ```

2. **Configure the database**
   - Create an account at [Supabase](https://supabase.com)
   - Create a new project
   - Run the `setup.php` script to create tables

3. **Configure config.php**
   ```php
   define('DB_HOST', 'your-supabase-host');
   define('DB_PORT', '5432');
   define('DB_NAME', 'postgres');
   define('DB_USER', 'postgres');
   define('DB_PASS', 'your-password');
   ```

4. **Start the server**
   - Start Apache in XAMPP
   - Access: `http://localhost/luxury-crm/`

### Default Credentials
- **Email**: admin@luxury.pt
- **Password**: admin123

---

## 🎨 Design & UI/UX

The system was developed with a premium and elegant design:

- **Color Palette**: Golden (#d4af37) as the main color, with gray and blue tones
- **Typography**: Modern and readable fonts
- **Components**: Cards with hover effects, smooth transitions
- **Responsiveness**: Fully responsive for desktop and mobile

---

## 🔒 Security

- ✅ CSRF protection on all forms
- ✅ Passwords hashed with `password_hash()`
- ✅ Secure sessions with custom name
- ✅ Input sanitization against XSS
- ✅ Prepared statements against SQL Injection

---

## 📱 Screenshots

The system includes:
- Dashboard with interactive KPIs
- Visual Kanban Pipeline
- Monthly Calendar
- Filterable Lists
- Complete Edit Forms

---

## 📄 License

This project is licensed under the MIT license.

---

## 🏆 Project Highlights

- 🎯 **Complete CRM Solution** - All-in-one system for luxury real estate management
- 💎 **Premium Design** - Elegant UI specifically designed for high-end clients
- ⚡ **Modern Tech Stack** - Built with PHP 8.2 and modern best practices
- 🔄 **Real-time Updates** - Dashboard and pipeline update automatically
- 🛡️ **Enterprise Security** - Production-ready security features
- 📱 **Fully Responsive** - Works on desktop, tablet, and mobile
- 🌐 **Multi-language Ready** - Portuguese interface, easy to translate

---

## 💡 Future Enhancements

Potential features for future versions:
- Email notifications and reminders
- WhatsApp integration
- PDF report generation
- Property website export
- Client portal
- Commission tracking
- Multi-agent support with permissions

---

## 👨‍💻 Author

**Eli Pereira**
- GitHub: [@elipereiraoficial](https://github.com/elipereiraoficial)
- Email: contato@elipereira.com

---

## 🙏 Acknowledgments

Special thanks to OpenCode AI for assistance in developing this project.

---

<p align="center">
  <sub>Developed with ❤️ and ☕</sub>
</p>

---

<p align="center">
  <a href="https://github.com/elipereiraoficial/Dev">
    <img src="https://img.shields.io/github/stars/elipereiraoficial/Dev?style=social" alt="Stars">
  </a>
  <a href="https://github.com/elipereiraoficial/Dev">
    <img src="https://img.shields.io/github/forks/elipereiraoficial/Dev?style=social" alt="Forks">
  </a>
  <a href="https://github.com/elipereiraoficial/Dev">
    <img src="https://img.shields.io/github/watchers/elipereiraoficial/Dev?style=social" alt="Watchers">
  </a>
</p>