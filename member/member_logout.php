<?php
/*
ACME Arts Gallery - User Logout Module
This script handles user session termination.

Operations:
    1. Clear session data
    2. Destroy active session
    3. Handle logout redirection
    4. Clean up user state
    5. Manage security logout

Security Features:
- Complete session destruction
- Immediate redirection
- Prevention of session fixation
*/
// Include functions file
include '../functions.php';

// Start the session
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: ../index.php");
exit;
?>


