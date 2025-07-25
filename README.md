# Rural Outage Management System

A modern, full-featured web platform for managing electrical outages, meter billing (prepaid & postpaid), user notifications, and utility operations in rural areas.

---

## 🚀 Features

- **User Authentication & Roles**: Secure login, registration, and admin/user roles
- **Meter Management**: Prepaid (token purchase, recharge) & postpaid (bill calculation, payment)
- **KPLC-Compliant Billing**: Accurate tariff bands, surcharges, and VAT
- **Ticketing System**: Outage reporting, assignment, and technician workflow
- **In-App Notifications**: Real-time updates for users and admins
- **Admin Dashboard**: System stats, meter/billing/ticket management
- **Modern UI**: Responsive, card-based layouts, modals, and icons
- **Reporting & Analytics**: Usage, billing, and outage statistics
- **Email Integration**: SendGrid support for notifications (optional)

---

## 📦 Project Structure

- `index.php` — Main landing page
- `login.php`, `register.php` — Authentication
- `admin_dashboard.php`, `user_dashboard.php` — Dashboards
- `meter_billing.php` — User meter management (prepaid/postpaid)
- `admin_meter_management.php` — Admin meter/token management
- `admin_billing.php` — Admin postpaid billing
- `api/` — Backend API endpoints
- `css/`, `js/` — Styles and scripts
- `uploads/` — User-uploaded files (ignored in Git)

---

## ⚡ Meter Management

### Prepaid
- Buy tokens (auto 20-digit generation)
- Recharge meters with tokens
- Admin can generate tokens for any user

### Postpaid
- Admin sets bill by kWh consumed (KPLC bands)
- User pays bill, balance updates in real time

---

## 🧮 Billing Logic

- **Tariff Bands:**
  - 0–30 kWh: 12.23 KES/kWh (DC0)
  - 31–100 kWh: 16.54 KES/kWh (DC1)
  - 100+ kWh: 19.08 KES/kWh (DC2)
- **Surcharges:** 30% of base
- **ERC Levy:** 0.08 KES/kWh
- **REP Levy:** 5% of base
- **VAT:** 16% of subtotal

---

## 🛠️ Setup & Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/rural-outage.git
   cd rural-outage
   ```
2. **Install dependencies:**
   - PHP 7.4+ with PDO, cURL, OpenSSL
   - MySQL 5.7+ or MariaDB
   - Composer (for SendGrid):
     ```bash
     composer install
     ```
3. **Configure database:**
   - Copy `db.php.example` to `db.php` and set your DB credentials
   - Import `schema.sql` into your database
4. **Set up environment:**
   - (Optional) Add `.env` for API keys (SendGrid, etc.)
   - Configure web server (Apache/Nginx) to point to project root
5. **Run the app:**
   - Access via `http://localhost/` or your configured domain

---

## 🔒 Security
- Sensitive files (db.php, .env, uploads/) are gitignored
- Use strong DB and API credentials
- Never commit secrets to the repository

---

## 🤝 Contributing

1. Fork the repo and create your branch:
   ```bash
   git checkout -b feature/your-feature
   ```
2. Commit your changes and push:
   ```bash
   git commit -am 'Add new feature'
   git push origin feature/your-feature
   ```
3. Open a Pull Request describing your changes

---

## 📝 License

This project is licensed under the MIT License. See `LICENSE` for details.

---

## 📚 Documentation & Support

- See `SYSTEM_STATUS.md` for a full system overview and changelog
- For help, open an issue or contact the maintainer

---

**Built with ❤️ for rural communities.** 
