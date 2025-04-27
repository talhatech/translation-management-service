# Translation Management Service

A scalable, high-performance API for managing translations across multiple languages and contexts.

## Features

- Multi-language support with extensible language management
- Tagging system for organizing translations by context (mobile, web, desktop, etc.)
- High-performance API with response times under 200ms
- JSON export endpoint for frontend applications
- Token-based authentication for API security
- Command-line tool for populating the database with test data
- Comprehensive test suite with unit and feature tests
- Docker support via Laravel Sail for easy development

## Tech Stack

- PHP 8.2
- Laravel 12
- MySQL 8.0
- Redis for caching
- Laravel Sail (Docker-based development environment)

## Installation

### Using Laravel Sail (Recommended)

1. Clone the repository:
```bash
git clone https://github.com/talhatech/translation-management-service.git
cd translation-management-service
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Install PHP dependencies:
```bash
composer install
```

4. Install Laravel Sail:
```bash
php artisan sail:install
```
   Select MySQL, Redis, and Meilisearch when prompted.

5. Start the Docker containers:
```bash
./vendor/bin/sail up -d
```

6. Generate application key:
```bash
./vendor/bin/sail artisan key:generate
```

7. Run migrations:
```bash
./vendor/bin/sail artisan migrate
```

8. (Optional) Seed the database with sample data:
```bash
./vendor/bin/sail artisan translations:populate
```

### Without Docker

1. Clone the repository:
```bash
git clone https://github.com/talhatech/translation-management-service.git
cd translation-management-service
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Install dependencies:
```bash
composer install
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in the .env file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=translation_service
DB_USERNAME=root
DB_PASSWORD=
```

6. Run migrations:
```bash
php artisan migrate
```

7. (Optional) Seed the database with sample data:
```bash
php artisan translations:populate
```

8. Start the development server:
```bash
php artisan serve
```

## API Documentation

API documentation is available using Swagger UI. After installation, you can access it at:

```
http://localhost/api/documentation
```

## API Endpoints

### Authentication

- `POST /api/register` - Register a new user
- `POST /api/login` - Login and get access token
- `POST /api/logout` - Logout (requires authentication)

### Languages

- `GET /api/languages` - List all languages
- `POST /api/languages` - Create a new language
- `GET /api/languages/{id}` - Get language details
- `PUT /api/languages/{id}` - Update a language
- `DELETE /api/languages/{id}` - Delete a language

### Tags

- `GET /api/tags` - List all tags
- `POST /api/tags` - Create a new tag
- `GET /api/tags/{id}` - Get tag details
- `PUT /api/tags/{id}` - Update a tag
- `DELETE /api/tags/{id}` - Delete a tag

### Translations

- `GET /api/translations` - List translations (with filters)
- `POST /api/translations` - Create a new translation
- `GET /api/translations/{id}` - Get translation details
- `PUT /api/translations/{id}` - Update a translation
- `DELETE /api/translations/{id}` - Delete a translation
- `GET /api/export` - Export translations as JSON for frontend use

## Design Decisions

### Database Schema

The database schema is designed to be scalable and efficient:

- **Languages Table**: Stores supported languages with a unique code (e.g., 'en', 'fr')
- **Tags Table**: Stores context tags (e.g., 'mobile', 'web', 'admin')
- **Translations Table**: Stores translation keys and values with language references
- **Translation_Tag Pivot Table**: Manages many-to-many relationships between translations and tags

### Performance Optimization

- **Indexing**: Strategic indexes on frequently queried fields
- **Caching**: Redis caching for language lists and translation exports
- **Pagination**: All list endpoints support pagination to handle large datasets
- **Query Optimization**: Carefully crafted queries to minimize database load

### SOLID Principles

- **Single Responsibility Principle**: Each class has a single responsibility
- **Open/Closed Principle**: Classes are open for extension but closed for modification
- **Liskov Substitution Principle**: Objects can be replaced with instances of their subtypes
- **Interface Segregation**: Clients aren't forced to depend on interfaces they don't use
- **Dependency Inversion**: High-level modules don't depend on low-level modules

### Security

- Token-based authentication using Laravel Sanctum
- Input validation on all endpoints
- Protection against SQL injection through Laravel's query builder
- Rate limiting on API endpoints

## Testing

The project includes a comprehensive test suite covering unit tests, feature tests, and performance tests.

For detailed information about our testing approach, please see [TESTING.md](TESTING.md).

### Quick Start with Testing

```bash
# Run all tests
./vendor/bin/sail test

# Run performance tests
./vendor/bin/sail test --filter=PerformanceTest

# Generate code coverage report
./vendor/bin/sail test --coverage-html reports/
```

## Development Workflow with Sail

### Useful Commands

```bash
# Start Laravel Sail
./vendor/bin/sail up -d

# Stop Laravel Sail
./vendor/bin/sail down

# Run migrations
./vendor/bin/sail artisan migrate

# Run database seeder
./vendor/bin/sail artisan db:seed

# Run artisan commands
./vendor/bin/sail artisan [command]

# Run composer commands
./vendor/bin/sail composer [command]

# Run tests
./vendor/bin/sail test

# Run specific tests
./vendor/bin/sail test --filter=TranslationApiTest

# Run with code coverage report
./vendor/bin/sail test --coverage
```

### Sail Aliases

To simplify working with Sail, consider adding this alias to your shell configuration:

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

Then you can simply use `sail` instead of `./vendor/bin/sail`:

```bash
sail up -d
sail artisan migrate
sail test
```

## Future Improvements

- Implement versioning for translations
- Add support for translation variables/placeholders
- Implement webhook notifications for translation updates
- Add support for importing translations from different formats (XLSX, CSV)
- Implement a web UI for translation management
- Add Elasticsearch for faster searching through large translation datasets
- Implement automated deployments with CI/CD pipeline

## License

This project is build with Laravel which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
