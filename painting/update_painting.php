<?php
/*
ACME Arts Gallery - Update Painting Module
This script handles painting modification process.

Operations:
    1. Load existing painting data
    2. Process form submissions
    3. Handle image updates
    4. Update related records
    5. Validate modifications

Database Tables:
- painting (PaintingID, Title, Year, ArtistID, MediaID, StyleID)
- artist (ArtistID, Name)
- media (MediaID, MediaType)
- style (StyleID, StyleName)

Access Levels:
- Access: Admin users only (RoleID = 2)
*/

include '../functions.php';
$pdo = pdo_connect_mysql();
$msg = '';

$user = get_logged_in_user();
admin_access($user); // Perform the check to deny access

//Changes has been done so that the prefill textboxes for the update are fully functional
// Check if PaintingID exists
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $paintingID = $_GET['id'];

    // Fetch painting details
    $stmt = $pdo->prepare('SELECT * FROM painting WHERE PaintingID = ?');
    $stmt->execute([$paintingID]);
    $painting = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$painting) {
        exit('Painting doesn\'t exist with that ID!');
    } else {
        // Fetch artist, media, and style names based on IDs
        $stmt = $pdo->prepare('SELECT Name FROM artist WHERE ArtistID = ?');
        $stmt->execute([$painting['ArtistID']]);
        $artist = $stmt->fetchColumn() ?: '';

        $stmt = $pdo->prepare('SELECT MediaType FROM media WHERE MediaID = ?');
        $stmt->execute([$painting['MediaID']]);
        $media = $stmt->fetchColumn() ?: '';

        $stmt = $pdo->prepare('SELECT StyleName FROM style WHERE StyleID = ?');
        $stmt->execute([$painting['StyleID']]);
        $style = $stmt->fetchColumn() ?: '';
    }

    // Check if POST data is not empty (i.e., form submission)
    if (!empty($_POST)) {
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $artist = isset($_POST['Artist_Name']) ? $_POST['Artist_Name'] : '';
        $media = isset($_POST['MediaID']) ? $_POST['MediaID'] : '';
        $style = isset($_POST['StyleName']) ? $_POST['StyleName'] : '';

        // Ensure the artist exists or create a new one
        if (!empty($artist)) {
            $stmt = $pdo->prepare('SELECT ArtistID FROM artist WHERE name = ?');
            $stmt->execute([$artist]);
            $artistInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$artistInfo) {
                $stmt = $pdo->prepare('INSERT INTO artist (name) VALUES (?)');
                $stmt->execute([$artist]);
                $artistID = $pdo->lastInsertId();
            } else {
                $artistID = $artistInfo['ArtistID'];
            }
        } else {
            $artistID = $painting['ArtistID'];
        }

        // Ensure the media exists or create a new one
        if (!empty($media)) {
            $stmt = $pdo->prepare('SELECT MediaID FROM media WHERE mediatype = ?');
            $stmt->execute([$media]);
            $mediaInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$mediaInfo) {
                $stmt = $pdo->prepare('INSERT INTO media (mediatype) VALUES (?)');
                $stmt->execute([$media]);
                $mediaID = $pdo->lastInsertId();
            } else {
                $mediaID = $mediaInfo['MediaID'];
            }
        } else {
            $mediaID = $painting['MediaID'];
        }

        // Ensure the style exists or create a new one
        if (!empty($style)) {
            $stmt = $pdo->prepare('SELECT StyleID FROM style WHERE stylename = ?');
            $stmt->execute([$style]);
            $styleInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$styleInfo) {
                $stmt = $pdo->prepare('INSERT INTO style (stylename) VALUES (?)');
                $stmt->execute([$style]);
                $styleID = $pdo->lastInsertId();
            } else {
                $styleID = $styleInfo['StyleID'];
            }
        } else {
            $styleID = $painting['StyleID'];
        }


        // Fixed the issue with update images , if no image needs to be updated, leave the existing one .
        
        if (isset($_FILES['small_image']['tmp_name']) && !empty($_FILES['small_image']['tmp_name']) &&
    isset($_FILES['large_image']['tmp_name']) && !empty($_FILES['large_image']['tmp_name'])) {
    // Allowed file types
    $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];

    if (!in_array($_FILES['small_image']['type'], $allowedTypes) || !in_array($_FILES['large_image']['type'], $allowedTypes)) {
        $msg = 'Invalid file type. Only PNG, JPEG, and GIF are allowed.';
    } else {
        // Update painting with images
        $stmt = $pdo->prepare('UPDATE painting SET Title = ?, Year = ?, ArtistID = ?, MediaID = ?, StyleID = ?, SmallImage = ?, LargeImage = ? WHERE PaintingID = ?');
        $smallImageData = file_get_contents($_FILES['small_image']['tmp_name']);
        $largeImageData = file_get_contents($_FILES['large_image']['tmp_name']);
        $stmt->execute([$title, $year, $artistID, $mediaID, $styleID, $smallImageData, $largeImageData, $paintingID]);
        $msg = 'The painting has been updated successfully!';
    }
} else {
    // Update painting without images if no files were uploaded
    $stmt = $pdo->prepare('UPDATE painting SET Title = ?, Year = ?, ArtistID = ?, MediaID = ?, StyleID = ? WHERE PaintingID = ?');
    $stmt->execute([$title, $year, $artistID, $mediaID, $styleID, $paintingID]);
    $msg = 'The painting has been updated successfully without changing images!';
}
    }
} else {
    exit('No Painting ID specified!');
}
?>

<!-- HTML form -->
<!-- Form Prefill: Used htmlspecialchars() -->
<?=template_header('Update Painting')?>

<div class="content update">
    <h2>Update Painting: "<?= htmlspecialchars($painting['Title']) ?>"</h2>
    <form action="update_painting.php?id=<?= htmlspecialchars($painting['PaintingID']) ?>" method="post" enctype="multipart/form-data">
        <table class="table table-centered">
            <tr>
                <td>
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" value="<?= htmlspecialchars($painting['Title']) ?>" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="year">Year</label>
                    <input type="text" name="year" id="year" value="<?= htmlspecialchars($painting['Year']) ?>" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="artistname">Artist</label>
                    <input type="text" name="Artist_Name" id="name" value="<?= htmlspecialchars($artist) ?>" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="medianame">Media</label>
                    <input type="text" name="MediaID" id="mediatype" value="<?= htmlspecialchars($media) ?>" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="stylename">Style</label>
                    <input type="text" name="StyleName" id="stylename" value="<?= htmlspecialchars($style) ?>" class="form-control">
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
                <!-- 14/11/24 add a button to the update painting page to allow the user to return to the painting page -->
                <td>
                    <a href="paintings.php">
                    <button type="button" class="btn btn-primary">Return to paintings</button>
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
