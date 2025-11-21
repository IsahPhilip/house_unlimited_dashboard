<?php
require_once 'inc/config.php';

try {
    $sql = "ALTER TABLE activity_log ADD COLUMN timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    if ($db->query($sql) === TRUE) {
        echo "Column 'timestamp' added to 'activity_log' table successfully.\n";
    } else {
        echo "Error adding column: " . $db->error . "\n";
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
} finally {
    $db->close();
}
?>
