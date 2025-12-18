<?php
require_once 'config/database.php';

$conn = getDBConnection();

echo "Starting database migration...\n";

// Function to check if column exists
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// Add image_data column if it doesn't exist
if (!columnExists($conn, 'items', 'image_data')) {
    $sql = "ALTER TABLE items ADD COLUMN image_data LONGTEXT";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added image_data column\n";
    } else {
        echo "✗ Error adding image_data: " . $conn->error . "\n";
    }
} else {
    echo "✓ image_data column already exists\n";
}

// Add other missing columns
$columns = [
    'description' => 'TEXT',
    'tags' => 'VARCHAR(255)',
    'unit' => "VARCHAR(50) DEFAULT 'plate'",
    'image_path' => 'VARCHAR(255)'
];

foreach ($columns as $colName => $colDef) {
    if (!columnExists($conn, 'items', $colName)) {
        $sql = "ALTER TABLE items ADD COLUMN $colName $colDef";
        if ($conn->query($sql) === TRUE) {
            echo "✓ Added column: $colName\n";
        } else {
            echo "✗ Error adding column $colName: " . $conn->error . "\n";
        }
    } else {
        echo "✓ Column $colName already exists\n";
    }
}

echo "Migration completed.\n";
?>
