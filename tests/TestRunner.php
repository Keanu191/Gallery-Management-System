<?php
/*
ACME Arts Gallery - Test Execution Module
This script manages the test suite execution.

Operations:
    1. Run unit tests
    2. Generate test reports
    3. Validate core functions
    4. Check database operations
    5. Test authentication

Test Coverage:
- Authentication testing
- Database connectivity
- Session handling
- Access control
- Utility functions
*/
require_once '../functions.php';

class TestRunner {
    private $passCount = 0;
    private $failCount = 0;
    private $results = [];

    public function __construct() {
        // Output HTML header
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Test Results</title>
            <link rel="stylesheet" href="test-styles.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        </head>
        <body>
        <div class="test-results">
            <div class="test-header">
                <h1>Test Results</h1>
            </div>';
    }

    public function assert($condition, $message) {
        if ($condition) {
            $this->passCount++;
            $this->results[] = [
                'status' => 'pass',
                'message' => $message
            ];
            echo "<div class='test-item'>
                    <span class='test-icon test-pass'><i class='fas fa-check-circle'></i></span>
                    <span class='test-message'>$message</span>
                  </div>";
        } else {
            $this->failCount++;
            $this->results[] = [
                'status' => 'fail',
                'message' => $message
            ];
            echo "<div class='test-item'>
                    <span class='test-icon test-fail'><i class='fas fa-times-circle'></i></span>
                    <span class='test-message'>$message</span>
                  </div>";
        }
    }

    public function assertEquals($expected, $actual, $message) {
        $this->assert($expected === $actual, $message);
    }

    public function runTests() {
        echo "\nRunning Tests...\n\n";
        
        // Test is_logged_in()
        $_SESSION = [];
        $this->assert(!is_logged_in(), "is_logged_in() returns false when not logged in");

        $_SESSION["loggedin"] = true;
        $this->assert(is_logged_in(), "is_logged_in() returns true when logged in");

        // Test get_logged_in_user()
        $_SESSION = [];
        $this->assert(get_logged_in_user() === null, "get_logged_in_user() returns null when not logged in");

        $_SESSION = [
            "loggedin" => true,
            "memid" => 1,
            "email" => "test@test.com",
            "name" => "Test User",
            "role" => 1
        ];
        $user = get_logged_in_user();
        $this->assert(
            $user['memid'] === 1 && 
            $user['email'] === "test@test.com" && 
            $user['fullname'] === "Test User" && 
            $user['role'] === 1,
            "get_logged_in_user() returns correct user data"
        );

        // Test database connection
        try {
            $pdo = pdo_connect_mysql();
            $this->assert($pdo instanceof PDO, "Database connection successful");
        } catch (PDOException $e) {
            $this->assert(false, "Database connection failed: " . $e->getMessage());
        }

        // Test path generation
        $path = get_path();
        $this->assert(is_string($path), "get_path() returns a string");

        // Print summary
        echo "\nTest Summary:\n";
        echo "Passed: {$this->passCount}\n";
        echo "Failed: {$this->failCount}\n";
        echo "Total: " . ($this->passCount + $this->failCount) . "\n";
    }

    public function __destruct() {
        // Output summary
        echo "<div class='test-summary'>
                <div class='summary-item summary-pass'>
                    <i class='fas fa-check'></i> Passed: {$this->passCount}
                </div>
                <div class='summary-item summary-fail'>
                    <i class='fas fa-times'></i> Failed: {$this->failCount}
                </div>
              </div>
            </div>
        </body>
        </html>";
    }
}

// Run the tests
$runner = new TestRunner();
$runner->runTests();