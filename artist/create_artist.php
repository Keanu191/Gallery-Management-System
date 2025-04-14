<?php
/*
ACME Arts Gallery - Create Artist Module
This script handles the creation of new artists in the gallery.

Operations:
    1. Handle artist creation form
    2. Process image uploads (small/large)
    3. Validate artist details
    4. Store artist information
    5. Manage file uploads

Database Tables:
- artist (ArtistID, Name, LifeSpan, Nationality, SmallImage, LargeImage)

Access Levels:
- Access: Admin users only (RoleID = 2)
- Form validation: Required fields, image upload validation
*/
include '../functions.php';
$pdo = pdo_connect_mysql();
$msg = '';

$user = get_logged_in_user();
admin_access($user); // Perform the check to deny access

if (!empty($_POST)) {
    // Artist details
    $artist = isset($_POST['Artist_Name']) ? trim($_POST['Artist_Name']) : '';
    $lifeSpan = isset($_POST['LifeSpan']) ? trim($_POST['LifeSpan']) : 'Unknown'; // Default value for LifeSpan
    $nationality = isset($_POST['Nationality']) ? trim($_POST['Nationality']) : '';
    $smallImageData = isset($_FILES['small_image']['tmp_name']) && !empty($_FILES['small_image']['tmp_name']) 
        ? file_get_contents($_FILES['small_image']['tmp_name']) : null;
    $largeImageData = isset($_FILES['large_image']['tmp_name']) && !empty($_FILES['large_image']['tmp_name']) 
        ? file_get_contents($_FILES['large_image']['tmp_name']) : null;

    // Insert new artist record into the artist table
    if (!empty($artist)) {
        $stmt = $pdo->prepare('INSERT INTO artist (Name, LifeSpan, Nationality, SmallImage, LargeImage) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$artist, $lifeSpan, $nationality, $smallImageData, $largeImageData]);

        // Get the ID of the inserted record
        $artistId = $pdo->lastInsertId();
        $msg = 'Artist Created Successfully!';
    } else {
        $msg = 'Artist Name cannot be blank!';
    }
}

?>

<?=template_header('Create Artist')?>
<div class="content update">
    <h2>Create Artist</h2>
    <form action="create_artist.php" method="post" enctype="multipart/form-data">
        <table class="table table-centered">
            <tr>
                <td>
                    <label for="artistname">Artist</label>
                    <input type="text" name="Artist_Name" id="name" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="lifespan">LifeSpan</label>
                    <input type="text" name="LifeSpan" id="lifespan" placeholder="e.g., 1900-1980" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="nationality">Nationality</label>
                    <input type="text" name="Nationality" id="nationality" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="smallimage">Small Image</label>
                    <input type="file" name="small_image" id="smallimage" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="largeimage">Large Image</label>
                    <input type="file" name="large_image" id="largeimage" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="Create" class="btn btn-primary">
                </td>
            </tr>
        </table>
    </form>
    <?php if ($msg): ?>
        <p><?= $msg ?></p>
    <?php endif; ?>
    <?php
    ?>
</div>


<?=template_footer()?>
