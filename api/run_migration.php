<?php
/**
 * Migration script to add mesas and turnos tables
 */
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "Starting migration...\n";

    $sql = file_get_contents('../sql/migration_mesas_turnos.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        try {
            $db->exec($stmt);
            echo "Executed: " . substr($stmt, 0, 50) . "...\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'already exists') === false &&
                strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
?>