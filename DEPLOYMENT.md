# Deploying to Render.com

Follow these steps to deploy your Music Database app:

## Step 1: Push to GitHub

1. Go to https://github.com and create a new repository
2. Name it something like `music-database-app`
3. In your project folder, open PowerShell and run:

```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
git push -u origin main
```

## Step 2: Deploy on Render

1. Go to https://render.com and sign up (use GitHub to sign in)

2. Click **"New +"** → **"Web Service"**

3. Connect your GitHub repository

4. Configure the service:
   - **Name**: `music-database-app` (or your choice)
   - **Environment**: `Python 3`
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `gunicorn app:app`
   - **Instance Type**: `Free`

5. Click **"Create Web Service"**

6. Wait 3-5 minutes for deployment

7. You'll get a URL like: `https://music-database-app.onrender.com`

## Step 3: Access Your App

- Share the Render URL with anyone
- They can access from anywhere in the world
- All users share the same database

## Important Notes

- **Free tier**: App may sleep after 15 minutes of inactivity
- First load after sleeping takes 30-60 seconds
- Database persists across deployments
- To update: Just push to GitHub, Render auto-deploys

## Troubleshooting

If deployment fails, check the Render logs for errors.
Most common issues:
- Missing dependencies in requirements.txt
- Syntax errors in code
- Database initialization issues

Need help? Check Render documentation: https://render.com/docs
