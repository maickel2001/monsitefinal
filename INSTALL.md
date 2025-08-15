# 🚀 SMM Platform Installation Guide

Complete step-by-step installation instructions for the SMM Platform.

## 📋 Prerequisites

Before installing, ensure you have:

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 5.7 or higher (8.0+ recommended)
- **PHP Extensions**: PDO, PDO_MySQL, mbstring, json, fileinfo
- **mod_rewrite**: Enabled (Apache)
- **SSL Certificate**: Recommended for production

## 🔧 Server Requirements

### PHP Extensions
```bash
# Required extensions
php-pdo
php-pdo-mysql
php-mbstring
php-json
php-fileinfo
php-curl
php-gd
php-zip

# Optional but recommended
php-opcache
php-redis
php-memcached
```

### PHP Configuration
```ini
; php.ini settings
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_vars = 3000
memory_limit = 256M
display_errors = Off
log_errors = On
error_log = /path/to/error.log
```

## 📥 Installation Steps

### Step 1: Download & Extract

1. **Download** the SMM Platform files
2. **Extract** to your web server directory
3. **Set permissions**:
   ```bash
   chmod 755 uploads/
   chmod 755 logs/
   chmod 644 .htaccess
   chmod 644 config/config.php
   ```

### Step 2: Database Setup

1. **Create Database**:
   ```sql
   CREATE DATABASE smm_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'smm_user'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON smm_platform.* TO 'smm_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Import Schema**:
   ```bash
   mysql -u smm_user -p smm_platform < database.sql
   ```

3. **Verify Tables**:
   ```sql
   USE smm_platform;
   SHOW TABLES;
   ```

### Step 3: Configuration

1. **Edit** `config/config.php`:
   ```php
   // Database Configuration
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'smm_platform');
   define('DB_USER', 'smm_user');
   define('DB_PASS', 'your_secure_password');
   
   // Site Configuration
   define('APP_URL', 'https://yourdomain.com');
   define('APP_NAME', 'Your SMM Platform');
   
   // Currency (FCFA by default)
   define('CURRENCY', 'FCFA');
   define('CURRENCY_SYMBOL', '₣');
   ```

2. **Email Settings**:
   ```php
   // SMTP Configuration
   define('SMTP_HOST', 'your-smtp-server.com');
   define('SMTP_PORT', 587);
   define('SMTP_USER', 'your-email@domain.com');
   define('SMTP_PASS', 'your-email-password');
   define('SMTP_SECURE', 'tls');
   ```

### Step 4: Web Server Configuration

#### Apache Configuration

1. **Enable Modules**:
   ```bash
   sudo a2enmod rewrite
   sudo a2enmod headers
   sudo a2enmod ssl
   ```

2. **Virtual Host** (example):
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       ServerAlias www.yourdomain.com
       DocumentRoot /var/www/smm-platform
       
       <Directory /var/www/smm-platform>
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/smm-error.log
       CustomLog ${APACHE_LOG_DIR}/smm-access.log combined
   </VirtualHost>
   ```

3. **Restart Apache**:
   ```bash
   sudo systemctl restart apache2
   ```

#### Nginx Configuration

1. **Server Block** (example):
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com www.yourdomain.com;
       root /var/www/smm-platform;
       index index.php index.html;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }
       
       location ~ /\.ht {
           deny all;
       }
   }
   ```

2. **Restart Nginx**:
   ```bash
   sudo systemctl restart nginx
   ```

### Step 5: SSL Certificate (Recommended)

1. **Install Certbot**:
   ```bash
   sudo apt install certbot python3-certbot-apache
   # or for Nginx
   sudo apt install certbot python3-certbot-nginx
   ```

2. **Obtain Certificate**:
   ```bash
   sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
   # or for Nginx
   sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
   ```

3. **Auto-renewal**:
   ```bash
   sudo crontab -e
   # Add this line:
   0 12 * * * /usr/bin/certbot renew --quiet
   ```

## 🔐 Initial Setup

### Step 1: Access Admin Panel

1. **Navigate** to: `https://yourdomain.com/admin/`
2. **Login** with default credentials:
   - **Email**: admin@smmplatform.com
   - **Password**: admin123

