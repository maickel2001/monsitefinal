# 🚀 SMM Platform - Premium Social Media Marketing Platform

A complete, secure, and responsive PHP/MySQL web application for Social Media Marketing (SMM) services with a modern Apple-style aesthetic.

## ✨ Features

### 🎯 Core Functionality
- **User Registration & Authentication** - Secure user accounts with bcrypt password hashing
- **SMM Services Management** - Categories and services with automatic icon generation
- **Order Management** - Complete order lifecycle with status tracking
- **Payment Proof System** - Secure file uploads for payment verification
- **Client Reviews** - Admin-moderated review system
- **Support Tickets** - Customer support with priority levels
- **Admin Dashboard** - Comprehensive admin panel with analytics

### 🎨 Design & UX
- **Mobile-First Design** - Responsive design optimized for all devices
- **Apple-Style Aesthetics** - Clean, modern interface with smooth animations
- **System Fonts** - Native system typography for optimal performance
- **Smooth Transitions** - Fluid animations and micro-interactions
- **Dark/Light Theme Support** - Customizable color schemes

### 🔒 Security Features
- **CSRF Protection** - Anti-CSRF tokens on all forms
- **SQL Injection Prevention** - PDO with prepared statements
- **XSS Protection** - Input validation and sanitization
- **Secure Sessions** - Configurable session management
- **File Upload Security** - Restricted file types and sizes
- **Security Headers** - Comprehensive HTTP security headers

### 📊 Analytics & Reporting
- **Chart.js Integration** - Beautiful data visualizations
- **Real-time Statistics** - Live dashboard updates
- **Order Analytics** - Revenue and order tracking
- **User Analytics** - User behavior insights
- **Performance Metrics** - System performance monitoring

## 🛠️ Technical Stack

- **Backend**: PHP 7.4+ with PDO MySQL
- **Database**: MySQL 5.7+ with InnoDB engine
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Charts**: Chart.js for data visualization
- **Icons**: Font Awesome 6.4.0
- **Security**: bcrypt, CSRF tokens, prepared statements
- **Hosting**: Optimized for Hostinger and similar hosting providers

## 📁 Project Structure

```
smm-platform/
├── admin/                 # Admin panel files
│   ├── index.php         # Admin dashboard
│   ├── login.php         # Admin authentication
│   └── logout.php        # Admin logout
├── config/               # Configuration files
│   └── config.php        # Main configuration
├── includes/             # PHP classes and includes
│   ├── Database.php      # Database connection class
│   ├── User.php          # User management class
│   ├── Admin.php         # Admin management class
│   ├── Services.php      # Services management class
│   ├── Orders.php        # Order management class
│   ├── Reviews.php       # Reviews management class
│   └── Tickets.php       # Support tickets class
├── uploads/              # File uploads directory
│   └── proofs/           # Payment proof uploads
├── logs/                 # Application logs
├── index.php             # Public landing page
├── register.php          # User registration
├── login.php             # User login
├── logout.php            # User logout
├── dashboard.php         # User dashboard
├── maintenance.php       # Maintenance mode page
├── .htaccess             # Apache configuration
└── README.md             # This file
```

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (Apache)

### Step 1: Database Setup
1. Create a new MySQL database
2. Import the `database.sql` file
3. Update database credentials in `config/config.php`

### Step 2: File Upload
1. Upload all files to your web server
2. Set proper permissions:
   ```bash
   chmod 755 uploads/
   chmod 755 logs/
   chmod 644 .htaccess
   ```

### Step 3: Configuration
1. Update `config/config.php` with your settings:
   - Database credentials
   - Site URL
   - Email settings
   - Currency (FCFA by default)

### Step 4: Admin Access
- **Default Admin**: admin@smmplatform.com
- **Default Password**: admin123
- **⚠️ Important**: Change these credentials immediately after installation

## 💰 Currency Configuration

The platform is configured to use **FCFA (CFA Franc)** as the default currency:

- **Symbol**: ₣
- **Position**: After amount (e.g., "1 000 FCFA")
- **Format**: Thousands separated by spaces
- **Configuration**: Easily changeable in `config/config.php`

## 🔧 Configuration Options

### Database Settings
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'smm_platform');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Currency Settings
```php
define('CURRENCY', 'FCFA');
define('CURRENCY_SYMBOL', '₣');
define('CURRENCY_POSITION', 'after');
```

### Security Settings
```php
define('SESSION_LIFETIME', 3600);        // Session timeout (seconds)
define('PASSWORD_COST', 12);             // bcrypt cost factor
define('MAINTENANCE_MODE', false);       // Maintenance mode toggle
```

## 📱 Mobile-First Design

The platform is built with mobile-first principles:

- **Responsive Grid System** - CSS Grid and Flexbox
- **Touch-Friendly Interface** - Optimized for mobile devices
- **Progressive Enhancement** - Core functionality works everywhere
- **Performance Optimized** - Fast loading on all devices

## 🎨 Customization

### Theme Colors
Edit the theme colors in the database or modify CSS variables:

```css
:root {
    --primary-color: #007AFF;
    --secondary-color: #5856D6;
    --success-color: #34C759;
    --warning-color: #FF9500;
    --danger-color: #FF3B30;
}
```

### Adding New Services
1. Access admin panel
2. Navigate to Services tab
3. Add new category or service
4. Set pricing in FCFA
5. Configure availability and limits

## 🔒 Security Best Practices

### Password Security
- bcrypt hashing with cost factor 12
- Minimum 8 character requirement
- Password strength validation

### Session Security
- Secure session configuration
- CSRF token protection
- Automatic logout on inactivity

### File Upload Security
- Restricted file types
- Size limitations
- Secure upload directory

## 📊 Admin Features

### Dashboard Overview
- Real-time statistics
- Chart visualizations
- Quick action buttons
- Recent activity feed

### Management Tools
- **Orders**: Status updates, notes, cancellation
- **Services**: CRUD operations, pricing, availability
- **Users**: Account management, status control
- **Reviews**: Moderation, approval, editing
- **Support**: Ticket management, responses

## 🚨 Maintenance Mode

Enable maintenance mode by setting `MAINTENANCE_MODE = true` in config:

- Custom maintenance page
- IP whitelist support
- Auto-refresh functionality
- Contact information display

## 📈 Performance Optimization

### Caching
- Browser caching headers
- Static asset optimization
- Database query optimization

### Compression
- Gzip compression enabled
- Minified CSS/JS (when applicable)
- Optimized images

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials
   - Verify MySQL service is running
   - Check database permissions

2. **Upload Errors**
   - Verify upload directory permissions
   - Check file size limits
   - Validate file types

3. **Session Issues**
   - Check PHP session configuration
   - Verify session directory permissions
   - Clear browser cookies

### Logs
- Application logs: `logs/` directory
- Error logs: Check web server error logs
- Database logs: MySQL error log

## 🔄 Updates & Maintenance

### Regular Maintenance
- Monitor error logs
- Update PHP and MySQL versions
- Backup database regularly
- Review security settings

### Backup Strategy
- Database: Daily automated backups
- Files: Weekly full backups
- Configuration: Version control

## 📞 Support

### Technical Support
- **Email**: support@smmplatform.com
- **Documentation**: This README file
- **Issues**: Check logs and error messages

### Community
- Share feedback and suggestions
- Report bugs and issues
- Contribute to improvements

## 📄 License

This project is proprietary software. All rights reserved.

## 🙏 Acknowledgments

- Font Awesome for icons
- Chart.js for data visualization
- PHP community for best practices
- Modern web standards for responsive design

---

**Built with ❤️ for the SMM community**

*Last updated: December 2024*