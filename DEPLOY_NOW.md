# 🚀 DEPLOY NOW - Get Your Live Link in 5 Minutes!

## What You're Seeing vs What You Need

**❌ Current:** `aathees.me/Kowsalya_cake/` shows GitHub repository (NOT a working website)
**✅ Goal:** Get a live PHP website like `kowsalya-cake.up.railway.app`

---

## 🎯 Quick Deploy to Railway (Fastest Method)

### Step 1: Go to Railway
Open: **https://railway.app**

### Step 2: Sign In
Click **"Login"** → Choose **"Login with GitHub"**

### Step 3: Deploy
1. Click **"New Project"** (big button in center)
2. Select **"Deploy from GitHub repo"**
3. Click **"Configure GitHub App"** if asked
4. Select repository: **`Kowsalya_cake`**
5. Click **"Deploy Now"**

### Step 4: Add Environment Variables
1. Wait for initial build to complete (1-2 minutes)
2. Click on your project
3. Go to **"Variables"** tab
4. Click **"+ New Variable"** and add these:

```
DB_HOST = gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT = 4000
DB_NAME = test
DB_USERNAME = (your TiDB username)
DB_PASSWORD = (your TiDB password)
APP_ENV = production
APP_DEBUG = false
```

5. Click **"Deploy"** again (it will redeploy with env variables)

### Step 5: Get Your Live Link! 🎉
1. Go to **"Settings"** tab
2. Find **"Domains"** section
3. Click **"Generate Domain"**
4. Your live link: **`https://your-project-name.up.railway.app`**

---

## ✅ After Getting Your Live Link

### Update GitHub Pages Redirect
1. Open `docs/index.html`
2. Replace this line:
   ```html
   <meta http-equiv="refresh" content="0; url=https://kowsalya-cake-production.up.railway.app">
   ```
   With your actual Railway link:
   ```html
   <meta http-equiv="refresh" content="0; url=https://YOUR-ACTUAL-LINK.up.railway.app">
   ```
3. Also update the link in the button
4. Commit and push changes

### Enable GitHub Pages (Optional)
1. Go to GitHub repository settings
2. Navigate to **Pages** section (left sidebar)
3. Source: **Deploy from a branch**
4. Branch: **main**
5. Folder: **/docs**
6. Click **Save**

Now `aathees.me/Kowsalya_cake/` will redirect to your live Railway site!

---

## 🔐 First Login After Deployment

### Admin Login
- URL: `https://your-railway-link.up.railway.app/admin/login.php`
- Email: `admin@kowsalyacake.com`
- Password: `admin123`
- **⚠️ CHANGE PASSWORD IMMEDIATELY!**

### Customer Portal
- Homepage: `https://your-railway-link.up.railway.app/`
- Shop: `https://your-railway-link.up.railway.app/shop.php`

---

## 🆘 Troubleshooting

### Build Failed
- Check Railway logs in the **"Deployments"** tab
- Ensure all environment variables are set correctly

### Database Connection Error
- Verify TiDB credentials are correct
- Check TiDB Cloud dashboard is active
- Ensure DB_HOST and DB_PORT are exact

### 404 Error
- Railway might need explicit PORT binding
- Check that start command is: `php -S 0.0.0.0:$PORT`

---

## 📱 Share Your Website

Once deployed, share your link:
```
🎂 Kowsalya Cake Shop - Now Live!
Visit: https://your-app.up.railway.app

Fresh cakes delivered to your door! 🍰
```

---

## 💡 Pro Tips

1. **Custom Domain:** Railway allows custom domains in free tier
2. **Auto Deploy:** Every git push automatically deploys
3. **Logs:** Check "Deployments" tab for errors
4. **Metrics:** Monitor usage in Railway dashboard

---

## 🎯 Expected Timeline

- ✅ Sign up: **30 seconds**
- ✅ Connect repo: **30 seconds**
- ✅ First deploy: **2-3 minutes**
- ✅ Add env variables: **1 minute**
- ✅ Redeploy: **2-3 minutes**
- **Total: ~5-7 minutes to live website!**

---

**Need more help?** Check `DEPLOYMENT_GUIDE.html` for visual instructions!
