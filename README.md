# Warehouse API — Setup Guide

## 1. Database
```bash
mysql -u root -p < schema.sql
```

## 2. Web Server

### Apache (recommended)
Point document root to project root. Enable `mod_rewrite`.
The `.htaccess` routes `/api/*` → `public/index.php`.

Serve `frontend/index.html` from root or a separate static host.

### PHP built-in (dev only)
```bash
# Terminal 1 — API server
php -S localhost:8000 -t . public/index.php

# Terminal 2 — Frontend
php -S localhost:3000 -t frontend
```

> Or use Apache/Nginx and point root at project folder.

## 3. Environment (optional)
Set env vars to override defaults:
```
DB_HOST=localhost
DB_NAME=warehouse_db
DB_USER=root
DB_PASS=
JWT_SECRET=your_strong_random_secret_here
```

## 4. Default Login
- **Email:** admin@warehouse.local
- **Password:** admin123
- ⚠️ Change via direct DB UPDATE after first login.

---

## API Reference

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | /api/auth/login | — | Login, get JWT |
| GET | /api/products | any | List / search products |
| POST | /api/products | admin | Create product |
| PUT | /api/products?id=N | admin | Update product |
| DELETE | /api/products?id=N | admin | Delete product |
| POST | /api/stock/in | admin | Add stock |
| POST | /api/stock/out | admin | Remove stock |
| GET | /api/faulty | any | List faulty items |
| POST | /api/faulty | admin | Move to/from faulty |
| GET | /api/replace?q=keyword | admin | Search stock-out records |
| POST | /api/replace | admin | Process return |
| POST | /api/import | admin | CSV/XLSX bulk import |
| GET | /api/reports?type=inventory | any | Inventory report |
| GET | /api/reports?type=movements | any | Movement log |
| GET | /api/reports?type=invoice&ids=1,2 | any | Invoice data |

All protected routes require: `Authorization: Bearer <token>`

## Folder Structure
```
warehouse/
├── api/
│   ├── auth/login.php
│   ├── products/{index,create,update,delete}.php
│   ├── stock/{in,out}.php
│   ├── faulty/{index,create}.php
│   ├── replace/process.php
│   ├── import/import.php
│   └── reports/report.php
├── core/
│   ├── bootstrap.php   ← include in every endpoint
│   ├── db.php
│   ├── jwt.php
│   ├── guard.php
│   └── helpers.php
├── frontend/
│   └── index.html      ← SPA, calls API via fetch
├── public/
│   └── index.php       ← URL router
├── assets/
│   ├── css/bootstrap.min.css
│   └── js/bootstrap.bundle.min.js
├── .htaccess
└── schema.sql
```
