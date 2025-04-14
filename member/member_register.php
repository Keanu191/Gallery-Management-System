<?php
/*
ACME Arts Gallery - User Registration Module
This script manages new user registration process.

Operations:
    1. Process registration forms
    2. Validate user input
    3. Hash passwords securely
    4. Handle subscription options
    5. Create user accounts

Database Tables:
- users (MemberID, Email, FullName, PasswordHash, Newsletter, NewsFlash)

Security Features:
- Password hashing
- Email validation
- Input sanitization
- Duplicate prevention
*/
include '../functions.php';

// Connect to MySQL database
$pdo = pdo_connect_mysql();

// Initialize variables to store error messages
$email_err = $password_err = '';
$msg = '';

$email = '';
$fullName = '';
$password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and force email to be all lowercase
    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : ''; // Retrieve the full name
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0; // Convert checkbox to boolean value
    $newsflash = isset($_POST['newsflash']) ? 1 : 0; // Convert checkbox to boolean value

    // Validate name
    if (empty($fullName)) {
        $name_err = "Full Name cannot be empty.";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $fullName)) { // Basically regex
        $name_err = "Full Name must contain only alphabetical letters.";
    } else {
        // Capitalize the first letter of each part of the name
        $fullName = ucwords(strtolower($fullName));
    }
    
    // Check if the email is not empty and valid
    if (empty($email)) {
        $email_err = "Email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        // Check if the member already exists
        $stmt = $pdo->prepare('SELECT Email FROM users WHERE Email = ?');
        $stmt->execute([$email]);
        $existingMember = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingMember) {
            $email_err = "This email is already registered.";
        }
    }

    // Validate password
    if (empty($password)) {
        $password_err = "Password cannot be empty.";
    } elseif (!preg_match('/[A-Z]/', $password) ||
              !preg_match('/[a-z]/', $password) ||
              !preg_match('/[0-9]/', $password) ||
              !preg_match('/[\W]/', $password)) {
        $password_err = "Password must contain at least one <br> uppercase letter, one lowercase letter,<br>one number, and one special character.";
    }

    // If there are no errors, insert the new record
    if (empty($email_err) && empty($password_err)) {
        $stmt = $pdo->prepare('INSERT INTO users (FullName, Email, PasswordHash, Newsletter, NewsFlash) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $newsletter, $newsflash]);
        $msg = "Registration successful.";
    }
}
?>

<?=template_header('Register')?>

<div class="wrapper">
    <h2>Sign Up</h2>
    <p>Please fill this form to create an account.</p>
    <?php 
    if (!empty($msg)) {
        echo '<div class="alert alert-success">' . $msg . '</div>';
    }        
    ?>
    
    <form action="<?=htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post">
        <div class="form-group">
            <label for="fullNameInput">Full Name</label>
            <input type="text" name="fullName" id="fullNameInput" class="form-control" placeholder="" value="<?= htmlspecialchars($fullName) ?>">
        </div>  
        <div class="form-group">
            <label for="emailInput">Email</label>
            <input type="text" name="email" id="emailInput" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?= htmlspecialchars($email) ?>">
            <span class="invalid-feedback"><?php echo $email_err; ?></span>
        </div>    
        <div class="form-group">
            <label for="passwordInput">Password</label>
            <input type="password" name="password" id="passwordInput" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="checkbox" name="newsletter" id="newsletter">
            <label for="newsletter">Subscribe to Newsletter</label>
        </div>
        <div class="form-group">
            <input type="checkbox" name="newsflash" id="newsflash">
            <label for="newsflash">Subscribe to NewsFlash</label>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Submit">
            <input type="reset" class="btn btn-secondary ml-2" value="Reset">
        </div>
    </form>
    <p>Already have an account? <a href="member_login.php">Login here</a>.</p>
</div>

<?=template_footer()?>
