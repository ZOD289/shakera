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

if (!isset($data[$user]['inventory']) || !is_array($data[$user]['inventory'])) $data[$user]['inventory'] = [];

$orig = isset($_GET['name']) ? $_GET['name'] : null;
if ($orig === null || !isset($data[$user]['inventory'][$orig])) { header('Location: dashboard.php'); exit(); }

$item = $data[$user]['inventory'][$orig];
$categories = array_keys($data[$user]['categories']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newname = trim($_POST['name']);
    $qty = max(0,(int)$_POST['quantity']);
    $price = (float)$_POST['price'];
    $category = trim($_POST['category'] ?? $item['category'] ?? 'All');


    $imageName = $item['image'] ?? '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
        $allowed = ['image/jpeg','image/png','image/jpg'];
        if (in_array($_FILES['image']['type'], $allowed)) {
            if (!is_dir('uploads')) mkdir('uploads',0755,true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = time().'_'.bin2hex(random_bytes(4)).'.'.($ext ?: 'jpg');
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/'.$imageName);
        }
    }

    if ($newname === '') { $error = "Invalid name."; }
    else {

        if ($newname !== $orig && isset($data[$user]['inventory'][$newname])) {
            // merge into existing
            $data[$user]['inventory'][$newname]['quantity'] += $qty;
            $data[$user]['inventory'][$newname]['price'] = $price;
            $data[$user]['inventory'][$newname]['category'] = $category;
            if ($imageName !== '') $data[$user]['inventory'][$newname]['image'] = $imageName;
            unset($data[$user]['inventory'][$orig]);
        
        } elseif ($newname !== $orig) {
            $data[$user]['inventory'][$newname] = [
                'quantity' => $qty,
                'price' => $price,
                'category' => $category,
                'image' => $imageName
            ];
            unset($data[$user]['inventory'][$orig]);

        } else {
            $data[$user]['inventory'][$orig]['quantity'] = $qty;
            $data[$user]['inventory'][$orig]['price'] = $price;
            $data[$user]['inventory'][$orig]['category'] = $category;
            $data[$user]['inventory'][$orig]['image'] = $imageName;
        }

        if (!isset($data[$user]['categories'][$category])) $data[$user]['categories'][$category] = [];
        if (!in_array($newname, $data[$user]['categories'][$category])) {
            $data[$user]['categories'][$category][] = $newname;
        }

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        header('Location: inventory.php?category='.urlencode($category)); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container" style="margin-top:75px">
  <h2>Edit Product</h2>
  <form method="post" action="" enctype="multipart/form-data">
    <label class="label">Product name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($orig); ?>" required>

    <label class="label">Quantity</label>
    <input type="number" name="quantity" min="0" value="<?php echo (int)$item['quantity']; ?>" required>

    <label class="label">Price</label>
    <input type="number" name="price" step="0.01" min="0" value="<?php echo number_format($item['price'],2,'.',''); ?>" required>

    <label class="label">Category</label>
    <select name="category">
      <?php foreach ($categories as $c): ?>
        <option value="<?php echo htmlspecialchars($c); ?>" <?php if (($item['category'] ?? '') === $c) echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
      <?php endforeach; ?>
    </select>

    <label class="label">Replace Image (optional)</label>
    <input type="file" name="image" accept="image/png,image/jpeg">

    <div style="margin-top:12px;">
      <button class="btn yes" type="submit">Save</button>
      <a class="btn no" href="inventory.php?category=<?php echo urlencode($item['category'] ?? 'All'); ?>">Cancel</a>
    </div>
  </form>

  <p style="color:#b91c1c;"><?php echo $error ?? ''; ?></p>
</div>
</body>
</html>