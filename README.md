# MDRRMO PHP REST API

A PHP 8.2 REST API starter for receiving and reading MDRRMO incident reports. It uses PDO PostgreSQL, prepared statements, and PostGIS geographic points.

## Important before deployment

This repository is a **starter implementation for a separate test database**. The existing MDRRMO Render endpoint was checked with safe GET/OPTIONS requests: `create.php` accepts POST and advertises POST/OPTIONS. Its production database schema and exact accepted emergency-type values were not exposed by those requests.

Do not point this starter at the existing production database or replace the current Render service until the real schema, API contract, and a safe test environment are confirmed. The SQL below creates a new `incidents` table and is intended for a disposable/test PostgreSQL database with PostGIS.

## Repository structure

```text
mdrrmo-api/
├── public/
│   └── api/incidents/
│       ├── create.php
│       ├── list.php
│       └── view.php
├── src/
│   ├── database.php
│   ├── db_connection.php
│   └── http.php
├── database/schema.sql
├── .dockerignore
├── .env.example
├── .gitignore
├── Dockerfile
├── test-db.php
└── README.md
```

## API routes

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/api/incidents/create.php` | Create an incident |
| GET | `/api/incidents/list.php` | List recent incidents |
| GET | `/api/incidents/view.php?id=1` | View one incident |

### POST request fields

The create endpoint accepts `application/json` or `multipart/form-data`. This starter does not save uploaded photos.

- `emergency_type` – required text
- `description` – required text
- `persons_affected` – required whole number, zero or higher
- `latitude` – required decimal from -90 to 90
- `longitude` – required decimal from -180 to 180
- `gps_accuracy` – required non-negative decimal

Example JSON:

```json
{
  "emergency_type": "Flood",
  "description": "Water is rising near the community road.",
  "persons_affected": 3,
  "latitude": 10.0,
  "longitude": 125.0,
  "gps_accuracy": 12.5
}
```

A successful create returns HTTP `201` and JSON containing `success: true` and the new incident ID. Validation errors return HTTP `422`. Server errors do not reveal database credentials or SQL details.

## Local setup

1. Install PHP 8.2 or newer with `pdo_pgsql`.
2. Create a separate PostgreSQL test database with PostGIS enabled.
3. Run `database/schema.sql` against that test database.
4. Copy `.env.example` values into your local environment. Do not commit `.env`.
5. Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`.
6. Start Apache with `public/` as its document root, or use the Dockerfile. Run `php test-db.php` from the repository root to test the database connection from the command line.

## GitHub

1. Sign in at https://github.com.
2. Create a repository named `mdrrmo-api`.
3. Keep it Private for class work unless your instructor requires Public.
4. Upload the repository files and commit the changes.
5. Confirm `.env` and real credentials are not in the repository.

## Render deployment

1. Push the repository to GitHub.
2. In Render, create a new **Web Service** and connect the `mdrrmo-api` repository.
3. Select Docker as the runtime so Render builds the included `Dockerfile`.
4. Add the five database environment variables in Render's Environment settings.
5. For a test deployment, use a separate PostgreSQL database with PostGIS enabled and run `database/schema.sql` once.
6. Deploy and test the GET routes first.
7. Test POST only with fictional data against the test database.
8. Do not replace the existing MDRRMO production API until the existing database schema and frontend contract have been verified.

## Security notes

- Keep database credentials in Render environment variables, never in source code.
- Use HTTPS.
- Replace the wildcard CORS origin in `src/http.php` with the exact website origin before production.
- Add authentication and role checks before exposing incident lists or details publicly.
- Add rate limiting, request-size limits, audit logging, and a verified image-storage service before production use.
- This starter does not implement photo uploads or emergency dispatch.
