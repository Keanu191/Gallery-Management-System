<?php
/*
ACME Arts Gallery - User Tickets Module
This script handles user request tracking system.

Operations:
    1. Display user tickets
    2. Track request status
    3. Show request history
    4. Monitor pending actions
    5. Handle ticket updates

Database Tables:
- user_requests (RequestID, MemberID, RequestType, RequestStatus)

Access Levels:
- Access: Logged-in users only
- Status Codes: 0=Denied, 1=Pending, 2=Approved
*/
include '../functions.php';
// Connect to MySQL database
$pdo = pdo_connect_mysql();

$user = get_logged_in_user();
member_access($user); // Perform the check to deny access
$member_id = $user['memid']; // We need the member ID

$msg = '';

// Query the database for the user's tickets
$stmt = $pdo->prepare('SELECT RequestID, RequestType, RequestStatus FROM user_requests WHERE MemberID = ?');
$stmt->execute([$member_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?=template_header('User Tickets')?>

<h2>Your Tickets</h2>
<table>
    <thead>
        <tr>
            <th>Ticket ID</th>
            <th>Request Type</th>
            <th>Request Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tickets as $ticket): ?>
        <tr>
            <td><?=htmlspecialchars($ticket['RequestID'], ENT_QUOTES)?></td>
            <td><?=htmlspecialchars($ticket['RequestType'], ENT_QUOTES)?></td>
            <td>
                <?php
                if ($ticket['RequestStatus'] == 1) {
                    echo 'Pending';
                } elseif ($ticket['RequestStatus'] == 0) {
                    echo 'Denied';
                } elseif ($ticket['RequestStatus'] == 2) {
                    echo 'Accepted';
                }
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div>
    <a href="member_settings.php" class="btn btn-primary" style="top: -110px; left: -850px !important;">Back To Account Settings</a>
</div>

<?=template_footer()?>
