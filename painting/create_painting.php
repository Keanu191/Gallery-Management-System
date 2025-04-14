<?php
/*
ACME Arts Gallery - Create Painting Module
This script handles the creation of new paintings in the gallery.

Operations:
    1. Create new painting records
    2. Handle image uploads (small/large)
    3. Link paintings to artists
    4. Validate painting details
    5. Process media and style relationships

Database Tables:
- painting (PaintingID, Title, Year, ArtistID, MediaID, StyleID, SmallImage, LargeImage)
- artist (ArtistID, Name)
- media (MediaID, MediaType)
- style (StyleID, StyleName)

Access Levels:
- Access: Admin users only (RoleID = 2)
*/
include '../functions.php';
$pdo = pdo_connect_mysql();
$msg = '';

// Deny access if not logged in.
$user = get_logged_in_user();
admin_access($user); // Perform the check

// Check if POST data is not empty
if (!empty($_POST)) {
    // Artist
    $artist = isset($_POST['Artist_Name']) ? trim($_POST['Artist_Name']) : '';
    $artistId = null;
    $lifeSpan = '';
    $nationality = '';
    $artistId;

    if (!empty($artist)) {
        // Check if the artist already exists
        $stmt = $pdo->prepare('SELECT ArtistID, LifeSpan, Nationality FROM artist WHERE Name = ?');
        $stmt->execute([$artist]);
        $existingArtist = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingArtist) {
            // If artist exists, retrieve existing LifeSpan and Nationality
            $artistId = $existingArtist['ArtistID'];
            $lifeSpan = $existingArtist['LifeSpan'];
            $nationality = $existingArtist['Nationality'];
            $msg = 'Existing artist found, use of existing LifeSpan and Nationality!';
        } else {
            // Insert new record into the artist table with empty LifeSpan and Nationality
            $stmt = $pdo->prepare('INSERT INTO artist (Name, LifeSpan, Nationality) VALUES (?, ?, ?)');
            $stmt->execute([$artist, '', '']);
            $artistId = $pdo->lastInsertId();
            $msg = 'New artist created with blank LifeSpan and Nationality.';
        }
    } else {
        $msg = 'Artist Name cannot be blank!';
    }

    // Media data
    $media = isset($_POST['MediaID']) ? trim($_POST['MediaID']) : '';

    // Call mediaID
    if (!empty($media)) {
        // Insert new record into the artist table
        $stmt = $pdo->prepare('INSERT INTO media (mediatype) VALUES (?)');
        $stmt->execute([$media]);

         // Get the ID of the inserted record
        $mediaId = $pdo->lastInsertId();

        $msg = 'Created Successfully!';
    } else {
        $msg = 'Media cannot be blank!';
    }

   // Style data
   $style = isset($_POST['StyleName']) ? trim($_POST['StyleName']) : '';
   $styleId = null;

   if (!empty($style)) {
       // Insert new record into the style table
       $stmt = $pdo->prepare('INSERT INTO style (stylename) VALUES (?)');
       $stmt->execute([$style]);
       $styleId = $pdo->lastInsertId();
   } else {
       $msg = 'Style cannot be blank!';
   }

   // Painting data
   $title = isset($_POST['title']) ? $_POST['title'] : '';
   $year = isset($_POST['year']) ? $_POST['year'] : '';

   if ($title && $year && $artistId && $mediaId && $styleId) {
       // Handle image uploads
       $smallImageData = isset($_FILES['small_image']['tmp_name']) && !empty($_FILES['small_image']['tmp_name']) 
                         ? file_get_contents($_FILES['small_image']['tmp_name']) : null;
       $largeImageData = isset($_FILES['large_image']['tmp_name']) && !empty($_FILES['large_image']['tmp_name']) 
                         ? file_get_contents($_FILES['large_image']['tmp_name']) : null;

       if ($smallImageData && $largeImageData) {
           // Insert painting record
           $stmt = $pdo->prepare('INSERT INTO painting (Title, Year, ArtistID, MediaID, StyleID, SmallImage, LargeImage) VALUES (?, ?, ?, ?, ?, ?, ?)');
           $stmt->execute([$title, $year, $artistId, $mediaId, $styleId, $smallImageData, $largeImageData]);
           $msg = 'Painting Created Successfully!';
       } else {
           $msg = 'Please upload both small and large images.';
       }
   } else {
       $msg = 'Please provide all required details for the painting (Title, Year, Artist, Media, and Style).';
   }
}

?>
<?=template_header('Create Painting')?>
<div class="content update">
    <h2>Create Painting</h2>
    <form action="create_painting.php" method="post" enctype="multipart/form-data"> <!-- enctype="multipart/form-data" specifies which content-type to use when submitting the form. -->
        <table class="table table-centered">
            <tr>
                <td>
                    <label for="artistname">Artist</label>
                    <input type="text" name="Artist_Name" id="name" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="medianame">Media</label>
                    <input type="text" name="MediaID" id="mediatype" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="stylename">Style</label>
                    <input type="text" name="StyleName" id="stylename" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="year">Year</label>
                    <input type="number" name="year" id="year" class="form-control">
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
    <p><?=$msg?></p>
    <?php endif; ?>
</div>



<?=template_footer()?>
