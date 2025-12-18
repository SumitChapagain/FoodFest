# Deploying FoodFest to Render

Since your application is built with PHP and MySQL, and originally designed for XAMPP (Apache), the best way to host it on Render is using **Docker**.

I have already prepared your codebase for this deployment.

## Step 1: Push to GitHub
Render pulls your code from a Git repository. You need to push your `FoodFest` folder to a GitHub repository.

1. Create a new repository on [GitHub](https://github.com/new).
2. Run these commands in your `FoodFest` folder to push your code:
   ```bash
   git init
   git add .
   git commit -m "Prepare for Render deployment"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
   git push -u origin main
   ```

## Step 2: Create Web Service on Render
1. Go to your Render Dashboard.
2. Click **New +** -> **Web Service**.
3. Connect your GitHub repository.
4. When asked for "Runtime", choose **Docker**.
   - **Name:** FoodFest (or whatever you like)
   - **Region:** Any (e.g., Singapore, Frankfurt)
   - **Branch:** main
   - **Instance Type:** Free (for testing)
5. **Environment Variables**:
   You need to provide the database connection details. Render does NOT provide a free MySQL database. You have two options:
   
   **Option A: Use an external Free MySQL Provider (Recommended)**
   - Sign up for a free MySQL database at [Aiven](https://aiven.io/) or [Clever Cloud](https://www.clever-cloud.com/).
   - Get the Host, User, Password, and Database Name.
   - Add these Environment Variables in Render:
     - `DB_HOST`: (e.g., mysql-service.aivencloud.com)
     - `DB_USER`: (e.g., avnadmin)
     - `DB_PASS`: (your password)
     - `DB_NAME`: (e.g., defaultdb)
     - `DB_PORT`: (e.g., 20924 - check your provider)

   **Option B: Paid Render Database**
   - If you want to use Render's Redis or Postgres, you'd need to change your PHP code. Stick to MySQL (Option A).

6. Click **Create Web Service**.

## Changes Made for Compatibility
I have made several changes to ensure your app runs smoothly on Render:
- **Dockerfile**: Instructs Render how to build your PHP/Apache environment.
- **Database Config**: Updated to read settings from Environment Variables (`config/database.php`).
- **Dynamic Paths**: Updated all JavaScript and PHP redirects to work regardless of whether the app is at the root (`/`) or a subfolder.
- **Root Redirect**: Added `index.php` to redirect visitors to the Order page.

## Troubleshooting
- **Database Connection Error**: Check your Environment Variables in Render.
- **Images not loading**: I fixed the paths, but ensure your images are committed to Git.
