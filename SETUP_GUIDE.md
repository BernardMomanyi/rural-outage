# OutageSys MVP Setup Guide

## 🚀 Quick Start

### 1. **Database Setup**
1. Ensure you have MySQL/MariaDB running
2. Create a database named `outagesys`
3. Update `db.php` with your database credentials
4. Run the setup script: `http://localhost/rural%20outage/setup_database.php`

### 2. **Default Login Credentials**
- **Admin**: username: `admin`, password: `admin123`
- **Users**: Register new accounts (pending admin approval)

### 3. **Test the System**
1. Go to `http://localhost/rural%20outage/`
2. Click "Login" and use admin credentials
3. Test all features across different screen sizes

## 🔧 Configuration

### Database Configuration (`db.php`)
```php
<?php
$host = 'localhost';
$dbname = 'outagesys';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

## 📱 Responsive Design Features

### Mobile (< 600px)
- Collapsible sidebar navigation
- Touch-friendly buttons (48px minimum)
- Horizontal scrolling tables
- Optimized form inputs (prevents zoom on iOS)

### Tablet (600px - 900px)
- Adaptive grid layouts
- Medium-sized touch targets
- Responsive typography

### Desktop (> 900px)
- Full sidebar navigation
- Multi-column layouts
- Hover effects and animations

## 🔐 Security Features

### Authentication
- Password hashing with `password_hash()`
- Session-based authentication
- Role-based access control (admin, technician, user)

### Data Protection
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Input validation and sanitization

## 🎯 Core Features

### Admin Dashboard
- User management
- Ticket assignment
- Substation monitoring
- System analytics

### Technician Dashboard
- Assigned tickets
- Maintenance tasks
- Equipment logs
- Work schedule

### User Dashboard
- Report outages
- Track ticket status
- View notifications
- Profile management

## 🐛 Troubleshooting

### Login Issues
1. Check database connection in `db.php`
2. Verify tables exist (run setup script)
3. Clear browser cache
4. Check PHP error logs

### Mobile Display Issues
1. Ensure viewport meta tag is present
2. Test on actual devices, not just browser dev tools
3. Check CSS media queries

### Database Errors
1. Verify MySQL/MariaDB is running
2. Check database credentials
3. Ensure database `outagesys` exists
4. Run setup script to create tables

## 📊 Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'technician', 'user') DEFAULT 'user',
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Substations Table
```sql
CREATE TABLE substations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    county VARCHAR(50),
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'online',
    risk ENUM('low', 'medium', 'high') DEFAULT 'low',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8)
);
```

### User Outages Table (Tickets)
```sql
CREATE TABLE user_outages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Submitted', 'Assigned', 'InProgress', 'Resolved') DEFAULT 'Submitted',
    technician_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (technician_id) REFERENCES users(id)
);
```

## 🎨 UI/UX Improvements

### Responsive Design
- Mobile-first approach
- Touch-friendly interfaces
- Adaptive layouts
- Optimized typography

### Accessibility
- Semantic HTML structure
- ARIA labels and roles
- Keyboard navigation support
- High contrast ratios

### Performance
- Optimized CSS and JavaScript
- Efficient database queries
- Caching strategies
- Image optimization

## 🔄 Testing Checklist

### Functionality
- [ ] User registration works
- [ ] Login/logout functions properly
- [ ] Role-based access control
- [ ] Ticket creation and management
- [ ] Substation monitoring
- [ ] User management (admin)

### Responsive Design
- [ ] Mobile layout (320px+)
- [ ] Tablet layout (768px+)
- [ ] Desktop layout (1024px+)
- [ ] Touch interactions work
- [ ] Tables scroll horizontally on mobile

### Security
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] Session management
- [ ] Password hashing
- [ ] Input validation

## 🚀 Deployment

### Local Development
1. Use XAMPP/WAMP/MAMP
2. Place files in `htdocs` directory
3. Access via `http://localhost/rural%20outage/`

### Production Deployment
1. Upload files to web server
2. Configure database connection
3. Set proper file permissions
4. Enable HTTPS
5. Configure error logging

## 📞 Support

For issues or questions:
1. Check this setup guide
2. Review error logs
3. Test with default admin account
4. Verify database connectivity

---

**OutageSys MVP** - Rural Power Outage Management System
Developed by Bernard Momanyi, JKUAT 