# ACME Arts Gallery

A dynamic web application for managing and displaying an art gallery collection. Built with PHP, MySQL, and Bootstrap, this project demonstrates CRUD operations, user authentication, role-based access control, and image handling.

## Features

- 🎨 Browse paintings and artists
- 👤 User authentication and registration
- 🔐 Role-based access control (Admin/User)
- 📧 Newsletter subscription system
- 🔍 Search and filter functionality
- 📸 Image handling for paintings and artists
- 📱 Responsive design
- ✅ WCAG compliance improvements

## Technologies Used

- **Backend:** PHP 8.2
- **Database:** MySQL 8.0
- **Server:** Apache (XAMPP)
- **Frontend:** 
  - HTML5
  - CSS3
  - Bootstrap 5
  - Font Awesome Icons
- **Security:**
  - Password hashing
  - PDO prepared statements
  - Session management
  - Input validation

## Installation

1. **Install XAMPP**
   - Download XAMPP from [Apache Friends](https://www.apachefriends.org/)
   - Run the installer and select PHP, MySQL, and Apache components
   - Complete the installation process

2. **Clone Repository**
   ```bash
   cd c:\xampp\htdocs
   git clone https://github.com/Keanu191/Gallery-Management-System.git
   ```

3. **Database Setup**
   - Start XAMPP Control Panel
   - Start Apache and MySQL services
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `painting_db`
   - Import the `painting_db.sql` file from the project root

4. **Configure Database Connection**
   - Navigate to `functions.php`
   - Update the database connection parameters if needed:
   ```php
   $DATABASE_HOST = 'localhost';
   $DATABASE_USER = 'root';
   $DATABASE_PASS = '';
   $DATABASE_NAME = 'painting_db';
   ```

5. **Access the Website**
   - Open your web browser
   - Navigate to `http://localhost/Paintings/`
   - Default admin credentials:
     - Email: admin@test.com
     - Password: admin123

## Project Structure

```
Paintings/
├── admin/                 # Administrative functions
│   ├── admin_interface.php
│   └── admin_requests.php
├── artist/                # Artist management
│   ├── artists.php
│   ├── create_artist.php
│   ├── update_artist.php
│   └── delete_artist.php
├── member/                # User management
│   ├── member_login.php
│   ├── member_register.php
│   └── member_settings.php
├── painting/              # Painting management
│   ├── paintings.php
│   ├── create_painting.php
│   ├── update_painting.php
│   └── delete_painting.php
├── tests/                 # Test suite
│   ├── TestRunner.php
│   └── TestConfig.php
├── functions.php          # Core functions
├── index.php             # Homepage
├── styles.css            # Global styles
└── painting_db.sql       # Database dump
```

## Screenshots

### Homepage
![Homepage](https://i.imgur.com/nvwY2qQ.png)
*Main landing page with featured paintings*

### Paintings Gallery
![Paintings](https://i.imgur.com/IlsDdjy.png)
*Browse and filter painting collection*

### Artist Management
![Artists](https://i.imgur.com/MmAW2Nd.png)
*Artist listing with CRUD operations*

### Admin Dashboard
![Admin](https://i.imgur.com/N5yeSoE.png)
*Administrative interface for site management*

## Core Features

### User Management
- Registration and authentication
- Role-based access control
- Profile management
- Newsletter subscriptions

### Content Management
- Full CRUD operations for paintings
- Artist portfolio management
- Image upload and processing
- Search and filter functionality

### Administrative Tools
- User request processing
- Account management
- Content moderation
- System statistics

## Testing

The project includes a comprehensive test suite:

```bash
cd c:\xampp\htdocs\Paintings\tests
php TestRunner.php
```

Tests cover:
- Database connectivity
- User authentication
- CRUD operations
- Access control
- Input validation

## Security Implementation

- Password hashing using PHP's password_hash()
- SQL injection prevention via PDO prepared statements
- XSS prevention through htmlspecialchars()
- CSRF token validation
- Input sanitization
- Secure session management


## License

MIT License - Feel free to use this project for learning purposes.
