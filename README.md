# OutageSys - Rural Power Outage Management System

A smart, web-based system designed to predict, manage, and reduce power outages in rural Kenya. By leveraging historical data, analytics, and real-time reporting, OutageSys aims to improve electricity reliability and empower communities with actionable insights.

## 🌟 Features

### Core Functionality
- **Predictive Analytics**: Advanced algorithms to predict potential power outages
- **Real-time Monitoring**: Live tracking of substation status and performance
- **Role-based Access Control**: Secure system with different user roles (Admin, Technician, User)
- **Data Management**: Easy upload and management of outage data
- **Reporting System**: Comprehensive reports and analytics dashboard
- **Interactive Maps**: Visual representation of substations and outage locations

### User Roles
- **Administrators**: Full system access, user management, and analytics
- **Technicians**: Field work management, ticket assignments, and maintenance tracking
- **Users**: Report outages, view status, and access basic information

## 🚀 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Custom responsive design
- **Charts**: Chart.js for data visualization
- **Maps**: Interactive mapping for substation locations

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/BernardMomanyi/outagesys.git
   cd outagesys
   ```

2. **Set up the database**
   - Create a new MySQL database
   - Import the `schema.sql` file to create the required tables
   - Update database credentials in `db.php`

3. **Configure the application**
   - Copy `db.php.example` to `db.php` (if available)
   - Update database connection settings in `db.php`:
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'outagesys';
   $username = 'your_username';
   $password = 'your_password';
   ?>
   ```

4. **Set up the web server**
   - Place the project in your web server's document root
   - Ensure PHP has write permissions for uploads and logs
   - Configure your web server to serve the application

5. **Seed initial data (optional)**
   ```bash
   php seed_substations.php
   ```

6. **Generate admin user**
   ```bash
   php generate_admin_hash.php
   ```

## 🎯 Usage

### Getting Started
1. Access the application through your web browser
2. Register a new account or use the default admin credentials
3. Navigate through the dashboard to explore features

### Key Features Usage

#### For Administrators
- **Dashboard**: View system overview and key metrics
- **User Management**: Add, edit, and manage user accounts
- **Substation Management**: Configure and monitor substations
- **Reports**: Generate and export comprehensive reports
- **Analytics**: View predictive analytics and trends

#### For Technicians
- **Ticket Management**: View and update assigned tickets
- **Field Work**: Track maintenance activities and repairs
- **Reports**: Submit work reports and updates

#### For Users
- **Outage Reporting**: Report new outages with location details
- **Status Tracking**: Monitor outage status and updates
- **Notifications**: Receive updates on outage resolution

## 📁 Project Structure

```
outagesys/
├── api/                    # API endpoints
│   ├── assignments.php
│   ├── dashboard_stats.php
│   ├── maintenance.php
│   ├── notifications.php
│   ├── reports.php
│   ├── substations.php
│   ├── tech_tickets.php
│   ├── tickets.php
│   ├── uploads.php
│   └── users.php
├── css/                    # Stylesheets
│   └── style.css
├── js/                     # JavaScript files
│   └── main.js
├── admin_dashboard.php     # Admin dashboard
├── user_dashboard.php      # User dashboard
├── technician_dashboard.php # Technician dashboard
├── login.php              # Authentication
├── register.php           # User registration
├── schema.sql             # Database schema
├── db.php                 # Database configuration
└── README.md              # This file
```

## 🔧 Configuration

### Database Configuration
Update `db.php` with your database credentials:
```php
<?php
$host = 'localhost';
$dbname = 'outagesys';
$username = 'your_username';
$password = 'your_password';
?>
```

### Security Settings
- Update CSRF tokens in `csrf.php`
- Configure session settings
- Set up proper file permissions

## 📊 Database Schema

The system uses the following main tables:
- `users`: User accounts and authentication
- `substations`: Power substation information
- `outages`: Outage records and status
- `tickets`: Maintenance and repair tickets
- `assignments`: Technician work assignments
- `reports`: System reports and analytics

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- **Developer**: Bernard Momanyi
- **Institution**: Jomo Kenyatta University of Agriculture and Technology
- **Project**: Rural Power Outage Management System

## 📞 Support

For support and questions:
- Create an issue in the GitHub repository
- Contact: benmomanyi969@gmail.com

## 🔄 Version History

- **v1.0.0** - Initial release with core functionality
- **v1.1.0** - Added predictive analytics
- **v1.2.0** - Enhanced reporting and dashboard features

---

**OutageSys** - Empowering rural communities with reliable electricity management. 
