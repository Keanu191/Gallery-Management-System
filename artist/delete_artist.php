<?php
/*
ACME Arts Gallery - Delete Artist Module
This script manages the deletion of artists from the gallery.

Operations:
    1. Verify artist exists
    2. Handle confirmation dialog
    3. Process artist deletion
    4. Manage orphaned paintings
    5. Update related records

Database Tables:
- artist (ArtistID, Name, LifeSpan, Nationality)
- painting (PaintingID, ArtistID) - For handling orphaned paintings

Access Levels:
- Access: Admin users only (RoleID = 2)
- Confirmation required before deletion
*/
include '../functions.php';
$pdo = pdo_connect_mysql();
$msg = '';

$user = get_logged_in_user();
admin_access($user); // Perform the check to deny access

// Check that the artist ID exists
if (isset($_GET['id'])) {
    // Select the record that is going to be deleted
    $stmt = $pdo->prepare('SELECT * FROM artist WHERE ArtistID = ?');
    $stmt->execute([$_GET['id']]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$artist) {
        exit('An artist doesn\'t exist with that ID!');
    }
    // Make sure the user confirms before deletion
    if (isset($_GET['confirm'])) {
        if ($_GET['confirm'] == 'yes') {
            // Update paintings to orphan them by setting ArtistID to 0
            $stmt = $pdo->prepare('UPDATE painting SET ArtistID = 0 WHERE ArtistID = ?');
            $stmt->execute([$_GET['id']]);

            // User clicked the "Yes" button, delete record
            $stmt = $pdo->prepare('DELETE FROM artist WHERE ArtistID = ?');
            $stmt->execute([$_GET['id']]);
            $msg = 'You have deleted the artist!';
        } else {
            // User clicked the "No" button, redirect them back to the read page
            $msg = 'Deletion cancelled!';
        }
    }
} else {
    exit('No ID specified!');
}
// HTML
?> 
<?=template_header('Delete Artist')?>

<div class="content delete">
    <h2>Delete Artist: "<?= htmlspecialchars($artist['Name']) ?>"</h2>
    <?php if ($msg): ?>
        <p><?= htmlspecialchars($msg) ?></p>
        <div class="back-button">
            <a href="artists.php" class="btn btn-primary">Back to Artists</a>
        </div>
    <?php else: ?>
        <!-- Ask user if they want to delete the artist -->
        <p>Are you sure you want to delete Artist <u><?= htmlspecialchars($artist['Name']) ?></u>?</p>
        <div class="yesno">
            <a href="delete_artist.php?id=<?= htmlspecialchars($artist['ArtistID']) ?>&confirm=yes">Yes</a>
            <a href="delete_artist.php?id=<?= htmlspecialchars($artist['ArtistID']) ?>&confirm=no">No</a>
        </div>
    <?php endif; ?>
</div>

<?=template_footer()?>