### Step 2: Change Default Password

1. **Go to** Admin Dashboard
2. **Navigate** to Profile/Settings
3. **Change** admin password immediately

### Step 3: Configure Site Settings

1. **Site Information**:
   - Site name and description
   - Contact information
   - Social media links

2. **Services Setup**:
   - Add SMM categories
   - Configure services with FCFA pricing
   - Set availability and limits

3. **Payment Settings**:
   - Configure payment methods
   - Set up payment gateways
   - Test payment flow

## 🧪 Testing Installation

### 1. Frontend Test
- Visit homepage
- Test responsive design
- Verify navigation

### 2. User Registration Test
- Create test user account
- Verify email verification
- Test login/logout

### 3. Admin Panel Test
- Access admin dashboard
- Test service management
- Verify user management

### 4. Order Flow Test
- Place test order
- Test payment proof upload
- Verify order status updates

## 🔧 Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check MySQL service
sudo systemctl status mysql

# Verify credentials
mysql -u smm_user -p -h localhost

# Check permissions
SHOW GRANTS FOR 'smm_user'@'localhost';
```

#### 2. File Upload Issues
```bash
# Check directory permissions
ls -la uploads/
chmod 755 uploads/
chown www-data:www-data uploads/

# Check PHP settings
php -i | grep upload
```

#### 3. 500 Internal Server Error
```bash
# Check error logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# Check PHP error log
tail -f /var/log/php7.4-fpm.log
```

#### 4. Page Not Found (404)
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Check .htaccess
cat .htaccess

# Verify AllowOverride
grep -r "AllowOverride" /etc/apache2/
```

### Performance Optimization

#### 1. Enable OPcache
```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

#### 2. Database Optimization
```sql
-- Add indexes for better performance
ALTER TABLE orders ADD INDEX idx_user_status (user_id, status);
ALTER TABLE orders ADD INDEX idx_created_at (created_at);
ALTER TABLE users ADD INDEX idx_email (email);
```

#### 3. Caching
```bash
# Install Redis
sudo apt install redis-server

# Configure PHP Redis extension
sudo apt install php-redis
```

## 📊 Monitoring & Maintenance

### 1. Log Monitoring
```bash
# Application logs
tail -f logs/error.log

# System logs
tail -f /var/log/syslog

# Database logs
tail -f /var/log/mysql/error.log
```

### 2. Performance Monitoring
```bash
# Check PHP-FPM status
php-fpm7.4 -t

# Monitor MySQL
mysqladmin -u root -p status

# Check disk usage
df -h
du -sh /var/www/smm-platform/*
```

### 3. Backup Strategy
```bash
# Database backup
mysqldump -u smm_user -p smm_platform > backup_$(date +%Y%m%d).sql

# File backup
tar -czf smm_platform_$(date +%Y%m%d).tar.gz /var/www/smm-platform/

# Automated backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u smm_user -p'password' smm_platform > /backups/db_$DATE.sql
tar -czf /backups/files_$DATE.tar.gz /var/www/smm-platform/
find /backups/ -mtime +7 -delete
```

## 🔒 Security Checklist

- [ ] Changed default admin password
- [ ] Enabled HTTPS/SSL
- [ ] Configured firewall rules
- [ ] Set secure file permissions
- [ ] Enabled error logging
- [ ] Configured backup system
- [ ] Set up monitoring alerts
- [ ] Regular security updates

## 📞 Support

### Technical Support
- **Email**: support@smmplatform.com
- **Documentation**: This guide and README.md
- **Issues**: Check logs and error messages

### Community
- Share feedback and suggestions
- Report bugs and issues
- Contribute to improvements

---

**Installation completed successfully! 🎉**

Your SMM Platform is now ready to use. Remember to:
1. Change default admin credentials
2. Configure your services and pricing
3. Test all functionality thoroughly
4. Set up regular backups
5. Monitor performance and security

*Last updated: December 2024*