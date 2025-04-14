<?php
/*
ACME Arts Gallery - Administration Interface
This script provides administrative functions for site management.

Operations:
    1. View all registered members
    2. Delete member accounts
    3. Process user requests
    4. Monitor system statistics
    5. Access administrative functions

Database Tables:
- users (MemberID, Email, FullName, RoleID)
- user_requests (RequestID, MemberID, RequestType, RequestStatus)

Access Levels:
- Access: Admin users only (RoleID = 2)
*/
include '../functions.php';
$pdo = pdo_connect_mysql();
$msg = '';

// Check if there's a message in the session and display it
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']); // Clear the message after displaying
}

$user = get_logged_in_user();
admin_access($user); // Perform the check

// Collect the total number of pending requests
$stmt = $pdo->prepare('SELECT COUNT(*) AS pending_count FROM user_requests WHERE RequestStatus = 1');
$stmt->execute();
$pending_requests = $stmt->fetch(PDO::FETCH_ASSOC)['pending_count'];

// Fetch all members from the database
$members = $pdo->query('SELECT MemberID, Email, FullName, RoleID FROM users')->fetchAll(PDO::FETCH_ASSOC);

// Process deletion of a member by email if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_email'])) {
    $delete_email = trim($_POST['delete_email']);
    
    // Check if the logged-in user is trying to delete their own account
    if ($delete_email == $user['email']) {
        $_SESSION['msg'] = "You cannot delete your own account!";
    } else {
        // Prepare the deletion statement
        $stmt = $pdo->prepare('DELETE FROM users WHERE Email = ?');
        $stmt->execute([$delete_email]);
        
        $_SESSION['msg'] = $stmt->rowCount() ? "Member with email $delete_email has been deleted." : "No member found with that email.";
    }

    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>

<?=template_header('Admin Page')?>

<div class="container">
    <h1>Admin Dashboard</h1>
    
    <!-- Display total number of pending requests -->
    <div class="alert alert-info">
        <p>There are <strong><?= htmlspecialchars($pending_requests) ?></strong> pending user requests awaiting approval.</p>
<!--I know that doing styles in HTML is frowned upon but the View Pending Requests button on the admin dashboard is the same button tag (a.btn.btn-primary) as the others on the account
web page so i have to do it to this individual button as its gone left per what i've done to the other buttons in the css file-->
        <a href="admin_requests.php" class="btn btn-primary" style="left: -2px;">View Pending Requests</a>
    </div>
    
    <!-- Display list of all members -->
    <h2>View All Members</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Member ID</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member): ?>
            <tr>
                <td><?= htmlspecialchars($member['MemberID']) ?></td>
                <td><?= htmlspecialchars($member['Email']) ?></td>
                <td><?= htmlspecialchars($member['FullName']) ?></td>
                <td><?= htmlspecialchars($member['RoleID']) == 2 ? 'Admin' : 'Member' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Form to delete a member by email -->
    <h2>Delete Member by Email</h2>
    <?php if ($msg): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="delete_email">Enter Member's Email:</label>
            <input type="email" name="delete_email" id="delete_email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger" style="margin-top: 10px !important;">Delete Member</button>
    </form>
</div>

<?=template_footer()?>
