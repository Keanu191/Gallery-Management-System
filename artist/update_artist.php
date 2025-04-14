<?php
/*
ACME Arts Gallery - Update Artist Module
This script handles modifications to existing artist records.

Operations:
    1. Load existing artist data
    2. Process form submissions
    3. Handle image updates
    4. Validate modifications
    5. Update database records

Database Tables:
- artist (ArtistID, Name, LifeSpan, Nationality, SmallImage, LargeImage)

Access Levels:
- Access: Admin users only (RoleID = 2)
- Form validation: Optional fields, image validation
*/
include '../functions.php';
$pdo = pdo_connect_mysql();
$msg = '';

$user = get_logged_in_user();
admin_access($user); // Perform the check to deny access

// Check if id exists
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $artistID = $_GET['id'];

    // Fetch artist details
    $stmt = $pdo->prepare('SELECT * FROM artist WHERE ArtistID = ?');
    $stmt->execute([$artistID]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artist) {
        exit('Artist doesn\'t exist with that ID!');
    }

    // Check if POST data is not empty (form submission)
    if (!empty($_POST)) {
        $name = isset($_POST['name']) ? $_POST['name'] : $artist['Name'];
        $lifeSpan = isset($_POST['lifeSpan']) ? $_POST['lifeSpan'] : $artist['LifeSpan'];
        $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : $artist['Nationality'];

        // Check if new images are uploaded
        $smallImageUploaded = isset($_FILES['small_image']['tmp_name']) && !empty($_FILES['small_image']['tmp_name']);
        $largeImageUploaded = isset($_FILES['large_image']['tmp_name']) && !empty($_FILES['large_image']['tmp_name']);

        // Allowed file types for images
        $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];

        // If image files are uploaded, validate and prepare for update
        if ($smallImageUploaded || $largeImageUploaded) {
            if (
                ($smallImageUploaded && !in_array($_FILES['small_image']['type'], $allowedTypes)) ||
                ($largeImageUploaded && !in_array($_FILES['large_image']['type'], $allowedTypes))
            ) {
                $msg = 'Invalid file type. Only PNG, JPEG, and GIF are allowed.';
            } else {
                // Get new image data if uploaded, or use existing
                $smallImageData = $smallImageUploaded ? file_get_contents($_FILES['small_image']['tmp_name']) : $artist['SmallImage'];
                $largeImageData = $largeImageUploaded ? file_get_contents($_FILES['large_image']['tmp_name']) : $artist['LargeImage'];

                // Update artist details with images
                $stmt = $pdo->prepare('UPDATE artist SET Name = ?, LifeSpan = ?, Nationality = ?, SmallImage = ?, LargeImage = ? WHERE ArtistID = ?');
                $stmt->execute([$name, $lifeSpan, $nationality, $smallImageData, $largeImageData, $artistID]);
                $msg = 'The artist has been updated successfully!';
            }
        } else {
            // Update without images if none are uploaded
            $stmt = $pdo->prepare('UPDATE artist SET Name = ?, LifeSpan = ?, Nationality = ? WHERE ArtistID = ?');
            $stmt->execute([$name, $lifeSpan, $nationality, $artistID]);
            $msg = 'The artist has been updated successfully!';
        }
    }
} else {
    exit('No Artist ID specified!');
}
?>

<!-- HTML form -->
<?=template_header('Update Artist')?>

<div class="content update">
    <h2>Update Artist: <?= htmlspecialchars($artist['Name']) ?></h2>
    <form action="update_artist.php?id=<?= htmlspecialchars($artist['ArtistID']) ?>" method="post" enctype="multipart/form-data">
        <table class="table table-centered">
            <tr>
                <td>
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="<?= htmlspecialchars($artist['Name']) ?>" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="lifeSpan">LifeSpan</label>
                    <input type="text" name="lifeSpan" id="lifeSpan" value="<?= htmlspecialchars($artist['LifeSpan']) ?>" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="nationality">Nationality</label>
                    <input type="text" name="nationality" id="nationality" value="<?= htmlspecialchars($artist['Nationality']) ?>" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="small_image">Small Image (Leave blank to keep existing)</label>
                    <input type="file" name="small_image" id="small_image" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="large_image">Large Image (Leave blank to keep existing)</label>
                    <input type="file" name="large_image" id="large_image" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="Update" class="btn btn-primary">
                </td>
            </tr>
            <tr>
                <!-- 14/11/24 add a button to the update artist page to allow the user to return to the arist page -->
                <td>
                    <a href="artists.php">
                    <button type="button" class="btn btn-primary">Return to artists</button>
                    </a>
                </td>
            </tr>
        </table>
    </form>
    <?php if ($msg): ?>
        <p><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>
</div>

<?=template_footer()?>