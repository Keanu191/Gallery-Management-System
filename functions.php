<?php
/*
This script contains functions to connect to a MySQL database,
handle user sessions, and manage user authentication.

It includes functions for: 
    1. Connecting to the database
    2. Checking if a user is logged in
    3. Getting logged-in user details
    4. Checking user access levels (member/admin)
    5. Redirecting users with messages
    6. Generating HTML headers and footers for web pages


Function reference:
- pdo_connect_mysql(): Connects to the MySQL database and returns the connection object.
- get_path(): Returns the base directory path of the current script.
- is_logged_in(): Checks if a user is logged in and returns a boolean.
- get_logged_in_user(): Returns the logged-in user's details or null if not logged in.
- member_access(): Checks if a user is logged in and redirects if not.
- admin_access(): Checks if a user is logged in and has admin role, redirects if not.
- return_to_index(): Redirects to the index page with a message.
- template_header(): Generates the HTML header for a web page, including navigation and search functionality.
- template_footer(): Generates the HTML footer for a web page.
*/
session_start();

///////////////////////////
/// Connect to Database ///
///////////////////////////
function pdo_connect_mysql() {
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = ''; 
    $DATABASE_NAME = 'painting_db';
    try {
        return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
    } catch (PDOException $exception) {
	    exit('Failed to connect to database!');
    }
}

///////////////////////
/// File path check ///
///////////////////////
function get_path() {
    // Get the base directory path of the current script
    $base_directory = realpath(dirname(__FILE__));
    $base_folder_name = basename($base_directory);
    $current_directory = basename(dirname($_SERVER['SCRIPT_FILENAME']));

    // Initialize the path
    $path = '';
    if ($base_folder_name !== $current_directory){
        $path = "../";
    } 
    return $path;
}

///////////////////////
/// Search Function ///
///////////////////////
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['searchTarget'])) {
    $target = $_GET['searchTarget'];
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if (!empty($search)) {
        // Redirect to the target page with the search query
        header("Location: " . $target . "?search=" . urlencode($search));
        exit();
    }
}

///////////////////
/// Login Check ///
///////////////////
function is_logged_in() { 
    // If user is logged in, do not display login or register buttons. 
    // display weclome "FullName" and logout button instead.
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

function get_logged_in_user() {
    if (is_logged_in()) {
        return [
            // Collect user details
            'memid' => $_SESSION["memid"],
            'email' => $_SESSION["email"], 
            'fullname' => $_SESSION["name"],
            'role' => $_SESSION["role"],
        ];
    }
    return null;
}


/////////////////////////////
/// Authentication Checks ///
/////////////////////////////
function member_access($user){
    if (!$user) {
        return_to_index('Permission Denied - Not logged in.');
    }
}

function admin_access($user) {
    if (!$user) {
        return_to_index('Permission Denied - Not logged in.');
    }

    // Check if user has the correct role
    if (!isset($user['role']) || $user['role'] !== 2) {
        return_to_index('Permission Denied - Not admin.');
    }
}

function return_to_index($message) {
    $base_path = get_path();
    // Display the HTML with a meta refresh tag for redirection - displays for 5 seconds then sends back to
    echo "<!DOCTYPE html>
          <html lang='en'>
          <head>
              <meta charset='UTF-8'>
              <meta http-equiv='refresh' content='5;url={$base_path}index.php'>
              <title>Redirecting...</title>
          </head>
          <body>
              <p>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</p>
              <p>You will be redirected in 5 seconds...</p>
          </body>
          </html>";
    exit();
}

///////////////////
/// HTML Header ///
///////////////////
function template_header($title) {
    $base_path = get_path();
    $user = get_logged_in_user();
    
    echo <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>$title</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="{$base_path}styles.css">
    </head>
    <body class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{$base_path}index.php">
                    <i class="fas fa-palette me-2"></i>ACME Arts
                </a>
EOT;

    echo <<<EOT
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="{$base_path}index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{$base_path}painting/paintings.php">Paintings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{$base_path}artist/artists.php">Artists</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
EOT;

    // Check if the user has values
    if ($user) {
        // Display admin panel option first if user has admin role
        if (isset($user['role']) && $user['role'] == 2) {
            echo <<<EOT
            <li class="nav-item">
                <a class="nav-link" href="{$base_path}admin/admin_interface.php">Admin Panel</a>
            </li>
EOT;
        }
        // If the user is logged in then display their name and a logout option
        echo <<<EOT
                        <li class="nav-item">
                            <a class="nav-link" href="{$base_path}member/member_settings.php">Account</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{$base_path}member/member_logout.php">Logout</a>
                        </li>
EOT;
    } else { // other wise show the options to register and login
        echo <<<EOT
                        <li class="nav-item">
                            <a class="nav-link" href="{$base_path}member/member_register.php">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{$base_path}member/member_login.php">Login</a>
                        </li>
EOT;
    }
    echo <<<EOT
                    </ul>
                    <form class="d-flex" action="" method="get">
                        <input class="form-control me-2" type="search" name="search" placeholder="Search:" aria-label="Search">
                        <select class="form-select me-2" name="searchTarget" aria-label="Search Target">
                            <option value="{$base_path}painting/paintings.php">Paintings</option>
                            <option value="{$base_path}artist/artists.php">Artists</option>
                        </select>
                        <button class="btn btn-secondary" type="submit">Search</button>
                    </form>
                </div>
            </div>
        </nav>
        <div class="content">
EOT;
}

function template_footer() {
    echo <<<EOT
        </div>
    </body>
</html>
EOT;
}