<?php
require_once 'inc/config.php';

$sql = file_get_contents('db/payments_schema.sql');

if ($db->multi_query($sql)) {
    echo "SQL script executed successfully.";
} else {
    echo "Error executing SQL script: " . $db->error;
}

// It's important to close the connection after a multi_query
while ($db->next_result()) {;}

$db->close();
?>