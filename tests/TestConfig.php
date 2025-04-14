<?php
/*
ACME Arts Gallery - Test Configuration Module
This script provides test environment configuration.

Configuration:
    1. Test user credentials
    2. Mock data settings
    3. Test environment variables
    4. Database test config
    5. Authentication test data

Test Data:
- Mock user accounts
- Test environment settings
- Database connection details
*/
define('TEST_USER', [
    'memid' => 1,
    'email' => 'test@test.com',
    'name' => 'Test User',
    'role' => 1
]);

define('TEST_ADMIN', [
    'memid' => 2,
    'email' => 'admin@test.com',
    'name' => 'Admin User',
    'role' => 2
]);