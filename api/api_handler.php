<?php
require 'db_connect.php'; 

// --- ការកំណត់ Cache ---
$cache_file = __DIR__ . '/cache/homepage_cache.json'; // ទីតាំងเก็บไฟล์ cache
$cache_time = 86400; // 24 ម៉ោងគិតជាវិនាទី (60 * 60 * 24)

// The db_connect.php file must define:
// $servername, $username, $password, $dbname
// and also create the mysqli connection object: $conn

$action = $_REQUEST['action'] ?? null;

// --- Main Router ---
if ($action) {
    header('Content-Type: application/json');
    switch($action) {
        case 'backup':
            do_backup($servername, $username, $password, $dbname);
            exit;
        
        case 'restore':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                do_restore($conn, $cache_file); // បញ្ជូន cache_file ទៅជាមួយ
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Restore must be a POST request.']);
            }
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
            break;
    }
} else {
    header('Content-Type: application/json');
    route_by_request_method($conn, $cache_file, $cache_time); // បញ្ជូន cache settings ទៅជាមួយ
}


function route_by_request_method($conn, $cache_file, $cache_time) {
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                get_single_series_and_count_view($conn, intval($_GET['id']));
            } elseif (isset($_GET['source']) && $_GET['source'] === 'admin') {
                get_all_series_for_admin($conn);
            } else {
                // *** ផ្នែកនេះគឺសម្រាប់ Homepage ដែលយើងនឹងប្រើ Cache ***
                
                // ពិនិត្យមើលថាតើមាន cache ដែលនៅមានសុពលភាពឬអត់
                if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
                    // ប្រសិនបើ cache នៅថ្មី (តិចជាង 24 ម៉ោង) ប្រើវាเลย
                    readfile($cache_file);
                    exit; // បញ្ចប់ការทำงานភ្លាមៗ
                }

                // បើមិនមាន cache ឬ cache ហួសសុពលភាព
                ob_start(); // ចាប់ផ្តើម Output Buffering ដើម្បីចាប់យកទិន្នន័យ JSON
                get_all_series_for_homepage($conn);
                $json_output = ob_get_contents(); // យកទិន្នន័យដែលបាន echo
                ob_end_clean(); // បញ្ឈប់ និងសម្អាត buffer

                // បង្កើត/សរសេរជាន់លើไฟล์ cache
                // ត្រូវប្រាកដថា folder 'cache' មានសិទ្ធិសរសេរ (writable)
                if (!is_dir(dirname($cache_file))) {
                    mkdir(dirname($cache_file), 0755, true);
                }
                file_put_contents($cache_file, $json_output);

                echo $json_output; // បញ្ជូនទិន្នន័យទៅអ្នកប្រើប្រាស់
            }
            break;
        case 'POST':
            // ពេលរក្សាទុក (Save) ត្រូវលុប cache ចោល
            save_series($conn, $cache_file);
            break;
        case 'DELETE':
            // ពេលលុប (Delete) ក៏ត្រូវលុប cache ចោលដែរ
            if (isset($_GET['id'])) {
                delete_series($conn, intval($_GET['id']), $cache_file);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
            break;
    }
}

/**
 * Функция для очистки кеша.
 * @param string $cache_file Путь к файлу кеша.
 */
function clear_cache($cache_file) {
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
}


function do_backup($servername, $username, $password, $dbname) {
    $filename = "database-backup-" . date('Y-m-d_H-i-s') . ".sql";
    header('Content-Type: application/octet-stream');
    header('Content-Transfer-Encoding: Binary');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s',
        escapeshellarg($servername),
        escapeshellarg($username),
        escapeshellarg($password),
        escapeshellarg($dbname)
    );
    passthru($command, $return_var);
    if ($return_var !== 0) {
        error_log("mysqldump command failed with return code: $return_var");
    }
}

function do_restore($conn, $cache_file) {
    if (!isset($_FILES['restoreFile']) || $_FILES['restoreFile']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        $error_message = 'No file uploaded or an upload error occurred.';
        if (isset($_FILES['restoreFile']['error'])) {
             $error_message .= ' Error code: ' . $_FILES['restoreFile']['error'];
        }
        echo json_encode(['success' => false, 'message' => $error_message]);
        return;
    }
    $file_tmp_path = $_FILES['restoreFile']['tmp_name'];
    $sql = file_get_contents($file_tmp_path);
    if ($sql === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not read the uploaded SQL file.']);
        return;
    }
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());

        if ($conn->errno) {
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Restore failed during query execution: ' . $conn->error]);
        } else {
            clear_cache($cache_file); // *** សំខាន់: លុប Cache បន្ទាប់ពី Restore ***
            echo json_encode(['success' => true, 'message' => 'Database has been restored successfully.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to start the restore process: ' . $conn->error]);
    }
}


