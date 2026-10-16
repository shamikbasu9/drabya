<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$conn = getDBConnection();

// Products table
$result = $conn->query("SELECT id, title, description, care_instructions FROM products");
$count = 0;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $title = html_entity_decode(html_entity_decode($row['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = html_entity_decode(html_entity_decode($row['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $care = html_entity_decode(html_entity_decode($row['care_instructions'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Sometimes it's encoded 3 or 4 times, let's decode it fully
        while ($title !== html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8')) {
            $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        while ($description !== html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8')) {
            $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        while ($care !== html_entity_decode($care, ENT_QUOTES | ENT_HTML5, 'UTF-8')) {
            $care = html_entity_decode($care, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $stmt = $conn->prepare("UPDATE products SET title=?, description=?, care_instructions=? WHERE id=?");
        $stmt->bind_param("sssi", $title, $description, $care, $id);
        $stmt->execute();
        $count++;
    }
}
echo "Cleaned up $count products.\n";

// Categories table
$result = $conn->query("SELECT id, name, description FROM categories");
$count = 0;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $name = $row['name'];
        $description = $row['description'];
        
        while ($name !== html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8')) {
            $name = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        while ($description !== html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8')) {
            $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $id);
        $stmt->execute();
        $count++;
    }
}
echo "Cleaned up $count categories.\n";

$conn->close();
echo "Done.\n";
