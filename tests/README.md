# Paintings Project Test Suite

A simple testing framework for the Paintings PHP application that tests core functionality without external dependencies.

## Test Structure

```
tests/
├── TestRunner.php    # Main test execution file
├── TestConfig.php    # Test configuration constants
└── README.md        # This documentation
```

## Running Tests

1. Open Command Prompt
2. Navigate to the tests directory:
   ```batch
   cd c:\xampp\htdocs\Paintings\tests
   ```
3. Run the tests:
   ```batch
   php TestRunner.php
   ```

## What's Being Tested

- Authentication Functions
  - `is_logged_in()`
  - `get_logged_in_user()`
  - Session handling

- Database Operations
  - Connection testing
  - PDO instance verification

- Utility Functions
  - `get_path()`
  - Path string validation

## Test Results

The test runner will display:
- ✓ Checkmarks for passed tests
- ✗ X's for failed tests
- A summary showing total passed/failed counts

## Adding New Tests

To add new tests, modify `TestRunner.php` and add assertions in the `runTests()` method:

```php
$this->assert($condition, "Test description");
$this->assertEquals($expected, $actual, "Test description");
```

## Test Configuration

Constants and test data are stored in `TestConfig.php`. Modify this file to add:
- Test user credentials
- Mock data
- Environment settings