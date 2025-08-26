<?php
// Define a logging function
function write_log($message) {
    $logFile = 'api_log.txt';
    $timestamp = date("Y-m-d H:i:s");
    // Using FILE_APPEND to add to the file, not overwrite it
    // Using LOCK_EX for safe writing
    file_put_contents($logFile, "[$timestamp] " . print_r($message, true) . "\n", FILE_APPEND | LOCK_EX);
}

// Clear log for new test session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('api_log.txt', ''); 
}

write_log("--- NEW REQUEST ---");
write_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

$settingsFile = 'settings.json';
$uploadDir = '../uploads/'; 

function handle_upload($file_key, $uploadDir, &$settings, $setting_name) {
    write_log("Handling upload for key: $file_key");
    
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        write_log("File '$file_key' exists and was uploaded successfully. Details:");
        write_log($_FILES[$file_key]);

        if (!is_dir($uploadDir)) {
            write_log("Upload directory does not exist. Attempting to create: $uploadDir");
            if (!mkdir($uploadDir, 0755, true)) {
                $error_message = "Failed to create upload directory.";
                write_log("ERROR: " . $error_message);
                return $error_message;
            }
        }
        
        if (!is_writable(realpath('../uploads'))) {
            $error_message = "Upload directory is not writable.";
            write_log("ERROR: " . $error_message);
            return $error_message;
        }

        if (!empty($settings[$setting_name])) {
            $oldFilePath = '../' . $settings[$setting_name]; 
            if (file_exists($oldFilePath)) {
                write_log("Attempting to delete old file: $oldFilePath");
                @unlink($oldFilePath);
            }
        }
        
        $tmpName = $_FILES[$file_key]['tmp_name'];
        $safeFilename = preg_replace('/[^A-Za-z0-9.\-_]/', '', basename($_FILES[$file_key]['name']));
        $newFileName = uniqid() . '-' . $safeFilename;
        $destination = $uploadDir . $newFileName;
        write_log("Attempting to move uploaded file to: $destination");

        if (move_uploaded_file($tmpName, $destination)) {
            write_log("SUCCESS: File moved successfully.");
            $settings[$setting_name] = 'uploads/' . $newFileName;
            return null;
        } else {
            $error_message = "Failed to move uploaded file '$file_key'.";
            write_log("ERROR: " . $error_message);
            return $error_message;
        }
    } else if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] !== UPLOAD_ERR_NO_FILE) {
        $error_message = "Upload error for '$file_key'. Code: " . $_FILES[$file_key]['error'];
        write_log("ERROR: " . $error_message);
        return $error_message;
    }
    write_log("No new file uploaded for key: $file_key");
    return null;
}

ob_start();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    ob_end_clean(); 
    header('Content-Type: application/json');
    if (file_exists($settingsFile)) { echo file_get_contents($settingsFile); } 
    else { echo json_encode(new stdClass()); }
    exit;
    
} elseif ($method === 'POST') {
    write_log("POST data received:");
    write_log($_POST);
    write_log("FILES data received:");
    write_log($_FILES);

    $settings = [];
    if (file_exists($settingsFile)) {
        $fileContent = file_get_contents($settingsFile);
        if (!empty($fileContent)) { $settings = json_decode($fileContent, true); }
        if ($settings === null) $settings = [];
    }

    $settings['google_analytics'] = $_POST['google_analytics'] ?? '';
    $settings['google_console'] = $_POST['google_console'] ?? '';

    $logoError = handle_upload('logo', $uploadDir, $settings, 'logo_url');
    $faviconError = handle_upload('favicon', $uploadDir, $settings, 'favicon_url');
    
    $errors = array_filter([$logoError, $faviconError]);
    
    ob_end_clean(); 
    header('Content-Type: application/json');

    if (!empty($errors)) {
        http_response_code(500);
        $response = ['success' => false, 'message' => implode(' ', $errors)];
        write_log("FINAL RESPONSE (ERROR):");
        write_log($response);
        echo json_encode($response);
        exit;
    }

    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        $response = ['success' => true, 'message' => 'Settings saved successfully.'];
        write_log("FINAL RESPONSE (SUCCESS):");
        write_log($response);
        echo json_encode($response);
    } else {
        http_response_code(500);
        $response = ['success' => false, 'message' => 'Failed to write settings to file.'];
        write_log("FINAL RESPONSE (ERROR - FAILED TO WRITE JSON):");
        write_log($response);
        echo json_encode($response);
    }
    exit;

} else {
    ob_end_clean(); 
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}
?>