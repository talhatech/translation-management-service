# Testing Guide for Translation Management Service

This document outlines the testing strategy, methodologies, and best practices for the Translation Management Service.

## Table of Contents
- [Testing Philosophy](#testing-philosophy)
- [Test Types](#test-types)
- [Running Tests](#running-tests)
- [Test Organization](#test-organization)
- [Writing Tests](#writing-tests)
- [Performance Testing](#performance-testing)
- [Test Coverage](#test-coverage)
- [Continuous Integration](#continuous-integration)

## Testing Philosophy

The Translation Management Service follows a test-driven development approach where possible. Our testing aims to:

- Ensure functionality meets the specified requirements
- Verify API response times meet performance criteria
- Prevent regressions when adding new features
- Document expected behavior through tests
- Maintain at least 95% test coverage

## Test Types

### Unit Tests
Unit tests focus on testing individual components in isolation, mocking dependencies where necessary:
- Service classes
- Repository classes
- Helper functions
- Validation logic

### Feature Tests
Feature tests focus on testing API endpoints and application features:
- API endpoint functionality
- Authentication flows
- Error handling
- Request validation

### Performance Tests
Performance tests ensure the API meets response time requirements:
- API endpoint response times
- Export endpoint performance with large datasets
- Query optimization verification

## Running Tests

### Basic Test Commands

Run the complete test suite:
```bash
./vendor/bin/sail test
```

Run a specific test class:
```bash
./vendor/bin/sail test --filter=TranslationApiTest
```

Run a specific test method:
```bash
./vendor/bin/sail test --filter='TranslationApiTest::testTranslationExport'
```

### Test with Coverage

Generate a code coverage report:
```bash
./vendor/bin/sail test --coverage
```

Generate an HTML coverage report:
```bash
./vendor/bin/sail test --coverage-html reports/
```

## Test Organization

Tests are organized in the following directory structure:

```
tests/
├── Feature/                  # Feature tests
│   ├── AuthTest.php          # Authentication tests
│   ├── LanguageApiTest.php   # Language API endpoint tests
│   ├── PerformanceTest.php   # Performance tests
│   ├── TagApiTest.php        # Tag API endpoint tests
│   └── TranslationApiTest.php # Translation API endpoint tests
└── Unit/                     # Unit tests
    ├── TranslationServiceTest.php  # Translation service tests
    └── ... (other service tests)
```

## Writing Tests

### Unit Test Example

```php
public function testGetTranslationsForLanguage()
{
    // Arrange
    $language = Language::factory()->create(['code' => 'en']);
    $tag = Tag::factory()->create(['name' => 'web']);
    
    // Create 5 translations with tag
    for ($i = 1; $i <= 5; $i++) {
        $translation = Translation::factory()->create([
            'key' => "key_{$i}",
            'value' => "Value {$i}",
            'language_id' => $language->id,
        ]);
        $translation->tags()->attach($tag->id);
    }
    
    // Act
    $translations = $this->service->getTranslationsForLanguage('en', ['web']);
    
    // Assert
    $this->assertCount(5, $translations);
    $this->assertArrayHasKey('key_1', $translations);
}
```

### Feature Test Example

```php
public function testTranslationExport()
{
    // Arrange
    $language = Language::factory()->create(['code' => 'en']);
    Translation::factory()->create([
        'key' => 'welcome_message',
        'value' => 'Welcome to our app',
        'language_id' => $language->id,
    ]);
    
    // Act
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson("/api/export?language=en");
    
    // Assert
    $response->assertStatus(200)
             ->assertJsonFragment(['welcome_message' => 'Welcome to our app']);
}
```

## Performance Testing

The project includes specific performance tests to ensure API response times meet requirements:

- General API endpoints should respond in under 200ms
- Export endpoint should handle large datasets and respond in under 500ms

### Testing with Large Datasets

1. Populate the database with test data:
```bash
./vendor/bin/sail artisan translations:populate 100000
```

2. Run the performance tests:
```bash
./vendor/bin/sail test --filter=PerformanceTest
```

Example performance test:

```php
public function testExportEndpointPerformance()
{
    // Arrange
    // (Database is pre-populated with test data)
    
    // Act & measure
    $start = microtime(true);
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/export?language=en');
    $end = microtime(true);
    
    $executionTime = ($end - $start) * 1000; // Convert to milliseconds
    
    // Assert
    $response->assertStatus(200);
    $this->assertLessThan(500, $executionTime, "Export endpoint response time exceeds 500ms limit");
}
```

## Test Coverage

We aim for >95% test coverage across the codebase. Coverage is measured using PHPUnit's built-in coverage tools.

### Key Areas to Test

- All API endpoints (happy paths and error cases)
- Service layer business logic
- Authentication flows
- Cache invalidation
- Performance with large datasets

## Continuous Integration

When integrated with a CI/CD pipeline, our testing process includes:

1. Running the full test suite on each commit
2. Verifying test coverage meets the 95% threshold
3. Running performance tests with realistic data volumes
4. Linting code to ensure PSR-12 compliance

### CI Pipeline Example

```yaml
# Example CI workflow
stages:
  - build
  - test
  - performance

test:
  stage: test
  script:
    - php artisan test --coverage-text
    - php artisan test --coverage-clover=coverage.xml
  artifacts:
    reports:
      coverage_report:
        coverage_format: cobertura
        path: coverage.xml

performance:
  stage: performance
  script:
    - php artisan translations:populate 100000
    - php artisan test --filter=PerformanceTest
  when: manual
```

## Troubleshooting Common Test Issues

### Database Transactions

Tests use the `RefreshDatabase` trait to ensure a clean database state between tests. If you encounter database-related issues, make sure this trait is properly applied.

### Redis for Testing

When testing with Redis, ensure Redis is available in the test environment. With Laravel Sail, this is configured automatically.

### Performance Variability

Performance tests may show some variability based on system resources. If performance tests are failing intermittently, consider increasing the thresholds slightly or running them in isolation.
