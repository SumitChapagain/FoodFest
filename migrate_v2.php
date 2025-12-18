<?php
/**
 * Database Migration V2
 * Adds description, tags, and unit columns to items table
 */

require_once 'config/database.php';

echo "Starting migration v2...\n";

$conn = getDBConnection();

// Add description column
try {
    $conn->query("ALTER TABLE items ADD COLUMN description TEXT AFTER name");
    echo "Added 'description' column.\n";
} catch (Exception $e) {
    echo "Column 'description' might already exist or error: " . $e->getMessage() . "\n";
}

// Add tags column
try {
    $conn->query("ALTER TABLE items ADD COLUMN tags VARCHAR(255) DEFAULT NULL AFTER description");
    echo "Added 'tags' column.\n";
} catch (Exception $e) {
    echo "Column 'tags' might already exist or error: " . $e->getMessage() . "\n";
}

// Add unit column
try {
    $conn->query("ALTER TABLE items ADD COLUMN unit VARCHAR(50) DEFAULT 'plate' AFTER price");
    echo "Added 'unit' column.\n";
} catch (Exception $e) {
    echo "Column 'unit' might already exist or error: " . $e->getMessage() . "\n";
}

echo "Migration completed.\n";
?>
