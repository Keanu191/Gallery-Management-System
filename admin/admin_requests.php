<?php
/*
ACME Arts Gallery - Request Management Module
This script handles administrative approval/denial of user requests.

Operations:
    1. View pending user requests
    2. Process subscription changes
    3. Handle account deletion requests
    4. Update request statuses
    5. Manage user notifications

Database Tables:
- user_requests (RequestID, MemberID, RequestType, RequestStatus)
- users (MemberID, Email, Newsletter, NewsFlash)

Access Levels:
- Access: Admin users only (RoleID = 2)
- Status Codes: 0=Denied, 1=Pending, 2=Approved
*/
include '../functions.php';

$pdo = pdo_connect_mysql();
$msg = '';

// Get and clear any message from the session
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

$user = get_logged_in_user();
admin_access($user); // Perform the check

// Check if the request is coming from a form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = (int) $_POST['request_id'];
    $action = $_POST['action'];

    // Check the action and update the request status accordingly
    if ($action == 'approve') {
        // Fetch request details
        $stmt = $pdo->prepare('SELECT * FROM user_requests WHERE RequestID = ?');
        $stmt->execute([$request_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($request) {

            if ($request['RequestType'] == 'unsubscribe_newsletter') {
                // Update the user's Newsletter subscription
                $stmt = $pdo->prepare('UPDATE users SET Newsletter = 0 WHERE MemberID = ?');
                $stmt->execute([$request['MemberID']]);
                $_SESSION['msg'] = "Member has been unsubscribed from the Newsletter.";

            } elseif ($request['RequestType'] == 'unsubscribe_newsflash') {
                // Update the user's NewsFlash subscription
                $stmt = $pdo->prepare('UPDATE users SET NewsFlash = 0 WHERE MemberID = ?');
                $stmt->execute([$request['MemberID']]);
                $_SESSION['msg'] = "Member has been unsubscribed from the NewsFlash.";

            } elseif ($request['RequestType'] == 'delete_account') {
                // Check if the request is trying to delete the logged-in user's account
                if ($request['MemberID'] == $user['memid']) {
                    // Prevent deleting the logged-in user
                    $_SESSION['msg'] = "!!! DENIED !!! You cannot delete your own account!";
                    $stmt = $pdo->prepare('UPDATE user_requests SET RequestStatus = 0 WHERE RequestID = ?');
                    $stmt->execute([$request_id]);
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    // Delete the request itself
                    $stmt = $pdo->prepare('DELETE FROM user_requests WHERE RequestID = ?');
                    $stmt->execute([$request_id]);
                    // Delete the user's account
                    $stmt = $pdo->prepare('DELETE FROM users WHERE MemberID = ?');
                    $stmt->execute([$request['MemberID']]);
                    $_SESSION['msg'] = "User account has been deleted.";
                }

            }
            // Update the request status to approved (RequestStatus = 2)
            $stmt = $pdo->prepare('UPDATE user_requests SET RequestStatus = 2 WHERE RequestID = ?');
            $stmt->execute([$request_id]);
        }
    } elseif ($action == 'deny') {
        // Update request status to denied (RequestStatus = 0)
        $stmt = $pdo->prepare('UPDATE user_requests SET RequestStatus = 0 WHERE RequestID = ?');
        $stmt->execute([$request_id]);
        $_SESSION['msg'] = "Request has been denied.";
    }

    // Redirect to the same page to show the message
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Collect all pending requests
$stmt = $pdo->prepare('
    SELECT ur.RequestID, ur.MemberID, ur.RequestType, u.Email 
    FROM user_requests ur
    JOIN users u ON ur.MemberID = u.MemberID
    WHERE ur.RequestStatus = 1
');
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_header('View Requests')?>

<div class="container">

<div>
    <a href="admin_interface.php" class="btn btn-primary" style="left: -220px !important;">Back To Admin Dashboard</a>
</div>

    <h1>Manage Requests</h1>

    <!-- Display any messages -->
    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Display list of pending requests -->
    <h2>Pending Requests</h2>
    <table class="table table-bordered">
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Member ID</th>
            <th>Email</th>
            <th>Request Type</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requests as $request): ?>
        <tr>
            <td><?= htmlspecialchars($request['RequestID']) ?></td>
            <td><?= htmlspecialchars($request['MemberID']) ?></td>
            <td><?= htmlspecialchars($request['Email']) ?></td>
            <td><?= htmlspecialchars($request['RequestType']) ?></td>
            <td>
                <!-- Form to approve or deny a request -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['RequestID']) ?>">
                    <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                    <button type="submit" name="action" value="deny" class="btn btn-danger">Deny</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>



<?=template_footer()?>
