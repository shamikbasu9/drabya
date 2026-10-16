<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$conn = getDBConnection();

$messages = [];

// Apply specific update for media_type missing column
$checkSql = "SHOW COLUMNS FROM `product_images` LIKE 'media_type'";
$result = $conn->query($checkSql);

if ($result && $result->num_rows === 0) {
    // Column does not exist, add it
    $alterSql = "ALTER TABLE `product_images` ADD COLUMN `media_type` ENUM('image', 'video') DEFAULT 'image' AFTER `image_path`";
    if ($conn->query($alterSql)) {
        $messages[] = ['type' => 'success', 'text' => "Successfully added 'media_type' column to product_images table."];
    } else {
        $messages[] = ['type' => 'danger', 'text' => "Error adding 'media_type' column: " . $conn->error];
    }
} else {
    $messages[] = ['type' => 'info', 'text' => "Database is already up to date. ('media_type' column exists)."];
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update - Drabya Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Database Schema Update</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php foreach($messages as $msg): ?>
                            <div class="alert alert-<?php echo $msg['type']; ?> mb-3">
                                <?php echo htmlspecialchars($msg['text']); ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-4">
                            <a href="product-add.php" class="btn btn-primary">Return to Add Product</a>
                            <a href="index.php" class="btn btn-outline-secondary">Go to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
