# ePortfolio API

A Laravel + MongoDB Atlas backend that replaces a portfolio frontend's
hardcoded data (projects, blog posts, metrics, experience, skills, tech
skills) with a real, admin-editable API.

**Stack:** Laravel 13 · TailwindCSS · MongoDB · MongoDB Atlas · Laravel Breeze (session auth) · Sanctum (API auth)

This README ties together the 11 build batches into one reference. Each
section names the batch it came from, so you can jump straight to the
relevant conversation turn if you need the original reasoning or want to
regenerate a piece.

---

## Contents

- [Quick start](#quick-start)
- [Project structure](#project-structure)
- [Data model](#data-model)
- [API reference](#api-reference)
- [Admin panel](#admin-panel)
- [Seeding](#seeding)
- [Frontend integration](#frontend-integration)
- [Deployment](#deployment)
- [Known gaps / open decisions](#known-gaps--open-decisions)

---

## Quick start

```bash
# Batch 1 — project init
composer create-project laravel/laravel eportfolio-api "13.*"
cd eportfolio-api
composer require mongodb/laravel-mongodb
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# Batch 2 — Atlas connection
# Create a cluster + DB user in Atlas, then add to .env:
#   DB_CONNECTION=mongodb
#   DB_URI=mongodb+srv://<user>:<pass>@cluster.mongodb.net/eportfolio
#   DB_DATABASE=eportfolio
php artisan tinker
>>> DB::connection('mongodb')->getMongoClient(); // should run with no error

# Batch 7 — admin auth scaffolding
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
php artisan migrate   # see "Known gaps" — needs a relational connection

# Batch 9 — seed sample data
php artisan db:seed --class=PortfolioSeeder

# Run it
php artisan serve
```

Then visit `/api/projects` for the API and `/admin` for the admin panel
(login via whatever Breeze account you register).

---

## Project structure

```
app/
  Models/                    # Batch 3 — 6 Mongo-backed Eloquent models
  Http/
    Resources/                # Batch 4 — JSON shape, _id -> id
    Controllers/
      Api/                     # Batch 5/7 — public + auth:sanctum write routes
      Admin/                   # Batch 8 — Blade CRUD, session auth
    Requests/                  # Batch 6 — Store{Model}Request validation (API only)
  Providers/
    AppServiceProvider.php     # Batch 11 — forces HTTPS in production
config/
  cors.php                    # Batch 10 — origins from FRONTEND_URL / FRONTEND_URLS
database/
  seeders/
    PortfolioSeeder.php        # Batch 9 — loads data/*.json into Atlas
    data/*.json                 # Batch 9 — sample seed content (placeholder)
resources/
  views/
    layouts/admin.blade.php    # Batch 8 — shared admin sidebar layout
    admin/{collection}/         # Batch 8 — index/create/edit/_form per collection
routes/
  api.php                      # Batch 5/7/11 — public (throttled) + protected routes
  web.php                      # Batch 8 — admin panel routes + Breeze auth routes
frontend/
  lib/api.ts                   # Batch 10 — typed client for the frontend to consume
DEPLOYMENT.md                  # Batch 11 — production checklist
deploy.sh                      # Batch 11 — build/optimize/migrate script
env-production.example         # Batch 11 — production .env template
```

---

## Data model

Six collections, defined in Batch 3:

| Model        | Collection    | Fillable fields                                                                                   |
| ------------ | ------------- | ------------------------------------------------------------------------------------------------- |
| `Project`    | `projects`    | title, description, technologies, status, githubLink, demoLink, image, outcome, metrics, featured |
| `Blog`       | `blogs`       | title, excerpt, date, readTime, slug, content                                                     |
| `Metric`     | `metrics`     | label, value, suffix, metricDescription                                                           |
| `Experience` | `experiences` | position, yearsOfExperience, soloProjects, collabProjects                                         |
| `Skill`      | `skills`      | proficient, familiar, authentication, architecture, toolsPlatforms, practices, ai                 |
| `TechSkill`  | `techSkills`  | category, logo, label                                                                             |

All models extend `MongoDB\Laravel\Eloquent\Model` with `_id` as the
primary/route key. API Resources (Batch 4) map `_id` → `id` and mirror the
frontend's TypeScript interfaces field-for-field — no translation layer
needed on the frontend.

---

## API reference

**Public (Batch 5), rate-limited to 60 req/min/IP as of Batch 11:**

```
GET  /api/projects
GET  /api/projects/featured
GET  /api/blogs
GET  /api/blogs/{slug}
GET  /api/metrics
GET  /api/experience
GET  /api/skills
GET  /api/tech-skills
GET  /api/tech-skills/category/{category}
```

**Protected — requires `auth:sanctum` (Batch 7):**

```
POST   /api/{collection}
PUT    /api/{collection}/{id}
DELETE /api/{collection}/{id}
```

(`{collection}` = `projects`, `blogs`, `metrics`, `experience`, `skills`, `tech-skills`)

Write requests are validated by `Store{Model}Request` classes (Batch 6) —
notably `Project.status` is restricted to `built`/`in-progress`, and
`Blog.slug` must be unique and kebab-case.

---

## Admin panel

Blade + Tailwind CRUD at `/admin`, behind Breeze's session `auth`
middleware (Batch 7/8) — a separate guard from the API's `auth:sanctum`.
One controller + `index`/`create`/`edit`/`_form` view set per collection,
built fully on Projects first and replicated for the other five.

Admin forms validate their own shape rather than reusing the API's
`Store{Model}Request` classes, since forms post plain fields (newline-
separated textareas for array fields, checkboxes for booleans) where the
API expects JSON arrays/booleans directly.

---

## Seeding

`database/seeders/data/*.json` — one file per collection, loaded by
`PortfolioSeeder` (Batch 9), which truncates and re-inserts on every run
(safe to re-seed repeatedly). `DatabaseSeeder` calls it too, so both
`php artisan db:seed` and `php artisan db:seed --class=PortfolioSeeder`
work.

> **The seed content is placeholder**, not real portfolio data — sample
> projects, blog posts, and generic skill lists that match the schema
> exactly. Replace the JSON contents with real content before going live;
> the seeder logic itself doesn't need to change.

---

## Frontend integration

`frontend/lib/api.ts` (Batch 10) — a typed client with one function per
public endpoint, matching the API Resource shapes exactly. CORS
(`config/cors.php`) reads allowed origins from `FRONTEND_URL` /
`FRONTEND_URLS` in `.env` rather than hardcoding them.

> This piece was generated without visibility into the actual frontend
> repo, so two things need your input: (1) confirm the bundler's env-var
> syntax (`api.ts` currently assumes Vite's `import.meta.env`; swap for
> `process.env.NEXT_PUBLIC_API_URL` on Next.js), and (2) actually delete
> the hardcoded arrays in the frontend and swap in these functions — send
> over the frontend files to finish this properly.

---

## Deployment

See **`DEPLOYMENT.md`** for the full checklist and **`deploy.sh`** for the
build/optimize/migrate script (Batch 11). Summary:

- Fresh `APP_KEY`, `APP_DEBUG=false`, production Atlas user + IP allowlist
- `composer install --optimize-autoloader --no-dev`, `npm run build`, cache config/routes/views
- CORS locked to the live frontend domain via `FRONTEND_URL`
- Public routes throttled (`60,1`), HTTPS forced via `AppServiceProvider`
- Atlas backups + uptime monitoring enabled; dev credentials rotated

---

## Known gaps / open decisions

Flagged along the way, collected here so nothing gets lost:

1. **Auth needs a relational connection.** Breeze/Sanctum's `users`,
   `sessions`, and `personal_access_tokens` tables are relational
   migrations — they won't run against `DB_CONNECTION=mongodb`. Add a
   second connection (SQLite is enough for a low-traffic admin panel) just
   for auth; portfolio data stays on the explicit `mongodb` connection each
   Model already declares. Unresolved as of Batch 11 — sort this out before
   the first `migrate --force` in production.

2. **`Project.metrics` isn't editable in the admin UI.** The per-project
   embedded array (distinct from the top-level `Metric` collection) needs
   either JS or a repeater UI to edit through a Blade form; out of scope
   for the Batch 8 pass. It's still settable via the API or seed data.

3. **Frontend hookup is half-done.** `lib/api.ts` exists and is typed
   correctly, but the actual hardcoded-array removal in the frontend
   codebase hasn't happened — I don't have that repo. Send it over to
   finish Batch 10 properly.

4. **Resource naming assumptions.** Admin routes use `experiences` and
   `tech-skills` as resource segments; the API uses `experience` (singular)
   and `tech-skills`. If your frontend or seed data expects different
   naming, both `routes/api.php` and `routes/web.php` need updating
   together.
