<?php
// កំណត់ API Key របស់អ្នកនៅទីនេះ
define('TMDB_API_KEY', '05902896074695709d7763505bb88b4d'); // <--- ជំនួស API KEY របស់អ្នកនៅត្រង់នេះ!

header('Content-Type: application/json');

// ត្រូវតែមាន query ឬ id
if (!isset($_GET['query']) && !isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing search query or ID']);
    exit;
}

// ត្រូវតែមាន type
if (!isset($_GET['type']) || !in_array($_GET['type'], ['movie', 'tv'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing type. Must be "movie" or "tv".']);
    exit;
}

$type = $_GET['type'];
$language = 'en-US'; 

// កំណត់ URL ដោយផ្អែកលើថាតើជាការស្វែងរក (search) ឬការទាញយកព័ត៌មានលម្អិត (details)
if (isset($_GET['id'])) {
    // ទាញយកព័ត៌មានលម្អិត
    $id = intval($_GET['id']);
    $base_url = "https://api.themoviedb.org/3/{$type}/{$id}";
} else {
    // ស្វែងរក
    $query = urlencode($_GET['query']);
    $base_url = "https://api.themoviedb.org/3/search/{$type}";
    $base_url .= "?query={$query}";
}

$api_connector = strpos($base_url, '?') === false ? '?' : '&';
$full_url = $base_url . $api_connector . 'api_key=' . TMDB_API_KEY . '&language=' . $language;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $full_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'MyMovieApp/1.0'); 

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code === 401) {
    http_response_code(401);
    echo json_encode(['error' => 'TMDB API Key មិនត្រឹមត្រូវ']);
    curl_close($ch);
    exit;
}

if ($http_code === 404) {
    http_response_code(404);
    echo json_encode(['error' => 'រកមិនឃើញទិន្នន័យសម្រាប់ ID ដែលបានផ្ដល់ឱ្យទេ']);
    curl_close($ch);
    exit;
}

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
} else {
    http_response_code($http_code);
    echo $response;
}

curl_close($ch);
?>