<?php
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Update Categories - Drabya</title>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' rel='stylesheet'>";
echo "</head><body class='bg-light p-4'><div class='container'><div class='card shadow-sm p-4'>";
echo "<h2 class='fw-bold mb-4 text-primary'><i class='fas fa-sync-alt me-2'></i>Updating Category Content</h2>";

try {
    $conn = getDBConnection();
    
    $categoryUpdates = [
        [
            'name' => 'Coffee Mugs',
            'slug' => 'coffee-mugs',
            'description' => "Your morning coffee just got a glow-up. Whether you're a minimalist or a maximalist, our premium mugs are designed to match your mood. Grab your favorite and start the day with a smile.",
            'icon' => 'fa-mug-hot'
        ],
        [
            'name' => 'Fridge Magnets',
            'slug' => 'fridge-magnets',
            'description' => "Small items, big personality. Turn your fridge into a mood board with our vibrant, high-quality magnets. They’re the perfect little treat for yourself or a friend.",
            'icon' => 'fa-magnet'
        ],
        [
            'name' => 'Coasters',
            'slug' => 'coasters',
            'description' => "Protect your desk, but make it fashionable. Our coasters add a pop of color to your space while keeping things clean. Functional art for your home or office.",
            'icon' => 'fa-circle-dot'
        ],
        [
            'name' => 'T-shirt',
            'slug' => 'tshirts',
            'description' => "Coming soon",
            'icon' => 'fa-tshirt'
        ]
    ];
    
    foreach ($categoryUpdates as $cat) {
        // Check if exists by slug or name
        $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ? OR name = ?");
        $stmt->bind_param("ss", $cat['slug'], $cat['name']);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $catId = $row['id'];
            $updateStmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?");
            $updateStmt->bind_param("sssi", $cat['name'], $cat['description'], $cat['icon'], $catId);
            $updateStmt->execute();
            $updateStmt->close();
            echo "<div class='alert alert-success py-2 mb-2'><i class='fas fa-check-circle me-2'></i>Updated category: <strong>{$cat['name']}</strong></div>";
        } else {
            $insertStmt = $conn->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("ssss", $cat['name'], $cat['slug'], $cat['description'], $cat['icon']);
            $insertStmt->execute();
            $insertStmt->close();
            echo "<div class='alert alert-info py-2 mb-2'><i class='fas fa-plus-circle me-2'></i>Inserted category: <strong>{$cat['name']}</strong></div>";
        }
        $stmt->close();
    }
    
    echo "<h4 class='mt-4 mb-3'>Current Categories in Database:</h4>";
    echo "<div class='table-responsive'><table class='table table-bordered table-striped'>";
    echo "<thead class='table-dark'><tr><th>Icon</th><th>Name</th><th>Slug</th><th>Description</th></tr></thead><tbody>";
    
    $result = $conn->query("SELECT * FROM categories ORDER BY id ASC");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='text-center'><i class='fas " . htmlspecialchars($row['icon']) . " fa-lg text-primary'></i></td>";
        echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
        echo "<td><code>" . htmlspecialchars($row['slug']) . "</code></td>";
        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table></div>";
    
    echo "<div class='mt-4'><a href='index.php' class='btn btn-primary rounded-pill px-4 me-2'>Visit Homepage</a><a href='products.php' class='btn btn-outline-primary rounded-pill px-4'>Visit Products</a></div>";
    
    $conn->close();
} catch (Exception $e) {
    echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></div></body></html>";
