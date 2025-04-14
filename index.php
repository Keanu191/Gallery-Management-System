<?php
include 'functions.php';

// Connect to MySQL database
$pdo = pdo_connect_mysql();

// Get the page via GET request (URL param: page), if none exists default the page to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Get the search query if it exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare the base SQL query
$sql = "
    SELECT p.*, a.Name AS ArtistName, m.MediaType AS MediaName, s.StyleName AS StyleName
    FROM painting p
    LEFT JOIN artist a ON p.ArtistID = a.ArtistID
    LEFT JOIN media m ON p.MediaID = m.MediaID
    LEFT JOIN style s ON p.StyleID = s.StyleID
";

// Add search condition if search query exists
if ($search) {
    $sql .= " WHERE p.Title LIKE :search";
}


// Prepare the SQL statement
$stmt = $pdo->prepare($sql);


// Bind the search parameter if it exists
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

// Execute the statement
$stmt->execute();

// Fetch the records
$paintingInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of paintings for pagination
$num_paintings = $pdo->query("SELECT COUNT(*) FROM painting" . ($search ? " WHERE Title LIKE '%$search%'" : ''))->fetchColumn();


// Display the page
?>

<?=template_header('ACME Arts Gallery')?>

<div class="content">
    <div class="hero-section text-center py-5">
        <h1 class="display-4 mb-4">Welcome to ACME Arts Gallery</h1>
        <p class="lead mb-4">Discover our collection of masterpieces from renowned artists</p>
        <div class="btn-group">
            <a href="painting/paintings.php" class="btn btn-primary btn-lg me-2">Browse Paintings</a>
            <a href="artist/artists.php" class="btn btn-primary btn-lg me-2">Meet the Artists</a>
        </div>
    </div>

    <div class="featured-section py-5">
        <h2 class="text-center mb-4">Featured Paintings</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            // Display up to 3 random paintings
            shuffle($paintingInfo);
            $featuredPaintings = array_slice($paintingInfo, 0, 3);
            
            foreach ($featuredPaintings as $painting): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if ($painting['SmallImage']): ?>
                            <img src="data:image/png;base64,<?= base64_encode($painting['SmallImage']) ?>" 
                                 class="card-img-top" alt="<?= htmlspecialchars($painting['Title']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($painting['Title']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    By <?= htmlspecialchars($painting['ArtistName']) ?> (<?= htmlspecialchars($painting['Year']) ?>)
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="stats-section py-5 bg-light">
        <div class="row text-center">
            <div class="col-md-4">
                <h3 class="text-primary"><?= $num_paintings ?></h3>
                <p>Artworks in Collection</p>
            </div>
            <div class="col-md-4">
                <h3 class="text-primary">
                    <?php 
                    $artist_count = $pdo->query("SELECT COUNT(*) FROM artist")->fetchColumn();
                    echo $artist_count;
                    ?>
                </h3>
                <p>Featured Artists</p>
            </div>
            <div class="col-md-4">
                <h3 class="text-primary">
                    <?php 
                    $style_count = $pdo->query("SELECT COUNT(*) FROM style")->fetchColumn();
                    echo $style_count;
                    ?>
                </h3>
                <p>Artistic Styles</p>
            </div>
        </div>
    </div>
</div>

<?=template_footer()?>
