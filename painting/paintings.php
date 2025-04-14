<?php
/*
ACME Arts Gallery - Painting Management Module
This script manages the painting collection display and operations.

Operations:
    1. Display paintings with filtering options
    2. Search functionality for paintings
    3. CRUD operations for paintings (admin only)
    4. Image handling for artwork
    5. Linking paintings to artists

Functions:
- Hide_Actions(): Controls visibility of CRUD operations
- filter_paintings(): Handles filtering by artist/style
- display_paintings(): Renders the painting list

Database Tables:
- painting (PaintingID, Title, Year, ArtistID, MediaID, StyleID, SmallImage, LargeImage)
- media (MediaID, MediaType)
- style (StyleID, StyleName)

Access Levels:
- View: All users
- Create/Update/Delete: Admin only
*/
include '../functions.php';

// Connect to MySQL database
$pdo = pdo_connect_mysql();

// Get the page via GET request (URL param: page), default to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Get the search query if it exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : '';

// Reset filter_value if the filter_type is changed
if (isset($_GET['previous_filter_type']) && $_GET['previous_filter_type'] != $filter_type) {
    $filter_value = '';
}

 // See if the Create, Update and Delete Functions should be hidden or not
 $hideCUD = Hide_Actions();

 // method to decide whether to hide the Create, Update, and delete functions if the user logged in is Admin or not
 function Hide_Actions() { 
    // NOTE: role 1 = user, role 2 = admin

    // See what user is currently logged in (call get logged in user method from functions.php)
    $user = get_logged_in_user();

    // Default to hiding CUD actions
    $hideCUD = true;

    // check to see if the current user is an admin
    if (isset($user['role']) && $user['role'] == 2) {
        // if the user is an admin make sure the CUD functions are visible
        $hideCUD = false;
    }
   
    return $hideCUD;
 }



// Store the current filter type as the previous filter type for the next request
$previous_filter_type = $filter_type;

// Prepare the SQL statement to get records from the painting table
$sql = "
    SELECT p.*, a.Name AS ArtistName, m.MediaType AS MediaName, s.StyleName AS StyleName
    FROM painting p
    LEFT JOIN artist a ON p.ArtistID = a.ArtistID
    LEFT JOIN media m ON p.MediaID = m.MediaID
    LEFT JOIN style s ON p.StyleID = s.StyleID
";

// Add search condition if search query exists
$conditions = [];
if ($search) {
    $conditions[] = "Title LIKE :search";
}

if ($filter_type && $filter_value) {
    switch ($filter_type) {
        case 'artist':
            $conditions[] = "a.Name = :filter_value";
            break;
        case 'style':
            $conditions[] = "s.StyleName = :filter_value";
            break;
    }
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Prepare the SQL statement
$stmt = $pdo->prepare($sql);

// Bind the search parameter if it exists
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

if ($filter_type && $filter_value) {
    $stmt->bindValue(':filter_value', htmlspecialchars($filter_value), PDO::PARAM_STR);
}

// Execute the statement
$stmt->execute();

// Fetch the records
$paintingInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct artists and styles for filter options
$artists = $pdo->query("SELECT DISTINCT Name FROM artist ORDER BY Name ASC")->fetchAll(PDO::FETCH_ASSOC);
$styles = $pdo->query("SELECT DISTINCT StyleName FROM style ORDER BY StyleName ASC")->fetchAll(PDO::FETCH_ASSOC);

// Display the page
?>

<?=template_header('Paintings')?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Painting Collection</h2>
        <?php if (!$hideCUD): ?>
            <a href="create_painting.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Painting
            </a>
        <?php endif; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="paintings.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter Type</label>
                    <select name="filter_type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="artist" <?= $filter_type == 'artist' ? 'selected' : '' ?>>Artist</option>
                        <option value="style" <?= $filter_type == 'style' ? 'selected' : '' ?>>Style</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Value</label>
                    <select name="filter_value" class="form-select">
                        <option value="">Select Value</option>
                        <?php
                        if ($filter_type == 'artist') {
                            foreach ($artists as $artist) {
                                echo '<option value="' . htmlspecialchars($artist['Name']) . '" ' . ($filter_value == $artist['Name'] ? 'selected' : '') . '>' . htmlspecialchars($artist['Name']) . '</option>';
                            }
                        } elseif ($filter_type == 'style') {
                            foreach ($styles as $style) {
                                echo '<option value="' . htmlspecialchars($style['StyleName']) . '" ' . ($filter_value == $style['StyleName'] ? 'selected' : '') . '>' . htmlspecialchars($style['StyleName']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
            <!-- 29/11/2024: Add a th cell/scope to the table to make it WCAG Compliant-->
                    <th scope="col">PaintingID</th>
                    <th scope="col">Title</th>
                    <th scope="col">Year</th>
                    <th scope="col">Artist</th>
                    <th scope="col">Media</th>
                    <th scope="col">Style</th>
                    <th scope="col">Image</th>
            <!-- If user is not admin then hide the actions tab-->
                    <?php if (!$hideCUD): ?>
                    <td scope="col">Actions</td>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paintingInfo as $paintingsInfo): ?>
                <tr>
                    <td><?=$paintingsInfo['PaintingID']?></td> 
                    <td><?=$paintingsInfo['Title']?></td> 
                    <td><?=$paintingsInfo['Year']?></td>
                    <td> 
                        <a href="../artist/artists.php?search=<?=$paintingsInfo['ArtistName']?>"><?=$paintingsInfo['ArtistName']?></a>
                    </td>
                    <td><?=$paintingsInfo['MediaName']?></td>
                    <td><?=$paintingsInfo['StyleName']?></td>
                    <td>
                        <!-- 13/11/2024 create div with class of image-container -->
                    <div class="image-container">
                        <!-- Small image shown on default in the table-->
                        <img src="data:image/png;base64,<?= base64_encode($paintingsInfo['SmallImage']) ?>" alt="Small image" width="50" height="50" class="small-image">

                        <!-- Large image will be displayed when hovered over -->
                        <img src="data:image/png;base64,<?= base64_encode($paintingsInfo['LargeImage']) ?>" alt="Large image" class="large-image">
                    </div>
                </td>
                <!-- If user is not admin then hide the update and delete buttons -->
                    <?php if (!$hideCUD): ?>
                    <td class="actions">
                        <a href="update_painting.php?id=<?=$paintingsInfo['PaintingID']?>" class="edit"><i class="fas fa-pen fa-xs"></i></a>
                        <a href="delete_painting.php?id=<?=$paintingsInfo['PaintingID']?>" class="trash"><i class="fas fa-trash fa-xs"></i></a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_footer()?>
