# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP-based reservation system for an event called "Cangrejos Albinos" hosted by CACT Lanzarote. The system allows users to book tickets for the event, with verification and confirmation via email, and includes an admin panel for managing reservations.

### Repository and Production Information

- Repository: https://github.com/NeoGeneration/cangrejos_albinos/
- Production URL: https://cangrejosalbinos.com/
- Hosting: Hostinger

## Development Setup

### System Requirements

- PHP 7.2 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- PHP extensions: mysqli, mail (or SMTP configuration)
- Web server: Apache or Nginx

### Local Development Setup

1. Configure the database connection:
   ```bash
   # Open the database config file in a web browser to configure
   http://localhost/cangrejos_albinos/update_db_password.php
   ```

2. Set up the database tables:
   ```bash
   # Create tables and initial admin user
   http://localhost/cangrejos_albinos/setup_database.php
   ```

3. For updates to the database schema:
   ```bash
   # Add email confirmation support
   http://localhost/cangrejos_albinos/update_database.php
   ```

4. Verify admin users:
   ```bash
   # Check admin users and create new ones if needed
   http://localhost/cangrejos_albinos/admin/check_users.php
   ```

### System Testing

Test the system using:
```bash
# Test the full system
http://localhost/cangrejos_albinos/test_system.php

# Test email templates
http://localhost/cangrejos_albinos/test_email_template.php

# Test basic email sending
http://localhost/cangrejos_albinos/test_email.php
```

## Core Architecture

### Main Components

1. **Reservation System**
   - `index.php`: Main landing page with embedded reservation form
   - `index_form.php`: Reservation form template
   - `process_reservation.php`: Processes form submissions
   - `confirm_reservation.php`: Handles email confirmation
   - `cancel_reservation.php`: Manages reservation cancellations

2. **Admin Panel**
   - `admin/index.php`: Admin login
   - `admin/dashboard.php`: Main admin dashboard for managing reservations
   - `admin/export.php`: Exports reservation data
   - `admin/reservas_stats.php`: Shows reservation statistics
   - `admin/logout.php`: Handles admin logout

3. **Email System**
   - `includes/email/email_template.php`: Email template system
   - `includes/mailer.php`: Email sending functionality
   - PHPMailer integration for reliable email delivery

4. **Database**
   - `includes/db_config.php`: Database connection configuration
   - Main tables: `reservations` and `admin_users`

### Database Schema

The database consists of two main tables:

1. **reservations**: Stores all reservations
   - Key fields: id, name, last_name, email, phone, dni, num_tickets, status, confirmation_code, confirmation_token

2. **admin_users**: Stores admin credentials
   - Key fields: id, username, password, email, last_login

### Authentication Flow

1. **User Reservation**:
   - User fills form → Process validation → Email verification sent → User confirms email → Reservation confirmed

2. **Admin Authentication**:
   - Admin enters credentials → Server validates → Session established → Dashboard access granted

## Common Tasks

### Ticket Management

1. **Adjusting maximum tickets**:
   - Edit `index_form.php` and `process_reservation.php`
   - Update the `MAX_TICKETS_PER_PERSON` and `total_tickets_available` variables

2. **Updating event details**:
   - Edit `includes/email/email_template.php`
   - Update the event date, time, and location in the email templates

### Email System

1. **Customizing email templates**:
   - Edit `includes/email/email_template.php`
   - Modify the appropriate section based on the email type (verification, confirmation, cancellation)

2. **Configuring PHPMailer**:
   - Edit `includes/mailer.php`
   - Update SMTP settings for production environments

### Admin Management

1. **Creating new admin users**:
   ```bash
   # Access the admin user management tool
   http://localhost/cangrejos_albinos/admin/check_users.php
   ```

2. **Default admin credentials**:
   - Username: `admin`
   - Password: `change_me_immediately` (change immediately after first login)

## Security Considerations

1. All forms implement CSRF protection
2. Passwords are stored using PHP's password_hash()
3. Input validation on both client and server sides
4. Remember to delete installation files after setup:
   - install.php
   - setup_database.php
   - update_database.php
   - update_db_password.php
   - database.sql
   - database_update.sql
   - database_update_direct.sql
   - admin/check_users.php
   - admin/debug_login.php