<?php
/*
ACME Arts Gallery - Delete Painting Module
This script manages painting deletion process.

Operations:
    1. Verify painting exists
    2. Handle deletion confirmation
    3. Process painting removal
    4. Clean up related records
    5. Manage image files

Database Tables:
- painting (PaintingID, Title, SmallImage, LargeImage)

Access Levels:
- Access: Admin users only (RoleID = 2)
- Confirmation required before deletion
*/
include '../functions.php';
$pdo = pdo_connect_mysql();
$msg = '';

$user = get_logged_in_user();
admin_access($user); // Perform the check to deny access

// Check that the painting ID exists
if (isset($_GET['id'])) {
    // Select the record that is going to be deleted
    $stmt = $pdo->prepare('SELECT * FROM painting WHERE paintingid = ?');
    $stmt->execute([$_GET['id']]);
    $painting = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$painting) {
        exit('A painting doesn\'t exist with that ID!');
    }
    // Make sure the user confirms before deletion
    if (isset($_GET['confirm'])) {
        if ($_GET['confirm'] == 'yes') {
            // User clicked the "Yes" button, delete record
            $stmt = $pdo->prepare('DELETE FROM painting WHERE paintingid = ?');
            $stmt->execute([$_GET['id']]);
            $msg = 'You have deleted the painting!' ;
        } else {
            // User clicked the "No" button, redirect them back to the read page
            $msg = 'Deletion cancelled!';
            // Removed the exit so that we can press a button to go back.
        }
    }
} else {
    exit('No ID specified!');
}
// HTML
?> 
<?=template_header('Delete Painting')?>

<div class="content delete">
    <h2>Delete Painting: "<?= htmlspecialchars($painting['Title']) ?>"</h2>
    <?php if ($msg): ?>
        <p><?= htmlspecialchars($msg) ?></p>
        <div class="back-button">
            <a href="paintings.php" class="btn btn-primary">Back to Paintings</a>
        </div>
    <?php else: ?>
        <!-- Ask user if they want to delete the painting -->
        <p>Are you sure you want to delete painting: <u><?= htmlspecialchars($painting['Title']) ?></u>?</p>
        <div class="yesno">
            <a href="delete_painting.php?id=<?= htmlspecialchars($painting['PaintingID']) ?>&confirm=yes">Yes</a>
            <a href="delete_painting.php?id=<?= htmlspecialchars($painting['PaintingID']) ?>&confirm=no">No</a>
        </div>
    <?php endif; ?>
</div>


<?=template_footer()?>