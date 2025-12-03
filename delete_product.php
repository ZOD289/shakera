<?php
session_start();

if (!isset($_SESSION['user'])) { 
    header('Location: login.php'); 
    exit(); 
}

$file = 'database.json';
$data = json_decode(file_get_contents($file), true);
$user = $_SESSION['user'];

$name = isset($_GET['name']) ? $_GET['name'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : 'All';

if ($name && isset($data[$user]['inventory'][$name])) {
    $img = $data[$user]['inventory'][$name]['image'] ?? '';
    if ($img && file_exists('uploads/'.$img)) @unlink('uploads/'.$img);

    unset($data[$user]['inventory'][$name]);
    
    foreach ($data[$user]['categories'] as $cat => &$list) {
        if (($k = array_search($name, $list)) !== false) {
            array_splice($list, $k, 1);
        }
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}
header('Location: inventory.php?category='.urlencode($category));
exit();