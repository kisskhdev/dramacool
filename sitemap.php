<?php
// File: /htdocs/kiskh.com/sitemap.php

require 'api/db_connect.php'; // ផ្លូវ​ទៅ​កាន់ db_connect.php ត្រូវ​តែ​ត្រឹមត្រូវ

header("Content-Type: text/xml; charset=utf-8");

// --- START: ផ្នែក​ដែល​ត្រូវ​កែប្រែ ---
// យក​ឈ្មោះ​ដែន​ដែល​កំពុង​ស្នើសុំ​ដោយ​ស្វ័យប្រវត្តិ
$current_host = $_SERVER['HTTP_HOST']; 
$base_url = 'https://' . $current_host;
// --- END: ផ្នែក​ដែល​ត្រូវ​កែប្រែ ---


echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// ទំព័រដើម (Homepage) - ប្រើ $base_url ថ្មី
echo '<url>';
echo '  <loc>' . $base_url . '/</loc>'; // ឧ. https://kissme.cam/
echo '  <changefreq>daily</changefreq>';
echo '  <priority>1.0</priority>';
echo '</url>';

// ទំព័រ Category - ប្រើ $base_url ថ្មី
echo '<url>';
echo '  <loc>' . $base_url . '/category.php</loc>'; // ឧ. https://kissme.cam/category.php
echo '  <changefreq>daily</changefreq>';
echo '  <priority>0.8</priority>';
echo '</url>';

$sql = "SELECT id, updated_at FROM series ORDER BY updated_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // បង្កើត URL របស់​រឿង ដោយ​ប្រើ $base_url ថ្មី
        $movie_url = $base_url . '/player.php?id=' . $row['id'];
        $last_modified_date = date('c', strtotime($row['updated_at']));

        echo '<url>';
        echo '  <loc>' . htmlspecialchars($movie_url) . '</loc>';
        echo '  <lastmod>' . $last_modified_date . '</lastmod>';
        echo '  <changefreq>weekly</changefreq>';
        echo '  <priority>0.6</priority>';
        echo '</url>';
    }
}

echo '</urlset>';
$conn->close();
?>