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
    $data[$user] = ['password'=>'','categories'=>['All'=>[]],'inventory'=>new stdClass()];
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

$categories = array_keys($data[$user]['categories']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="topbar">
  <label for="nav-toggle" class="hamburger">☰</label>
  <div style="font-weight:700;">Inventory Dashboard</div>
</div>

<input type="checkbox" id="nav-toggle" />

<div class="layout">

  <nav class="sidebar">
    <h3 style="margin-top:0;">Hello, <?php echo htmlspecialchars($user); ?>!</h3>

    <ul class="side-list">
      <?php foreach ($categories as $cat): ?>
        <li><a href="inventory.php?category=<?php echo urlencode($cat); ?>"><?php echo htmlspecialchars($cat); ?></a></li>
      <?php endforeach; ?>
    </ul>

    <a href="#addCategoryModal" class="add-link" style="display:block; margin-bottom: 10px;">+ Add Category</a>

    <div class="logout">
      <a href="logout.php" class="btn no">Logout</a>
    </div>
  </nav>

  
  <main class="main">
    <div class="controls">
      <div style="font-weight:700; font-size:20px">Categories</div>
      <div class="note">Click a category to view its products.</div>
    </div>

    <div style="background:#550202; padding:16px; border-radius:12px;">
      <p style="margin:0; color: #d2d2d2"><b>Available categories:</b></p>
      <ul>
        <?php foreach ($categories as $cat): ?>
          <li style="color:#d2d2d2"><?php echo htmlspecialchars($cat); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </main>
</div>


<div id="addCategoryModal" class="modal">
  <div class="modal-box">
    <h3>Add Category</h3>
    <form method="post" action="add_category.php">
      <input type="text" name="category" required placeholder="Category">
      <div style="margin-top:12px;">
        <button class="btn create" type="submit">Create</button>
        <button class="btn btn-back"><a href="dashboard.php" class="modal-close">Cancel</a></button>
      </div>
    </form>
  </div>
</div>

</body>
</html>