function get_all_series_for_homepage($conn) {
    $response = [
        'latestUpdates' => [],
        'popularSeries' => []
    ];
    $sql_latest = "SELECT s.id, s.title, s.type, s.status, s.country, s.release_year, s.image_url, s.description, s.category, s.is_featured, COUNT(e.id) as episode_count 
            FROM series s 
            LEFT JOIN episodes e ON s.id = e.series_id 
            GROUP BY s.id
            ORDER BY s.updated_at DESC
            LIMIT 15";
    $result_latest = $conn->query($sql_latest);
    if ($result_latest) {
        while($row = $result_latest->fetch_assoc()) {
            $response['latestUpdates'][] = $row;
        }
    }
        $sql_popular = "SELECT s.id, s.title, s.type, s.status, s.country, s.release_year, s.image_url, s.description, s.category, s.is_featured, s.view_count, s.updated_at, COUNT(e.id) as episode_count 
            FROM series s 
            LEFT JOIN episodes e ON s.id = e.series_id 
            GROUP BY s.id
            ORDER BY s.view_count DESC, s.updated_at DESC";
    $result_popular = $conn->query($sql_popular);
    if ($result_popular) {
        while($row = $result_popular->fetch_assoc()) {
            $response['popularSeries'][] = $row;
        }
    }
    echo json_encode($response);
}

