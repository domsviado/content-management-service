Multi-Language Content Management API
A high-performance Laravel-based API for managing localized content strings across multiple platforms (Web, Mobile, etc.). This project is architected to handle high-volume data (100k+ records) while maintaining response times under 500ms.

🚀 Key Features
Localized Content Management: CRUD operations for content keys across different locales.

Global Search: High-performance filtering by key, value, locale, and JSON-based tags.

JWT/Sanctum Authentication: Secure API access for administrative tasks.

Performance Optimized: Database indexing and Service-Repository pattern to ensure scalability.

Full Test Suite: >90% code coverage across Controllers, Services, and Repositories.

🛠 Tech Stack
Framework: Laravel 12.52.0

Database: MySQL 8.0 (optimized with composite indexes)

Auth: Laravel Sanctum

Testing: PHPUnit / Pest

Documentation: Swagger/L5-Swagger

🏗 Architecture & Design Patterns

1. Service-Repository Pattern
   To maintain a clean separation of concerns, the project utilizes the Repository Pattern.

Controllers: Handle request validation and HTTP responses.

Services: Handle business logic (e.g., coordinating between cache and database).

Repositories: Handle all database-specific queries, ensuring the logic is reusable and testable in isolation.

2. Search Optimization
   The search logic uses grouped WHERE clauses to prevent logical leaks between locale filters and LIKE search terms. To support the 100k record requirement:

Indexes have been applied to locale and key columns.

The tags column is stored as a JSON type, utilizing whereJsonContains for efficient filtering.

3. Testing Strategy
   The project maintains a rigorous test suite using RefreshDatabase for isolation.

Feature Tests: Covering all API endpoints, including "Sad Paths" (404, 422, 401).

Unit Tests: Mocking dependencies to test business logic in the Service layer.

Coverage: Verified at 93.3%+ for the Content Controller and 100% for Authentication.

🚦 Getting Started
Prerequisites
Docker & Laravel Sail

Installation
Clone the repository:

Bash
git clone git@github.com:domsviado/content-management-service.git
cd content-management-api
Install dependencies:

Bash
composer install
Start the environment:

Bash
./vendor/bin/sail up -d
Run migrations and seeders:

Bash
./vendor/bin/sail artisan migrate --seed
🧪 Running Tests & Coverage
To run the full test suite:

Bash
./vendor/bin/sail artisan test
To generate a coverage report:

Bash
./vendor/bin/sail artisan test --coverage-html reports
📖 API Documentation
Once the server is running, you can access the interactive Swagger documentation at:
http://localhost/api/documentation

Final "Plus Points" addressed:
[x] Authentication: Fully implemented via Sanctum.

[x] Code Quality: Strict typing and PSR-12 compliance.

[x] Performance: Handles 100k records with optimized SQL.

[x] Docker: Seamless environment setup via Sail.
