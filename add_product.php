<?php
session_start();
if (!isset($_SESSION['user'])) { 
    header('Location: login.php'); 
    exit(); 
}

$file = 'database.json';
if (!file_exists($file)) file_put_contents($file, json_encode([]));
$data = json_decode(file_get_contents($file), true);
$user = $_SESSION['user'];

if (!isset($data[$user])) { 
    header('Location: dashboard.php'); 
    exit(); 
}

if (!isset($data[$user]['inventory']) || !is_array($data[$user]['inventory'])) $data[$user]['inventory'] = [];
if (!isset($data[$user]['categories']) || !is_array($data[$user]['categories'])) $data[$user]['categories'] = ['All'=>[]];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $qty = max(1, (int)$_POST['quantity']);
    $price = (float)$_POST['price'];
    $category = trim($_POST['category'] ?? 'All');
    $return_category = $_POST['return_category'] ?? $category;

    if (!isset($data[$user]['categories'][$category])) $data[$user]['categories'][$category] = [];


    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $up = $_FILES['image'];
        if ($up['error'] === 0) {
            $allowed = ['image/jpeg','image/png','image/jpg'];
            if (in_array($up['type'], $allowed)) {
                if (!is_dir('uploads')) mkdir('uploads', 0755, true);
                $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
                $imageName = time().'_'.bin2hex(random_bytes(4)).'.'.($ext ?: 'jpg');
                move_uploaded_file($up['tmp_name'], 'uploads/'.$imageName);
            }
        }
    }


    if (isset($data[$user]['inventory'][$name])) {
        $data[$user]['inventory'][$name]['quantity'] += $qty;
        $data[$user]['inventory'][$name]['price'] = $price;
        $data[$user]['inventory'][$name]['category'] = $category;
        
    if ($imageName !== '') $data[$user]['inventory'][$name]['image'] = $imageName;
    } else {
        $data[$user]['inventory'][$name] = [
            'quantity' => $qty,
            'price' => $price,
            'category' => $category,
            'image' => $imageName
        ];
    }

    if (!in_array($name, $data[$user]['categories'][$category])) {
        $data[$user]['categories'][$category][] = $name;
    }

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

    header('Location: inventory.php?category='.urlencode($return_category));
    exit();
}

header('Location: inventory.php'); 
exit();