# Markdown Editor

A lightweight, self-contained Markdown editor designed to run on shared hosting or a small VM.

## Requirements

- PHP 8.0+
- Write access to the content directory

## What This App Expects

- A single public entry point under `public/` (`public/index.php`).
- All application code lives under `src/`.
- When served from a subdirectory (e.g. `/mdeditor`), you must set `BASE_PATH` in `.env`.

## Configuration

Configuration is loaded from `.env` in the project root (optional). If omitted, defaults are used.

Key settings:

- `REPOS_PATH` (optional): where Markdown files live. Defaults to `./repos`.
- `ADMIN_USERNAME` / `ADMIN_PASSWORD` (required for login).
- `BASE_PATH` (required for subdirectory installs): e.g. `/mdeditor` or `/mdeditor/public`.
- `ALLOWED_EXTENSIONS` (optional): comma-separated extensions, e.g. `md,markdown,txt,html`.

See `.env.example` for a full template.

## Local Development

From the project root:

```sh
php -S localhost:8080 -t public public/router.php
```

Open:

- `http://localhost:8080/`

If you want a subdirectory URL locally (e.g. `/mdeditor`), use a web server with rewrites and set:

```
BASE_PATH=/mdeditor
```

## Deployment (Shared Hosting or VM)

You have two supported URL modes:

### 1) Clean URL (Recommended)

Example:

- `https://example.com/mdeditor/`

Requirements:

- Route `/mdeditor/*` into `public/` at the web-server layer.
- Set `BASE_PATH=/mdeditor` in `.env`.

#### Apache (shared hosting)

Use both `.htaccess` files included in the repo:

- `./.htaccess` rewrites `/mdeditor/*` → `/mdeditor/public/*`
- `./public/.htaccess` sends all requests to `public/index.php`

#### Nginx (VM)

Create a location that maps `/mdeditor/` to `public/`, and ensure PHP requests are routed correctly.

### 2) Public URL

Example:

- `https://example.com/mdeditor/public/`

Requirements:

- Point the web root to `public/`.
- Set `BASE_PATH=/mdeditor/public` in `.env`.

## Admin Tools (CLI)

Run from the project root:

```sh
php admin-tools.php
```

This tool reads and updates `users.json` in the project root.

## Project Layout

- `public/` — public entry point (`public/index.php`)
- `src/` — application code
- `repos/` — Markdown file storage (created automatically if missing)
