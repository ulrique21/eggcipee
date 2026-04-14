# Recipe App - Complete Web Application

A comprehensive PHP web application with PHPMailer and Dompdf integration, featuring user authentication, email functionality, and PDF generation.

## Features Implemented

### ✅ PHPMailer Integration
- **Registration Email Confirmation**: Users receive verification emails upon registration
- **Contact Form**: Contact messages are sent via email with confirmation to users
- **Password Reset**: Secure password reset functionality with email links
- **SMTP Configuration**: Uses Gmail SMTP with app passwords for secure email delivery

### ✅ Dompdf Integration
- **User Report Generation**: Generate comprehensive PDF reports with user information
- **Professional PDF Layout**: Styled PDFs with tables, headers, and proper formatting
- **Dynamic Content**: PDFs include user data, contact message history, and statistics

### ✅ Web Pages (8+ Pages)
1. **Home Page** (`index.php`) - Recipe showcase with navigation
2. **Login Page** (`login.php`) - User authentication with validation
3. **Registration Page** (`register.php`) - User registration with email verification
4. **Dashboard** (`dashboard.php`) - User dashboard with statistics and quick actions
5. **Contact Form** (`contact.php`) - Contact form with PHPMailer integration
6. **Password Reset** (`forgot-password.php`, `reset-password.php`) - Secure password reset
7. **Email Verification** (`verify.php`) - Email verification handler
8. **Report Generation** (`report.php`) - PDF report generation with Dompdf
9. **Admin Panel** (`admin.php`) - Admin interface for user management

## Installation & Setup

### 1. Install Dependencies
```bash
cd FrontEnd
composer install
```

### 2. Database Setup
1. Create a MySQL database named `recipe_app`
2. Update database credentials in `config.php`
3. The database tables will be created automatically when you first run the application

### 3. Email Configuration
Update the email settings in `config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'your-email@gmail.com');
define('FROM_NAME', 'Recipe App');
```

**Gmail App Password Setup:**
1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password: Google Account → Security → App passwords
3. Use the generated app password in the configuration

### 4. Admin Access
To access the admin panel, create a user with username `admin` or `administrator` through the registration process.

## File Structure

```
FrontEnd/
├── composer.json              # Composer dependencies
├── config.php                 # Database and email configuration
├── db.php                     # Database initialization
├── styles.css                 # Comprehensive CSS styling
├── README.md                  # This file
├── index.php                  # Home page with recipe showcase
├── login.php                  # User login with validation
├── register.php               # Registration with email verification
├── verify.php                 # Email verification handler
├── forgot-password.php        # Password reset request
├── reset-password.php         # Password reset form
├── dashboard.php              # User dashboard
├── contact.php                # Contact form with PHPMailer
├── report.php                 # PDF report generation
├── admin.php                  # Admin panel
├── logout.php                 # Logout functionality
└── vendor/                    # Composer dependencies
    ├── phpmailer/
    └── dompdf/
```

## Key Features Demonstrated

### Email Functionality (PHPMailer)
- ✅ Registration confirmation emails
- ✅ Contact form email notifications
- ✅ Password reset emails
- ✅ Email verification system
- ✅ SMTP configuration with Gmail

### PDF Generation (Dompdf)
- ✅ User report generation
- ✅ Professional PDF styling
- ✅ Dynamic content from database
- ✅ HTML to PDF conversion
- ✅ Browser streaming and download

### Security Features
- ✅ Password hashing with PHP's `password_hash()`
- ✅ Prepared statements to prevent SQL injection
- ✅ Email verification system
- ✅ Secure password reset with tokens
- ✅ Session management

### User Experience
- ✅ Responsive design
- ✅ Modern UI with CSS Grid and Flexbox
- ✅ Form validation and error handling
- ✅ Success/error message system
- ✅ Navigation consistency

## Usage Examples

### 1. User Registration Flow
1. Visit `/register.php`
2. Fill out registration form
3. Receive verification email
4. Click verification link
5. Account activated

### 2. Contact Form
1. Visit `/contact.php`
2. Fill out contact form
3. Email sent to admin
4. Confirmation email sent to user

### 3. PDF Report Generation
1. Login to dashboard
2. Visit `/report.php`
3. Click "Generate PDF Report"
4. PDF downloads with user information

### 4. Admin Panel
1. Create admin user account
2. Login with admin credentials
3. Visit `/admin.php`
4. View user statistics and management

## Screenshots Required

To demonstrate the application, capture screenshots of:
1. **Home Page** - Recipe showcase with navigation
2. **Registration Page** - Form with validation
3. **Login Page** - Authentication form
4. **Dashboard** - User dashboard with statistics
5. **Contact Form** - Contact form with PHPMailer integration
6. **Report Generation** - PDF generation page
7. **Admin Panel** - User management interface
8. **Email Verification** - Email confirmation page

## Technical Requirements Met

- ✅ **PHPMailer Integration**: Multiple functional pages with email sending
- ✅ **Dompdf Integration**: PDF generation with HTML to PDF conversion
- ✅ **5-8 Distinct Pages**: 9+ pages implemented with full functionality
- ✅ **SMTP Configuration**: Gmail SMTP with app password authentication
- ✅ **Database Integration**: MySQL with PDO and prepared statements
- ✅ **User Authentication**: Complete login/register system
- ✅ **Responsive Design**: Modern CSS with responsive layout
- ✅ **Security**: Password hashing, SQL injection prevention, email verification

## Testing the Application

1. **Registration Test**: Register a new user and check email verification
2. **Contact Form Test**: Send a contact message and verify emails are sent
3. **Password Reset Test**: Request password reset and follow email link
4. **PDF Generation Test**: Generate user report and verify PDF content
5. **Admin Panel Test**: Access admin features and view statistics

## Troubleshooting

### Email Issues
- Verify Gmail app password is correct
- Check SMTP settings in `config.php`
- Ensure 2FA is enabled on Gmail account

### Database Issues
- Verify database credentials in `config.php`
- Ensure MySQL server is running
- Check database name matches configuration

### PDF Generation Issues
- Verify Dompdf is installed via Composer
- Check file permissions for temporary files
- Ensure PHP has sufficient memory for PDF generation

This application demonstrates a complete web development project with modern PHP practices, third-party library integration, and professional user experience design.
