# 🎂 Kowsalya Cake Shop - Online Shopping Website

A modern, responsive e-commerce platform for cake ordering built with PHP, MySQL/TiDB Cloud, and vanilla JavaScript.

![PHP Version](https://img.shields.io/badge/PHP-%5E7.4%20%7C%7C%20%5E8.0-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## ✨ Features

- 🛍️ **User Features:**
  - Browse products with category filtering and search
  - Shopping cart functionality
  - User authentication and registration
  - Order management and tracking
  - Invoice generation
  - Newsletter subscription

- 👨‍💼 **Admin Features:**
  - Product management (CRUD operations)
  - Order management and status updates
  - User management
  - Dashboard with analytics

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB or TiDB Cloud account
- Web server (Apache/Nginx) or PHP built-in server
- Composer (optional, for dependency management)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Aatheeswaran-M/Kowsalya_cake.git
   cd Kowsalya_cake
   ```

2. **Set up environment variables:**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` file with your database credentials:
   ```
   DB_HOST=your_database_host
   DB_PORT=4000
   DB_NAME=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

3. **Import the database schema:**
   ```bash
   # Using MySQL command line
   mysql -u your_username -p your_database_name < database/schema_fixed.sql
   
   # Or import through phpMyAdmin or your database client
   ```

4. **Set up admin account:**
   ```bash
   php reset_admin.php
   ```
   Default credentials:
   - Email: `admin@kowsalyacake.com`
   - Password: `admin123`
   
   **⚠️ Change these credentials immediately after first login!**

5. **Configure file permissions (Linux/Mac):**
   ```bash
   chmod -R 755 .
   chmod -R 777 assets/images
   ```

### Running Locally

#### Option 1: PHP Built-in Server
```bash
php -S localhost:8000
```
Visit: `http://localhost:8000`

#### Option 2: Using XAMPP/WAMP
1. Copy project to `htdocs` (XAMPP) or `www` (WAMP) folder
2. Start Apache and MySQL services
3. Visit: `http://localhost/Online-Shopping-website`

#### Option 3: Using Docker
```bash
docker build -t kowsalya-cake .
docker run -p 8080:80 kowsalya-cake
```
Visit: `http://localhost:8080`

## 🌐 Deployment Options

### Option 1: Heroku (Recommended for PHP)

1. **Install Heroku CLI** and login:
   ```bash
   heroku login
   ```

2. **Create new Heroku app:**
   ```bash
   heroku create kowsalya-cake
   ```

3. **Set environment variables:**
   ```bash
   heroku config:set DB_HOST=your_host
   heroku config:set DB_PORT=4000
   heroku config:set DB_NAME=your_db
   heroku config:set DB_USERNAME=your_user
   heroku config:set DB_PASSWORD=your_pass
   ```

4. **Deploy:**
   ```bash
   git push heroku main
   ```

5. **Your live link:**
   ```
   https://kowsalya-cake.herokuapp.com
   ```

### Option 2: Railway.app

1. Visit [Railway.app](https://railway.app)
2. Click "New Project" → "Deploy from GitHub repo"
3. Select your repository
4. Add environment variables in Railway dashboard
5. Deploy automatically

**Live Link:** `https://your-project.up.railway.app`

### Option 3: Render.com

1. Visit [Render.com](https://render.com)
2. Create new "Web Service"
3. Connect your GitHub repository
4. Set build command: `composer install` (if using composer)
5. Set start command: `php -S 0.0.0.0:$PORT`
6. Add environment variables

**Live Link:** `https://kowsalya-cake.onrender.com`

### Option 4: Traditional Hosting (cPanel)

1. Export your files via FTP/SFTP
2. Import database via phpMyAdmin
3. Update `.env` file with hosting database credentials
4. Set up domain/subdomain

### Option 5: AWS/DigitalOcean/Linode

Deploy on a VPS with Apache/Nginx + PHP + MySQL stack. Follow standard LAMP/LEMP setup guides.

## 🔒 Security Considerations

- ✅ Database credentials secured via environment variables
- ✅ Password hashing with bcrypt
- ✅ Prepared statements to prevent SQL injection
- ✅ Session management for authentication
- ⚠️ Change default admin credentials immediately
- ⚠️ Set `APP_DEBUG=false` in production
- ⚠️ Use HTTPS in production
- ⚠️ Keep PHP and dependencies updated

## 📁 Project Structure

```
Online-Shopping-website/
├── admin/              # Admin panel pages
│   ├── products/      # Product management
│   ├── orders/        # Order management
│   └── users/         # User management
├── api/               # REST API endpoints
│   ├── auth/         # Authentication
│   ├── cart/         # Cart operations
│   ├── orders/       # Order operations
│   └── products/     # Product operations
├── assets/           # Static assets
│   └── images/       # Product images
├── config/           # Configuration files
│   └── database.php  # Database connection
├── customer/         # Customer portal pages
├── database/         # SQL schema files
├── .env.example      # Environment template
├── index.php         # Homepage
├── shop.php          # Product listing
└── README.md         # This file
```

## 🛠️ API Endpoints

### Authentication
- `POST /api/auth/login.php` - User login
- `POST /api/auth/register.php` - User registration

### Products
- `GET /api/products/read.php` - Get all products
- `GET /api/products/read_single.php?id={id}` - Get product by ID

### Cart
- `GET /api/cart/read.php` - Get cart items
- `POST /api/cart/add.php` - Add to cart
- `PUT /api/cart/update.php` - Update cart item
- `DELETE /api/cart/delete.php` - Remove from cart

### Orders
- `POST /api/orders/create.php` - Create order
- `GET /api/orders/read.php` - Get user orders

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/YourFeature`
3. Commit changes: `git commit -m 'Add YourFeature'`
4. Push to branch: `git push origin feature/YourFeature`
5. Open a Pull Request

## 📝 License

This project is open source and available under the MIT License.

## 👨‍💻 Developer

**Aatheeswaran M**
- GitHub: [@Aatheeswaran-M](https://github.com/Aatheeswaran-M)

## 🐛 Known Issues & Troubleshooting

### Database Connection Issues
- Ensure TiDB Cloud credentials are correct
- Check if SSL is properly configured
- Verify port 4000 is accessible

### File Upload Issues
- Check `assets/images/` directory permissions
- Verify PHP `upload_max_filesize` and `post_max_size` settings

### Session Issues
- Ensure `session_start()` is called before any output
- Check PHP session directory permissions

## 📞 Support

For issues, questions, or contributions, please open an issue on GitHub.

---

**Note:** This is a PHP application and requires a PHP-enabled server. GitHub Pages only supports static HTML/CSS/JS, so you'll need to use one of the hosting options mentioned above.

## 🎯 Live Demo

**After deploying to one of the hosting platforms above, your live link will be:**

- **Heroku:** `https://your-app-name.herokuapp.com`
- **Railway:** `https://your-app-name.up.railway.app`
- **Render:** `https://your-app-name.onrender.com`
- **Custom Domain:** `https://yourdomain.com`

Replace `your-app-name` with your actual application name on the hosting platform.