// Function save_series ត្រូវបានកែសម្រួលដើម្បីទទួល $cache_file
function save_series($conn, $cache_file) {
    $conn->begin_transaction(); 
    try {
        // --- យកទិន្នន័យថ្មីពី Form ---
        $id = isset($_POST['seriesId']) && !empty($_POST['seriesId']) ? intval($_POST['seriesId']) : null;
        $title = $_POST['seriesTitle'];
        $type = $_POST['seriesType'];
        $status = $_POST['seriesStatus'];
        $country = $_POST['seriesCountry'];
        $release_year = !empty($_POST['seriesReleaseYear']) ? $_POST['seriesReleaseYear'] : null;
        $description = $_POST['seriesDescription'];
        $image_url = $_POST['seriesImage'];
        $category = $_POST['seriesCategory'];
        $is_featured = isset($_POST['seriesFeatured']) && $_POST['seriesFeatured'] === '1' ? 1 : 0;

        if ($id) { // --- ផ្នែកនេះសម្រាប់ Update រឿងដែលមានស្រាប់ ---

            // *** STEP 1: ទាញយកទិន្នន័យចាស់ពី Database ***
            $stmt_fetch = $conn->prepare("SELECT * FROM series WHERE id = ?");
            $stmt_fetch->bind_param("i", $id);
            $stmt_fetch->execute();
            $result = $stmt_fetch->get_result();
            $old_data = $result->fetch_assoc();
            $stmt_fetch->close();

            if (!$old_data) {
                throw new Exception("Series not found.");
            }

            // *** STEP 2: ប្រៀបធៀបទិន្នន័យដើម្បីរកមើល "Major Change" ***
            $major_change_detected = false;
            
            // STEP 2.1: ពិនិត្យមើល Field ធម្មតា (លើកលែងតែ is_featured)
            if ($old_data['title'] != $title ||
                $old_data['type'] != $type ||
                $old_data['status'] != $status ||
                $old_data['country'] != $country ||
                $old_data['release_year'] != $release_year ||
                $old_data['description'] != $description ||
                $old_data['image_url'] != $image_url ||
                $old_data['category'] != $category) {
                $major_change_detected = true;
            }

            // STEP 2.2: ពិនិត្យមើលចំនួន Episode (នេះជាចំណុចកែប្រែសំខាន់)
            if (!$major_change_detected) { // ធ្វើការពិនិត្យនេះ លុះត្រាតែមិនទាន់រកឃើញ Change
                // រាប់ចំនួន Episode ដែលមានក្នុង Database ស្រាប់
                $stmt_count_eps = $conn->prepare("SELECT COUNT(id) as episode_count FROM episodes WHERE series_id = ?");
                $stmt_count_eps->bind_param("i", $id);
                $stmt_count_eps->execute();
                $result_count = $stmt_count_eps->get_result();
                $row_count = $result_count->fetch_assoc();
                $old_episode_count = $row_count['episode_count'];
                $stmt_count_eps->close();

                // រាប់ចំនួន Episode ដែលបាន Submit ពី Form
                $new_episode_count = isset($_POST['episodes']) && is_array($_POST['episodes']) ? count($_POST['episodes']) : 0;
                
                // បើចំនួនមិនដូចគ្នា មានន័យថាមានការបន្ថែម/លុប Episode
                if ($old_episode_count != $new_episode_count) {
                    $major_change_detected = true;
                }
                // ចំណាំ៖ ការកែប្រែ URL ឬចំណងជើង Episode ក៏គួរតែចាត់ទុកជា Major Change ដែរ
                // ប៉ុន្តែដើម្បីឲ្យកូដងាយស្រួលយល់ យើងផ្អែកលើការផ្លាស់ប្តូរចំនួនសិន
                // ហើយម៉្យាងទៀត កូដរបស់អ្នកតែងតែលុបហើយបញ្ចូល Episode ថ្មីទាំងអស់
                // ដូច្នេះការកែប្រែ Title/URL នឹងត្រូវបញ្ចូលជាទិន្នន័យថ្មីទាំងអស់ដែរ ដែលធ្វើឲ្យ Cache ត្រូវលុបចោល (ត្រឹមត្រូវ)
                // ប៉ុន្តែមិនប៉ះពាល់ដល់ updated_at ដោយសារ Logic នេះ
            }

            // *** STEP 3: សម្រេចចិត្តថាតើត្រូវ Update ពេលវេលាឬអត់ ***
            $sql_update_time = "updated_at = updated_at"; // Default: មិន Update ពេលវេលា
            if ($major_change_detected) {
                $sql_update_time = "updated_at = NOW()"; // Nếu có thay đổi lớn, update thời gian
            }
            
            $sql = "UPDATE series SET title = ?, type = ?, status = ?, country = ?, release_year = ?, description = ?, image_url = ?, category = ?, is_featured = ?, " . $sql_update_time . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssisssii", $title, $type, $status, $country, $release_year, $description, $image_url, $category, $is_featured, $id);
            $stmt->execute();
            $stmt->close();
            $series_id = $id;

            // លុប episodes និង subtitles ចាស់ៗចោលដើម្បីបញ្ចូលថ្មី
            $stmt_del_subs = $conn->prepare("DELETE FROM subtitles WHERE episode_id IN (SELECT id FROM episodes WHERE series_id = ?)");
            $stmt_del_subs->bind_param("i", $series_id);
            $stmt_del_subs->execute();
            $stmt_del_subs->close();
            
            $stmt_del_eps = $conn->prepare("DELETE FROM episodes WHERE series_id = ?");
            $stmt_del_eps->bind_param("i", $series_id);
            $stmt_del_eps->execute();
            $stmt_del_eps->close();

        } else { // --- ផ្នែកនេះសម្រាប់បញ្ចូលរឿងថ្មី ---
            $stmt = $conn->prepare("INSERT INTO series (title, type, status, country, release_year, description, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssisssi", $title, $type, $status, $country, $release_year, $description, $image_url, $category, $is_featured);
            $stmt->execute();
            $series_id = $stmt->insert_id;
            $stmt->close();
        }

        // បញ្ចូល episodes និង subtitles ថ្មី (កូដផ្នែកនេះរក្សាទុកដដែល)
        if (isset($_POST['episodes']) && is_array($_POST['episodes'])) {
            $stmt_ep = $conn->prepare("INSERT INTO episodes (series_id, title, video_url, video_type, episode_order) VALUES (?, ?, ?, ?, ?)");
            $stmt_sub = $conn->prepare("INSERT INTO subtitles (episode_id, file_url, label, is_default) VALUES (?, ?, ?, ?)");
            foreach ($_POST['episodes'] as $index => $ep_data) {
                if(empty($ep_data['title']) || empty($ep_data['url'])) continue;
                
                $stmt_ep->bind_param("isssi", $series_id, $ep_data['title'], $ep_data['url'], $ep_data['type'], $index);
                $stmt_ep->execute();
                $episode_id = $stmt_ep->insert_id;
                
                if (isset($ep_data['subtitles']) && is_array($ep_data['subtitles'])) {
                    foreach ($ep_data['subtitles'] as $sub_data) {
                        if (!empty($sub_data['label']) && !empty($sub_data['url'])) {
                            $is_default = 0;
                            $stmt_sub->bind_param("issi", $episode_id, $sub_data['url'], $sub_data['label'], $is_default);
                            $stmt_sub->execute();
                        }
                    }
                }
            }
            $stmt_ep->close();
            $stmt_sub->close();
        }
        
        $conn->commit();
        clear_cache($cache_file);
        echo json_encode(['success' => true, 'message' => 'Series saved successfully!', 'id' => $series_id]);

    } catch (Exception $e) {
        $conn->rollback(); 
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
    }
}

