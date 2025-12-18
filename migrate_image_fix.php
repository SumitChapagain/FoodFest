<?php
require_once 'config/database.php';

$conn = getDBConnection();

echo "Starting database migration...\n";

// Add image_data column
$sql = "ALTER TABLE items ADD COLUMN IF NOT EXISTS image_data LONGTEXT";
if ($conn->query($sql) === TRUE) {
    echo "✓ Added image_data column\n";
} else {
    echo "✗ Error adding image_data: " . $conn->error . "\n";
}

// Add other missing columns found in code but missing in SQL
$missing_cols = [
    "description TEXT",
    "tags VARCHAR(255)",
    "unit VARCHAR(50) DEFAULT 'plate'",
    "image_path VARCHAR(255)"
];

foreach ($missing_cols as $col) {
    $sql = "ALTER TABLE items ADD COLUMN IF NOT EXISTS $col";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Checked/Added column: $col\n";
    } else {
        echo "✗ Error checking column $col: " . $conn->error . "\n";
    }
}

echo "Migration completed.\n";
?>
