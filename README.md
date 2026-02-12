## Student Management REST API (PHP + MySQL)

This project is a student-management REST API built with **core PHP**, **MySQL**, and **PDO**.  
The current revision focuses on refactoring, security, and structure while keeping the same core functionality and endpoint semantics.

### 1. Architecture Overview

- **Core technologies**
  - **PHP** (no framework, suitable for PHP 8+)
  - **MySQL** (5.7+ / 8+)
  - **PDO** for all database access
- **Key responsibilities**
  - **API layer** (`api/`): HTTP handling, JSON parsing, request validation orchestration, and response formatting.
  - **Model layer** (`models/Student.php`): Database operations and reusable validation logic for `students`.
  - **Configuration** (`config/`): Database credentials and app settings.

#### Folder structure

- `api/`
  - `create.php` – Create student (POST)
  - `read.php` – Read all / single / search students (GET)
  - `update.php` – Update student (PUT)
  - `delete.php` – Delete student (DELETE)
  - `response.php` – Shared JSON response helpers
- `config/`
  - `config.php` – Central configuration (DB, debug flags)
  - `database.php` – PDO `Database` class using the config file
- `models/`
  - `Student.php` – Student model with validation and CRUD/search methods
- Other root files:
  - `schema.sql` – Database schema and sample data.
  - `api_test.php` – cURL-based test script (you can adapt the base URL).

### 2. Main Changes in This Refactor

- **Configuration & DB connection**
  - `config/config.php` with environment-variable-aware settings:
    - `SMS_API_DB_HOST`, `SMS_API_DB_NAME`, `SMS_API_DB_USER`, `SMS_API_DB_PASS`, `SMS_API_DEBUG`.
  - `config/database.php`:
    - Uses DSN with charset (`mysql:host=...;dbname=...;charset=utf8mb4`).
    - Enables exceptions (`PDO::ERRMODE_EXCEPTION`), disables emulate prepares.
    - Does **not** echo JSON directly on errors; exceptions are handled by API scripts.

- **Models**
  - `models/Student.php`:
    - Central `validate(array $input, bool $isUpdate = false)`:
      - Required fields: `first_name`, `last_name`, `email`.
      - Email format validation via `filter_var(..., FILTER_VALIDATE_EMAIL)`.
      - Length checks for strings (`first_name`, `last_name`, `phone`, `gender`, `address`).
      - Date validation (`date_of_birth`, `enrollment_date`) using `DateTime::createFromFormat('Y-m-d', ...)`.
      - ID validation for updates.
      - Allowed genders enforced: `Male`, `Female`, `Other`.
    - `fill(array $data)` to map sanitized values into model properties.
    - CRUD/search methods using prepared statements:
      - `create()`, `readAll(?int $limit = null, ?int $offset = null)`,
        `readOne()`, `update()`, `delete()`,
        `search(string $keywords, ?int $limit = null, ?int $offset = null)`.
      - `emailExists(string $email, ?int $excludeId = null)` with optional ID exclusion for updates.
    - Removed HTML-oriented sanitization (`htmlspecialchars/strip_tags`) in favor of plain prepared statements + validation.

- **API endpoints**
  - All main endpoints live under `api/` and:
    - Use a **central response helper** (`send_success`, `send_error`) for consistent JSON.
    - Use appropriate **HTTP status codes**:
      - `201` for successful create.
      - `200` for successful read/update/delete.
      - `400` for malformed input / missing parameters.
      - `405` for wrong HTTP method.
      - `409` for email conflicts.
      - `404` when resources are not found.
      - `422` for field-level validation errors.
      - `500` for unexpected server/DB errors.
    - Handle CORS preflight (`OPTIONS`).

- **Schema**
  - `schema.sql` updated to:
    - Use `utf8mb4` and `utf8mb4_unicode_ci` consistently.
    - Use `INT UNSIGNED` for primary key `id`.
    - Increase `first_name` / `last_name` to `VARCHAR(100)`.
    - Set `email` to `VARCHAR(191)` (safer with utf8mb4 + unique indexes).
    - Keep `phone`, `date_of_birth`, `gender`, `address` as nullable for flexibility.
    - Keep `enrollment_date` as `NOT NULL`.
    - Retain indexes on `email` and `(first_name, last_name)` for lookup/search.

### 3. Installation & Setup

- **1. Clone / place the project**
  - Place the repository under your web root, e.g.:
    - `C:\xampp\htdocs\sms_api`
  - Your base URL will typically be `http://localhost/sms_api`.

- **2. Create the database (fresh install)**
  - Import `schema.sql` using phpMyAdmin, MySQL Workbench, or CLI:

```bash
mysql -u root -p < schema.sql
```

  - This will create the `sms_api` database (if not present), the `students` table, and some sample rows.

- **3. Configure database credentials**
  - Edit `config/config.php` or use environment variables.
  - Examples (XAMPP default):
    - Host: `localhost`
    - DB name: `sms_api`
    - Username: `root`
    - Password: `""` (empty string)
  - For production, prefer **environment variables**:
    - `SMS_API_DB_HOST`, `SMS_API_DB_NAME`, `SMS_API_DB_USER`, `SMS_API_DB_PASS`.

- **4. Verify PHP & extensions**
  - Ensure your PHP installation has:
    - PDO
    - PDO MySQL driver (`pdo_mysql`)

### 4. Database Migration Notes (Existing Installations)

If you already have a running database and want to align it with the revised schema, you can **optionally** apply these changes (test in a staging environment first):