// Function delete_series ត្រូវបានកែសម្រួលដើម្បីទទួល $cache_file
function delete_series($conn, $id, $cache_file) {
    $conn->begin_transaction();
    try {
        // ... (កូដខាងក្នុង function នេះរក្សាទុកដដែល) ...
        $stmt_del_subs = $conn->prepare("DELETE FROM subtitles WHERE episode_id IN (SELECT id FROM episodes WHERE series_id = ?)");
        $stmt_del_subs->bind_param("i", $id);
        $stmt_del_subs->execute();
        $stmt_del_subs->close();
        $stmt_del_eps = $conn->prepare("DELETE FROM episodes WHERE series_id = ?");
        $stmt_del_eps->bind_param("i", $id);
        $stmt_del_eps->execute();
        $stmt_del_eps->close();
        $stmt_del_series = $conn->prepare("DELETE FROM series WHERE id = ?");
        $stmt_del_series->bind_param("i", $id);
        $stmt_del_series->execute();
        $stmt_del_series->close();

        $conn->commit();
        
        // *** សំខាន់: លុប Cache ចោលបន្ទាប់ពីការលុបទិន្នន័យ ***
        clear_cache($cache_file);

        echo json_encode(['success' => true, 'message' => 'Series and all related data deleted successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete series: ' . $e->getMessage()]);
    }
}


// Các hàm còn lại giữ nguyên không thay đổi
function get_all_series_for_admin($conn) {
    $sql = "SELECT s.id, s.title, s.description, s.image_url, s.category, s.is_featured, s.updated_at, COUNT(e.id) as episode_count 
            FROM series s 
            LEFT JOIN episodes e ON s.id = e.series_id 
            GROUP BY s.id
            ORDER BY s.updated_at DESC";
    $result = $conn->query($sql);
    $series = [];
    while($row = $result->fetch_assoc()) {
        $series[] = $row;
    }
    echo json_encode($series);
}

function get_single_series_and_count_view($conn, $id) {
    if (!isset($_GET['source']) || $_GET['source'] !== 'admin_edit') {
        $stmt_view = $conn->prepare("UPDATE series SET view_count = view_count + 1, updated_at = updated_at WHERE id = ?");
        $stmt_view->bind_param("i", $id);
        $stmt_view->execute();
        $stmt_view->close();
    }
    
    $stmt = $conn->prepare("SELECT id, title, type, status, country, release_year, description, image_url, category, is_featured, view_count FROM series WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $series = $result->fetch_assoc();
    $stmt->close();

    if ($series) {
        $stmt_ep = $conn->prepare("SELECT id, title, video_url, video_type FROM episodes WHERE series_id = ? ORDER BY episode_order ASC");
        $stmt_ep->bind_param("i", $id);
        $stmt_ep->execute();
        $result_ep = $stmt_ep->get_result();
        $episodes = [];
        $stmt_sub = $conn->prepare("SELECT file_url, label FROM subtitles WHERE episode_id = ?");
        while($row_ep = $result_ep->fetch_assoc()) {
            $stmt_sub->bind_param("i", $row_ep['id']);
            $stmt_sub->execute();
            $result_sub = $stmt_sub->get_result();
            $subtitles = [];
            while($row_sub = $result_sub->fetch_assoc()){
                $subtitles[] = $row_sub;
            }
            $row_ep['subtitles'] = $subtitles;
            $episodes[] = $row_ep;
        }
        $series['episodes'] = $episodes;
        $stmt_ep->close();
        $stmt_sub->close();
    }
    
    echo json_encode($series);
}

$conn->close();
?>