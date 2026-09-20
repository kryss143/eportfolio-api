# Admin Dashboard Setup

## Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+
- MongoDB (running locally or via Docker)
- `ext-mongodb` PHP extension

## Quick Start

```bash
cd eportfolio-api/eportfolioAPI

# 1. Install dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Edit .env with your MongoDB connection
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=eportfolio
ADMIN_NAME="Your Name"
ADMIN_EMAIL="you@example.com"
ADMIN_PASSWORD="your-secure-password"

# 4. Seed the database
php artisan migrate:fresh --seed

# 5. Create storage link (for image uploads)
php artisan storage:link

# 6. Build frontend assets
npm run build

# 7. Start the server
php artisan serve
```

## First Login

1. Open `http://localhost:8000/admin/login`
2. Enter the admin email and password from your `.env`
3. You'll be redirected to the dashboard

## What's Included

### Dashboard
- KPI cards: projects, blogs, tech skills, users
- Projects by status chart
- Skills by category breakdown
- Needs attention panel
- Recent blog posts
- Recent activity feed

### Content Management
- **Projects**: Full CRUD, image uploads, featured toggle, status management
- **Blog Posts**: Full CRUD, publish/unpublish toggle, slug management
- **Experience**: Edit experience summary (position, years, project counts)
- **Metrics**: Edit portfolio stat values (total projects, commits, etc.)
- **Skills**: Edit skill categories (proficient, familiar, auth, etc.)
- **Tech Skills**: Full CRUD, category filtering

### User Management
- List/search users
- Toggle admin access
- Deactivate users (with safeguards for last admin)

### Activity Log
- Audit trail of all content changes
- Filter by event type and model
- Shows user, model, changed fields, and timestamp

## File Structure

```
app/
├── Enums/
│   ├── ProjectStatus.php
│   └── TechCategory.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/          # Public API (6 controllers)
│   │   └── Admin/           # Admin panel (9 controllers)
│   └── Middleware/
│       └── IsAdmin.php
├── Models/
│   ├── ActivityLog.php
│   ├── Blog.php
│   ├── Experience.php
│   ├── Metric.php
│   ├── Project.php
│   ├── Skill.php
│   └── TechSkill.php
├── Observers/
│   └── ActivityObserver.php
└── Providers/
    └── AppServiceProvider.php

database/
├── factories/               # 6 factories for testing
├── migrations/              # is_admin + activity_log
└── seeders/
    ├── AdminUserSeeder.php
    ├── ContentSeeder.php
    └── DatabaseSeeder.php

resources/
├── css/admin.css            # Admin styles + design tokens
├── js/admin.js              # Admin Alpine.js stores
└── views/admin/
    ├── layouts/app.blade.php
    ├── auth/login.blade.php
    ├── dashboard.blade.php
    ├── projects/             # index, create, edit
    ├── blogs/                # index, create, edit
    ├── experiences/          # index, edit
    ├── metrics/              # index, edit
    ├── skills/               # index, edit
    ├── tech-skills/          # index, create, edit
    ├── users/                # index
    └── activity/             # index

routes/
├── api.php                   # Public API routes
└── web.php                   # Admin + auth routes
```

## Troubleshooting

### "Class not found" errors
Run `composer dump-autoload`.

### MongoDB connection errors
Ensure MongoDB is running and the `MONGODB_URI` in `.env` is correct.

### Image uploads not working
Run `php artisan storage:link` to create the public storage symlink.

### Session not persisting
The session driver is set to `database`. Ensure the `sessions` table exists (created by the users migration). If using MongoDB for sessions, update `config/session.php`.
