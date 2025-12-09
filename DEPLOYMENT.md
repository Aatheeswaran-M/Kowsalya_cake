# 🚀 Quick Deployment Guide

## Important Note
**This is a PHP application and CANNOT be hosted on GitHub Pages**, as GitHub Pages only supports static HTML/CSS/JavaScript. You need a PHP-enabled hosting platform.

## ✅ Recommended Hosting Options (FREE)

### 1. Railway.app (Easiest & Free)
**Live Link Format:** `https://your-app-name.up.railway.app`

**Steps:**
1. Visit https://railway.app
2. Sign up with your GitHub account
3. Click "New Project" → "Deploy from GitHub repo"
4. Select `Kowsalya_cake` repository
5. Add environment variables:
   - `DB_HOST` = gateway01.ap-southeast-1.prod.aws.tidbcloud.com
   - `DB_PORT` = 4000
   - `DB_NAME` = test
   - `DB_USERNAME` = Your TiDB username
   - `DB_PASSWORD` = Your TiDB password
6. Deploy automatically ✅
7. Get your live link!

### 2. Render.com (Free Tier Available)
**Live Link Format:** `https://your-app-name.onrender.com`

**Steps:**
1. Visit https://render.com
2. Sign up with GitHub
3. Click "New +" → "Web Service"
4. Connect your GitHub repository
5. Configure:
   - **Build Command:** `composer install`
   - **Start Command:** `php -S 0.0.0.0:$PORT`
6. Add same environment variables as Railway
7. Click "Create Web Service"
8. Wait for deployment (5-10 minutes)

### 3. Heroku (Traditional Choice)
**Live Link Format:** `https://your-app-name.herokuapp.com`

**Steps:**
1. Install Heroku CLI: https://devcenter.heroku.com/articles/heroku-cli
2. Open terminal/PowerShell and run:
   ```bash
   heroku login
   heroku create your-app-name
   heroku config:set DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
   heroku config:set DB_PORT=4000
   heroku config:set DB_NAME=test
   heroku config:set DB_USERNAME=your_username
   heroku config:set DB_PASSWORD=your_password
   git push heroku main
   ```
3. Your site is live at `https://your-app-name.herokuapp.com`

## 🔧 After Deployment

1. **Access Admin Panel:**
   - URL: `https://your-live-link.com/admin/login.php`
   - Email: `admin@kowsalyacake.com`
   - Password: `admin123`
   - **⚠️ CHANGE PASSWORD IMMEDIATELY!**

2. **Test the Website:**
   - Homepage: `https://your-live-link.com/index.php`
   - Shop: `https://your-live-link.com/shop.php`
   - Create test customer account
   - Add products to cart
   - Test checkout process

## 🎯 Your Live Links

After deploying on any platform, your website will be accessible at:

- **Railway:** `https://kowsalya-cake-production.up.railway.app`
- **Render:** `https://kowsalya-cake.onrender.com`
- **Heroku:** `https://kowsalya-cake.herokuapp.com`

## ⚠️ Troubleshooting

### Database Connection Error
- Double-check your TiDB credentials
- Ensure environment variables are set correctly
- Check if TiDB Cloud service is active

### 500 Internal Server Error
- Check server logs in your hosting dashboard
- Verify PHP version is 7.4 or higher
- Ensure all required PHP extensions are enabled

### Images Not Loading
- Check file upload permissions
- Verify `assets/images` directory exists
- Upload sample product images via admin panel

## 📱 Share Your Live Link

Once deployed, you can share your live website link:
```
🎂 Kowsalya Cake Shop
Visit: https://your-app-name.your-hosting.com

Admin Access:
https://your-app-name.your-hosting.com/admin/login.php
```

## 🆘 Need Help?

- Check the main `README.md` for detailed instructions
- Review hosting platform documentation
- Open an issue on GitHub: https://github.com/Aatheeswaran-M/Kowsalya_cake/issues

---

**Note:** Free hosting tiers may have limitations like:
- Cold starts (site sleeps after inactivity)
- Limited bandwidth/storage
- Slower performance

For production use, consider upgrading to paid hosting plans.
