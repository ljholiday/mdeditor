# Markdown Editor

A self-contained Markdown editor designed for shared hosting. The app assumes a hostile hosting environment and enforces a single public entry point via the `public/` directory.

## Requirements

- PHP 8.0+
- File system access for the content repository directory

## Quick Start (Local)

1. Copy `.env.example` to `.env` and update credentials.
2. Start the built-in server from the project root:

```sh
php -S localhost:8080 -t public public/router.php
```

Then open `http://localhost:8080/`.

## Shared Hosting Deployment

- Point the web root (document root) to the `public/` directory.
- Do not expose the project root; `public/` must be the only web-accessible path.
- No `.htaccess` is required or assumed.

## Configuration

Configuration is loaded from `.env` (optional). If omitted, defaults are used.

- `REPOS_PATH`: path where Markdown files are stored. Defaults to `./repos`.
- `ADMIN_USERNAME` and `ADMIN_PASSWORD`: credentials for login.

See `.env.example` for the available settings.

## Admin Tools (CLI)

Run the CLI admin utility from the project root:

```sh
php admin-tools.php
```

This tool reads and updates `users.json` in the project root.

## Project Layout

- `public/`: the only public entry point (`public/index.php`)
- `src/`: application code
- `repos/`: Markdown file storage (created automatically if missing)