```sql
USE sms_api;

-- Optional: widen name fields and email length
ALTER TABLE students
  MODIFY COLUMN first_name VARCHAR(100) NOT NULL,
  MODIFY COLUMN last_name VARCHAR(100) NOT NULL,
  MODIFY COLUMN email VARCHAR(191) NOT NULL;

-- Ensure charset/collation (adjust if database already configured)
ALTER DATABASE sms_api
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE students
  CONVERT TO CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

These modifications preserve existing data while bringing the schema closer to best practices.

### 5. API Endpoints & Usage

You should call the API via the **`api/` routes**:

- Base URLs:
  - `POST   /sms_api/api/create.php`
  - `GET    /sms_api/api/read.php`
  - `PUT    /sms_api/api/update.php`
  - `DELETE /sms_api/api/delete.php`

#### 5.1 Create Student – `POST /api/create.php`

- **Request (JSON)**:

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "555-0101",
  "date_of_birth": "2000-05-15",
  "gender": "Male",
  "address": "123 Main St",
  "enrollment_date": "2024-01-15"
}
```

- **Response (201)**:

```json
{
  "success": true,
  "message": "Student created successfully.",
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "555-0101",
    "date_of_birth": "2000-05-15",
    "gender": "Male",
    "address": "123 Main St",
    "enrollment_date": "2024-01-15"
  }
}
```

- **Validation errors (422)**:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": "Email format is invalid."
  }
}
```

#### 5.2 Read All Students – `GET /api/read.php`

- **Optional query parameters**:
  - `page` (default: 1)
  - `per_page` (default: 50, max: 100)

- **Example**: `GET /sms_api/api/read.php?page=1&per_page=20`

- **Response (200)**:

```json
{
  "success": true,
  "data": {
    "count": 5,
    "students": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "phone": "555-0101",
        "date_of_birth": "2000-05-15",
        "gender": "Male",
        "address": "123 Main St",
        "enrollment_date": "2024-01-15",
        "created_at": "2024-01-15 10:00:00",
        "updated_at": "2024-01-15 10:00:00"
      }
    ],
    "page": 1,
    "per_page": 20
  }
}
```

#### 5.3 Read Single Student – `GET /api/read.php?id={id}`

- Example: `GET /sms_api/api/read.php?id=1`
- Returns `404` if the student does not exist, `400` if the ID is invalid.

#### 5.4 Search Students – `GET /api/read.php?search={term}`

- Example: `GET /sms_api/api/read.php?search=John&page=1&per_page=10`
- Searches in `first_name`, `last_name`, and `email`.

#### 5.5 Update Student – `PUT /api/update.php`

- **Request (JSON)**:

```json
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe.updated@example.com",
  "phone": "555-9999",
  "date_of_birth": "2000-05-15",
  "gender": "Male",
  "address": "123 Updated St",
  "enrollment_date": "2024-01-15"
}
```

- Responses:
  - `200` on success.
  - `404` if the student does not exist.
  - `409` if the email is already used by another student.
  - `422` for validation errors.

#### 5.6 Delete Student – `DELETE /api/delete.php`

- **Request (JSON)**:

```json
{
  "id": 1
}
```

- Responses:
  - `200` on success.
  - `404` if the student does not exist.
  - `400` if the ID is missing/invalid.

### 6. Testing Instructions

#### 6.1 Using cURL (CLI)

- **Create**

```bash
curl -X POST http://localhost/sms_api/api/create.php \
  -H "Content-Type: application/json" \
  -d '{"first_name":"Alice","last_name":"Williams","email":"alice@example.com"}'
```

- **Read all**

```bash
curl http://localhost/sms_api/api/read.php
```

- **Read single**

```bash
curl http://localhost/sms_api/api/read.php?id=1
```

- **Search**

```bash
curl "http://localhost/sms_api/api/read.php?search=John"
```

- **Update**

```bash
curl -X PUT http://localhost/sms_api/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"id":1,"first_name":"John","last_name":"Doe","email":"john.doe.updated@example.com"}'
```

- **Delete**

```bash
curl -X DELETE http://localhost/sms_api/api/delete.php \
  -H "Content-Type: application/json" \
  -d '{"id":1}'
```

#### 6.2 Using Postman

- Create a collection and add requests:
  - Set the appropriate HTTP method.
  - URL: e.g. `http://localhost/sms_api/api/create.php`.
  - Headers: `Content-Type: application/json`.
  - Body: raw JSON samples shown above.

#### 6.3 Using `api_test.php`

- Ensure `$base_url` at the top of `api_test.php` points to your API base, for example:

```php
$base_url = "http://localhost/sms_api/api";
```

- Run from CLI:

```bash
php api_test.php
```

### 7. Security & Best Practices

- **Prepared statements everywhere**: All DB queries use PDO prepared statements to prevent SQL injection.
- **Input validation**:
  - Email format checked with `filter_var`.
  - IDs validated as positive integers.
  - Dates checked against `YYYY-MM-DD` format.
  - Allowed genders enforced.
- **Error handling**:
  - Centralized JSON error responses via `send_error()`.
  - Proper HTTP status codes, no raw SQL errors leaked in responses (unless you enable verbose debug yourself).
- **Recommended production hardening**:
  - Hide detailed error messages (set `SMS_API_DEBUG` to false).
  - Restrict CORS origins instead of using `*`.
  - Put this API behind TLS (`https`).
  - Add authentication/authorization if using beyond local or trusted environments.

### 8. Recommended Future Improvements

- **Authentication / Authorization**
  - Add API keys, JWT, or session-based auth before exposing the API publicly.
- **Pagination metadata**
  - Include total count and links (`next`, `prev`) in list/search responses.
- **Richer validation**
  - Add phone number pattern checks and maximum/minimum date constraints (e.g., realistic student ages).
- **Soft deletes**
  - Add a `deleted_at` column to `students` and treat deletes as soft by default.
- **Automated tests**
  - Add a `tests/` directory with PHPUnit or simple integration tests for each endpoint.

r