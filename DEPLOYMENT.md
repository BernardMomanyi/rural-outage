# Deployment Guide - OutageSys

This guide provides step-by-step instructions for deploying OutageSys in different environments.

## 🚀 Quick Start (Local Development)

### Using XAMPP (Recommended for Windows)

1. **Install XAMPP**
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install with default settings

2. **Set up the project**
   ```bash
   # Navigate to XAMPP htdocs directory
   cd C:\xampp\htdocs\
   
   # Clone or copy the project
   git clone https://github.com/BernardMomanyi/outagesys.git
   cd outagesys
   ```

3. **Configure database**
   - Start XAMPP Control Panel
   - Start Apache and MySQL services
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create new database: `outagesys`
   - Import `schema.sql` file

4. **Configure application**
   ```bash
   # Copy database configuration
   cp db.php.example db.php
   # Edit db.php with your database credentials
   ```

5. **Access the application**
   - Open browser: http://localhost/outagesys
   - Register or use default admin account

## 🌐 Production Deployment

### Shared Hosting

1. **Upload files**
   - Upload all project files to your web hosting directory
   - Ensure `db.php` is configured with production database

2. **Database setup**
   - Create MySQL database on your hosting provider
   - Import `schema.sql` file
   - Update `db.php` with production credentials

3. **Configure web server**
   - Set proper file permissions (755 for directories, 644 for files)
   - Ensure PHP version 7.4+ is available

### VPS/Cloud Deployment

#### Using Apache

1. **Install LAMP stack**
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install apache2 mysql-server php php-mysql php-mbstring
   
   # CentOS/RHEL
   sudo yum install httpd mysql-server php php-mysql php-mbstring
   ```

2. **Configure Apache**
   ```bash
   # Enable mod_rewrite
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. **Set up virtual host**
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/outagesys
       
       <Directory /var/www/outagesys>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

#### Using Nginx

1. **Install LEMP stack**
   ```bash
   sudo apt update
   sudo apt install nginx mysql-server php-fpm php-mysql
   ```

2. **Configure Nginx**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       root /var/www/outagesys;
       index index.php index.html;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
       }
   }
   ```

## 🔧 Configuration

### Environment Variables

Create `.env` file for sensitive configuration:
```env
DB_HOST=localhost
DB_NAME=outagesys
DB_USER=your_username
DB_PASS=your_password
APP_ENV=production
APP_DEBUG=false
```

### Security Settings

1. **File permissions**
   ```bash
   # Set proper permissions
   find . -type d -exec chmod 755 {} \;
   find . -type f -exec chmod 644 {} \;
   chmod 600 db.php
   ```

2. **SSL/HTTPS**
   - Install SSL certificate
   - Configure redirect from HTTP to HTTPS
   - Update all internal links to use HTTPS

3. **Database security**
   - Use strong passwords
   - Limit database user permissions
   - Enable SSL for database connections

## 📊 Performance Optimization

### PHP Optimization

1. **Enable OPcache**
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.interned_strings_buffer=8
   opcache.max_accelerated_files=4000
   ```

2. **Configure PHP-FPM**
   ```ini
   pm = dynamic
   pm.max_children = 50
   pm.start_servers = 5
   pm.min_spare_servers = 5
   pm.max_spare_servers = 35
   ```

### Database Optimization

1. **MySQL configuration**
   ```ini
   innodb_buffer_pool_size = 1G
   query_cache_size = 64M
   max_connections = 200
   ```

2. **Index optimization**
   - Add indexes for frequently queried columns
   - Monitor slow query log
   - Optimize database queries

## 🔍 Monitoring and Maintenance

### Log Monitoring

1. **Enable error logging**
   ```php
   error_reporting(E_ALL);
   ini_set('log_errors', 1);
   ini_set('error_log', '/var/log/outagesys/error.log');
   ```

2. **Application logging**
   - Monitor user activities
   - Track system performance
   - Log security events

### Backup Strategy

1. **Database backups**
   ```bash
   # Daily backup script
   mysqldump -u username -p outagesys > backup_$(date +%Y%m%d).sql
   ```

2. **File backups**
   ```bash
   # Backup application files
   tar -czf outagesys_backup_$(date +%Y%m%d).tar.gz /var/www/outagesys/
   ```

## 🚨 Troubleshooting

### Common Issues

1. **Database connection errors**
   - Check database credentials
   - Verify database server is running
   - Test connection manually

2. **Permission errors**
   - Check file and directory permissions
   - Verify web server user has access
   - Review SELinux settings (if applicable)

3. **Performance issues**
   - Monitor server resources
   - Check database query performance
   - Review PHP configuration

### Support

For deployment issues:
- Check the [GitHub issues](https://github.com/BernardMomanyi/outagesys/issues)
- Review server error logs
- Contact system administrator

## 📈 Scaling

### Horizontal Scaling

1. **Load balancing**
   - Use multiple web servers
   - Configure load balancer
   - Implement session sharing

2. **Database scaling**
   - Master-slave replication
   - Database sharding
   - Connection pooling

### Vertical Scaling

1. **Server resources**
   - Increase RAM and CPU
   - Use SSD storage
   - Optimize server configuration

## 🔐 Security Checklist

- [ ] SSL certificate installed
- [ ] Database credentials secured
- [ ] File permissions set correctly
- [ ] Error reporting disabled in production
- [ ] Regular security updates applied
- [ ] Backup strategy implemented
- [ ] Monitoring and logging enabled
- [ ] Firewall configured
- [ ] Intrusion detection system active

---

For additional support, refer to the [main documentation](README.md) or create an issue on GitHub. 