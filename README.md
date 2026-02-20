# Multi-Language Content Management API

A high-performance, scalable RESTful API built with Laravel 11 to manage localized content strings across multiple platforms. This system is architected to handle large datasets (100,000+ records) with optimized search and retrieval speeds under 500ms.

---

## 🚀 Key Features

-   **Localized Content Management**: Full CRUD operations for managing keys/values across various locales.
-   **Global Search**: Advanced filtering by `key`, `value`, `locale`, and JSON-based `tags`.
-   **Performance Optimized**: Sub-500ms response times for large-scale data via database indexing and query optimization.
-   **JWT/Sanctum Authentication**: Secure endpoints protected by Laravel Sanctum.
-   **Robust Testing**: High reliability with **93.3% code coverage** on core API controllers.

---

## 🏗 Architecture & Design Patterns

### 1. Service-Repository Pattern

To ensure a clean separation of concerns and maintainability, the project implements the **Repository Pattern**:

-   **Controllers**: Handle HTTP requests, input validation, and API responses.
-   **Services**: Contain business logic, such as coordinating data between the database and the repository.
-   **Repositories**: Encapsulate Eloquent queries, ensuring database interactions are centralized and easily mockable for testing.

### 2. Database & Search Optimization

To meet the requirement of handling 100,000+ records efficiently:

-   **Indexing**: Composite indexes are applied to the `locale` and `key` columns for O(1) lookup speeds.
-   **JSON Filtering**: Utilizes native MySQL `whereJsonContains` for efficient tag-based searching within JSON columns.
-   **Parameter Grouping**: Search queries use nested logical groups in SQL to ensure search terms stay strictly scoped to the requested locale, preventing "leaking" results from other languages.

---

## 🛠 Tech Stack

-   **Framework**: Laravel 12
-   **Database**: MySQL 8.0
-   **Auth**: Laravel Sanctum
-   **Environment**: Laravel Sail (Docker)
-   **Testing**: PHPUnit / Mockery

---

## 🚦 Getting Started

### Prerequisites

-   Docker Desktop installed.
-   PHP 8.2+ (for local composer installation).

### Installation

1. **Clone the repository:**
    ```bash
    git clone git@github.com:domsviado/content-management-service.git
    cd content-management-api
    ```
2. **Install dependencies:**
    ```bash
    composer install
    ```
3. **Start the Docker environment (Sail):**
    ```bash
    ./vendor/bin/sail up -d
    ```
4. **Run migrations and seed the database::**
    ```bash
    ./vendor/bin/sail artisan migrate --seed
    ```

---

## 🧪 Testing & Quality Assurance

This project maintains a high standard of code quality and reliability. The test suite covers "Happy Paths," "Sad Paths" (404, 422), and security constraints.

-   **Feature Tests**: Validate API endpoints, middleware, and authentication flows.
-   **Unit Tests**: Validate business logic in isolation within the Service layer.

**Run all tests with coverage report:**

```bash
./vendor/bin/sail artisan test --coverage-text
```

---

## 📡 API Reference

### Public Endpoints

-   `GET /api/v1/content/{locale}` - Fetch all translations for a specific language.

### Protected Endpoints (Requires Bearer Token)

-   `GET /api/v1/content/search` - Advanced filtering.
    -   **Query Params**: `q` (search term), `tag` (JSON tag search), `locale` (exact match).
-   `GET /api/v1/content/detail/{id}` - Fetch specific record details.
-   `POST /api/v1/content` - Create or update (Upsert) content keys.
-   `GET /api/v1/content/export/{locale}` - Export content for a specific locale.

---

## 📈 Performance Benchmarks

Tested with **100,000 records**:

-   **Direct ID Lookup**: ~90ms
-   **Locale Indexed Fetch**: ~300ms
-   **Global Search (LIKE %query%)**: ~115ms
-   **JSON Tag Search**: ~200ms
