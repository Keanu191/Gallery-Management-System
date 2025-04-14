<?php
/*
ACME Arts Gallery - User Authentication Module
This script handles user login functionality.

Operations:
    1. Authenticate user credentials
    2. Create user sessions
    3. Handle login errors
    4. Redirect authenticated users
    5. Validate input data

Database Tables:
- users (MemberID, Email, PasswordHash, RoleID)

Security Features:
- Password hashing
- Input sanitization
- Session management
- SQL injection prevention
*/
include '../functions.php';

// Connect to MySQL database
$pdo = pdo_connect_mysql();

// Check if the user is already logged in, if yes then redirect to index page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: ../index.php");
    exit;
}

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if email is empty
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($email_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT MemberID, FullName, Email, PasswordHash, RoleID, Newsletter, NewsFlash FROM users WHERE email = ?";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(1, $email, PDO::PARAM_STR);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Check if email exists, if yes then verify password
                if($stmt->rowCount() == 1){                    
                    // Bind result variables
                    if($row = $stmt->fetch()){
                        $memberid = $row["MemberID"];
                        $email = $row["Email"];
                        $name = $row["FullName"];
                        $hashed_password = $row["PasswordHash"];
                        $role = $row["RoleID"];
                        $letter = $row["Newsletter"];
                        $flash = $row["NewsFlash"];
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["memid"] = $memberid;
                            $_SESSION["email"] = $email;
                            $_SESSION["name"] = $name;
                            $_SESSION["role"] = $role;
                            
                            // Redirect user to welcome page
                            header("location: ../index.php");
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else{
                    // Email doesn't exist, display a generic error message
                    $login_err = "Invalid email or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            unset($stmt);
        }
    }
    
    // Close connection
    unset($pdo);
}

?>

<?=template_header('Login')?>

<div class="wrapper">
    <h2>Login</h2>
    <p>Please fill in your credentials to login.</p>
    
    <?php 
    if(!empty($login_err)){
        echo '<div class="alert alert-danger">' . $login_err . '</div>';
    }        
    ?>

    <!-- 29/11/2024: Add ID to label to make it WCAG compliant -->
    <form action="<?=htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post">
        <div class="form-group">
            <label for="emailInput">Email</label>
            <input type="text" name="email" id="emailInput" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?= $email ?>">
            <span class="invalid-feedback"><?php echo $email_err; ?></span>
        </div>    
        <div class="form-group">
            <label for="passwordInput">Password</label>
            <input type="password" name="password" id="passwordInput" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Login">
        </div>
        <p>Don't have an account? <a href="member_register.php">Sign up now</a>.</p>
    </form>
</div>

<?=template_footer()?>
