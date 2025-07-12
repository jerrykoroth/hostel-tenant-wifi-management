# 🏠 Tenant Management Mobile Application

A comprehensive, modern, and mobile-responsive tenant management system built with PHP, MySQL, and Bootstrap. This application provides complete hostel management features including tenant registration, room & bed management, payment tracking, and detailed reporting.

## ✨ Features

### 🔐 User Authentication
- **Registration with email** (no verification required)
- **Secure login system** with password hashing
- **Role-based access** (Admin, Staff, Owner)
- **Session management** with activity logging

### � Hostel Management
- **Add/Edit/Delete Hostels** with detailed information
- **Multi-hostel support** for organizations
- **Hostel-specific user access** control

### 🛏️ Room & Bed Management
- **Dynamic room creation** with customizable capacity
- **Automatic bed generation** (A, B, C, etc.)
- **Bed status tracking** (Available, Occupied, Maintenance)
- **Visual bed layout** with occupancy indicators
- **Room editing** with automatic bed adjustment

### 👥 Tenant Management
- **Complete tenant registration** with personal details
- **Check-in/Check-out** management
- **Tenant history tracking**
- **Emergency contact information**
- **ID proof management**

### 💳 Payment System
- **Multiple payment methods** (Cash, Card, UPI, Bank Transfer, Cheque)
- **Payment type categorization** (Rent, Security Deposit, Maintenance, etc.)
- **Automatic receipt generation**
- **Payment history tracking**
- **Monthly payment summaries**

### 📊 Dashboard & Reports
- **Real-time statistics** dashboard
- **Occupancy rate tracking**
- **Monthly payment summaries**
- **Date-range reports**
- **Detailed analytics** with visual charts

### 📱 Mobile-Responsive Design
- **Mobile-first approach** with touch-friendly interface
- **Progressive Web App** capabilities
- **Offline-ready** design patterns
- **Modern UI/UX** with smooth animations

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5.1, JavaScript ES6+
- **Icons**: Font Awesome 6.0
- **Security**: Prepared statements, input sanitization, CSRF protection

## 📋 Database Schema

```sql
🧑 users         (id, name, email, password_hash, role, hostel_id)
🏨 hostels       (id, name, address, phone, email, description)
🛏️ rooms         (id, hostel_id, room_number, capacity, monthly_rent, description)
🛌 beds          (id, room_id, bed_number, status)
🧍 tenants       (id, name, phone, address, bed_id, checkin_date, checkout_date, monthly_rent, security_deposit, status)
💳 payments      (id, tenant_id, amount, date, method, payment_type, receipt_number, notes)
📝 checkin_history (id, tenant_id, bed_id, checkin_date, checkout_date, rent_amount, security_deposit)
📊 activity_log  (id, user_id, action, description, ip_address, created_at)
```

## � Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser with JavaScript enabled

### Step 1: Download/Clone the Application
```bash
git clone https://github.com/yourusername/tenant-management-app.git
cd tenant-management-app
```

### Step 2: Database Setup
1. Create a new MySQL database:
```sql
CREATE DATABASE tenant_management;
```

2. Import the database schema:
```bash
mysql -u your_username -p tenant_management < database/tenant_management.sql
```

### Step 3: Configure Database Connection
Edit `includes/config.php` and update the database credentials:
```php
$host = "localhost";
$dbname = "tenant_management";
$user = "your_username";
$pass = "your_password";
```

### Step 4: Set Up Web Server
1. **Apache**: Place the application in your web root directory (e.g., `/var/www/html/`)
2. **Nginx**: Configure a virtual host pointing to the application directory
3. **Local Development**: Use PHP's built-in server:
```bash
php -S localhost:8000
```

### Step 5: Access the Application
1. Open your web browser and navigate to your application URL
2. Register a new user account or use the default admin credentials:
   - **Email**: admin@tenantmanagement.com
   - **Password**: admin123

## 📱 Usage Guide

### Getting Started
1. **Register/Login**: Create an account or log in with existing credentials
2. **Add Hostel**: Create your first hostel/organization
3. **Create Rooms**: Add rooms with specified bed capacity
4. **Register Tenants**: Add tenants and assign them to available beds
5. **Track Payments**: Record payments and generate receipts
6. **Generate Reports**: View analytics and export reports

### Main Features

#### Dashboard
- View real-time statistics
- Quick action buttons for common tasks
- Monthly payment summaries
- Occupancy rates

#### Tenant Management
- Add new tenants with complete details
- Check-in/Check-out functionality
- View tenant history
- Emergency contact management

#### Room & Bed Management
- Add rooms with custom capacity
- Visual bed layout management
- Bed status tracking
- Occupancy reporting

#### Payment Tracking
- Record various payment types
- Multiple payment methods
- Automatic receipt generation
- Payment history and reports

#### Reports
- Date-range payment reports
- Occupancy analytics
- Revenue tracking
- Export capabilities

## 🔧 Configuration Options

### Security Settings
- Update `includes/config.php` for security headers
- Configure file upload limits
- Set session timeout values

### Customization
- Modify CSS variables in `mobile_app.php` for theme customization
- Update logo and branding elements
- Configure email settings for notifications

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Verify database exists and user has proper permissions

2. **Login Issues**
   - Clear browser cache and cookies
   - Check if JavaScript is enabled
   - Verify user credentials

3. **Payment/Tenant Registration Errors**
   - Ensure all required fields are filled
   - Check for duplicate entries
   - Verify bed availability

4. **Mobile Display Issues**
   - Ensure viewport meta tag is present
   - Check CSS media queries
   - Test on different devices/browsers

## 🛡️ Security Features

- **SQL Injection Protection**: All queries use prepared statements
- **XSS Prevention**: Input sanitization and output encoding
- **CSRF Protection**: Token-based form validation
- **Session Security**: Secure session handling
- **Password Hashing**: Bcrypt password encryption
- **Activity Logging**: Complete audit trail

## 📈 Performance Optimization

- **Database Indexing**: Optimized queries with proper indexes
- **Caching**: Browser caching for static resources
- **Compression**: Gzip compression for faster loading
- **Lazy Loading**: Dynamic content loading
- **Responsive Images**: Optimized images for different screen sizes

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## � Future Enhancements

- [ ] Email notifications for payment reminders
- [ ] SMS integration for alerts
- [ ] PDF report generation
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] Mobile app (React Native/Flutter)
- [ ] API for third-party integrations
- [ ] Automated backup system
- [ ] Advanced search and filtering
- [ ] Bulk operations for tenants/payments

## 📞 Support

For support, email jerrykoroth@gmail.com or create an issue in the GitHub repository.

## 🙏 Acknowledgments

- Bootstrap team for the excellent CSS framework
- Font Awesome for the beautiful icons
- PHP community for comprehensive documentation
- MySQL team for the robust database system

---

**Made with ❤️ for hostel management**
