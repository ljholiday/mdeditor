# Markdown Editor

A lightweight, self‑contained web app for editing Markdown files on your server.

## What It Does

- Lists Markdown files from a configurable directory
- Lets you open, edit, and save files in the browser
- Supports nested folders
- Optional HTML editing when enabled in `.env`

## Why It Exists

This is a simple, practical editor for shared hosting or small servers where you want quick access to your Markdown content without installing a full CMS.

## Configuration

Configuration lives in `.env`. See `.env.example` for available options.

Login is optional. If `ADMIN_USERNAME` and `ADMIN_PASSWORD` are both set, the app requires login. If either is missing, the app is open.

## Security

The app is designed to run with a single public entry point under `public/` and keeps all other code outside the web root.

## Project Layout

- `public/` — public entry point
- `src/` — application code
- `repos/` — Markdown file storage (created automatically if missing)
