<?php
session_start();

if (!isset($_SESSION['user'])) { 
    header('Location: login.php'); 
    exit(); 
}

$file = 'database.json';
$data = json_decode(file_get_contents($file), true);
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat = trim($_POST['category']);
    if ($cat === '') { header('Location: dashboard.php'); exit(); }

    if (!isset($data[$user]['categories']) || !is_array($data[$user]['categories'])) {
        $data[$user]['categories'] = [];
    }
    if (!isset($data[$user]['categories'][$cat])) {
        $data[$user]['categories'][$cat] = []; // list of product names (optional)
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
}
header('Location: dashboard.php');
exit();