<?php
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost"; // ឬ IP របស់ Server Database
$username = "puronkiskh";       // Username សម្រាប់ Database (default របស់ XAMPP គឺ root)
$password = "puronkiskh";           // Password សម្រាប់ Database (default របស់ XAMPP គឺ​គ្មាន)
$dbname = "puronkiskh";     // ឈ្មោះ Database ដែល​បាន​បង្កើត

// បង្កើតការតភ្ជាប់
$conn = new mysqli($servername, $username, $password, $dbname);

// កំណត់ charset ទៅជា utf8mb4
if (!$conn->set_charset("utf8mb4")) {
    die("Error loading character set utf8mb4: " . $conn->error);
}

// ពិនិត្យការតភ្ជាប់
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}
?>