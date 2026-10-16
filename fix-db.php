<?php
require_once 'config/config.php';
$conn = getDBConnection();
$sql = "ALTER TABLE product_images ADD COLUMN media_type ENUM('image', 'video') DEFAULT 'image' AFTER image_path";
if ($conn->query($sql)) {
    echo "Successfully added media_type column to product_images table.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}
$conn->close();
?>
