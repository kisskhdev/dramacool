<?php
require 'db_connect.php'; // ត្រូវប្រាកដថា path នេះត្រឹមត្រូវ

// --- Function to perform the backup ---
function create_backup($conn) {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        // យើងจะ backup តែตารางที่เกี่ยวข้องនឹងข้อมูลរឿង
        if ($row[0] === 'series' || $row[0] === 'episodes' || $row[0] === 'subtitles') {
            $tables[] = $row[0];
        }
    }

    $sql_script = "";
    foreach ($tables as $table) {
        // --- Get table structure ---
        $result = $conn->query("SHOW CREATE TABLE $table");
        $row = $result->fetch_row();
        $sql_script .= "\n\n-- Table structure for table `$table`\n\n";
        $sql_script .= "DROP TABLE IF EXISTS `$table`;\n"; // បន្ថែមคำสั่งลบตารางเก่า
        $sql_script .= $row[1] . ";\n\n";

        // --- Get table data ---
        $result = $conn->query("SELECT * FROM $table");
        $column_count = $result->field_count;
        
        $sql_script .= "-- Dumping data for table `$table`\n\n";
        while ($row = $result->fetch_assoc()) {
            $sql_script .= "INSERT INTO `$table` VALUES(";
            $first = true;
            foreach ($row as $value) {
                if (!$first) {
                    $sql_script .= ", ";
                }
                if ($value === null) {
                    $sql_script .= "NULL";
                } else {
                    // Escape special characters
                    $sql_script .= "'" . $conn->real_escape_string($value) . "'";
                }
                $first = false;
            }
            $sql_script .= ");\n";
        }
    }
    
    if (!empty($sql_script)) {
        // --- Set headers to trigger download ---
        $filename = "kisskh_backup_" . date("Y-m-d_H-i-s") . ".sql";
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($sql_script));
        
        // --- Output the SQL script ---
        echo $sql_script;
        exit();
    }
}

// --- Function to perform the restore ---
function restore_from_file($conn, $file_path) {
    header('Content-Type: application/json');
    $error_message = '';
    $sql_script = file_get_contents($file_path);

    if ($sql_script === false) {
        echo json_encode(['success' => false, 'message' => 'Cannot read the uploaded file.']);
        return;
    }

    // --- Disable foreign key checks before restore ---
    $conn->query('SET foreign_key_checks = 0');

    // --- Execute multi query ---
    if ($conn->multi_query($sql_script)) {
        // --- Clear the results from the buffer ---
        while ($conn->next_result()) {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        }
    } else {
        $error_message = $conn->error;
    }

    // --- Re-enable foreign key checks ---
    $conn->query('SET foreign_key_checks = 1');

    if (empty($error_message)) {
        echo json_encode(['success' => true, 'message' => 'Database has been restored successfully! Please refresh the page.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred during restore: ' . $error_message]);
    }
}

// --- Main handler ---
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'backup':
        create_backup($conn);
        break;

    case 'restore':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['restoreFile'])) {
            if ($_FILES['restoreFile']['error'] === UPLOAD_ERR_OK) {
                restore_from_file($conn, $_FILES['restoreFile']['tmp_name']);
            } else {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'File upload failed with error code: ' . $_FILES['restoreFile']['error']]);
            }
        } else {
             header('Content-Type: application/json');
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Invalid request for restore.']);
        }
        break;

    default:
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No action specified.']);
        break;
}

$conn->close();