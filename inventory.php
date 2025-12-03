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

$category = isset($_GET['category']) ? $_GET['category'] : 'All';

if (!isset($data[$user]['categories'])) $data[$user]['categories'] = ['All'=>[]];
if (!isset($data[$user]['inventory']) || !is_array($data[$user]['inventory'])) $data[$user]['inventory'] = [];

$products = []; 
foreach ($data[$user]['inventory'] as $pname => $prod) {
    if ($category === 'All' || (isset($prod['category']) && $prod['category'] === $category)) {
        $products[$pname] = $prod;
    }
}

if (isset($_GET['plus'])) {
    $p = $_GET['plus'];
    if (isset($data[$user]['inventory'][$p])) {
        $data[$user]['inventory'][$p]['quantity'] += 1;
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    header('Location: inventory.php?category='.urlencode($category)); 
    exit();
}
if (isset($_GET['minus'])) {
    $p = $_GET['minus'];
    if (isset($data[$user]['inventory'][$p])) {
        $data[$user]['inventory'][$p]['quantity'] -= 1;
        if ($data[$user]['inventory'][$p]['quantity'] <= 0) {
            unset($data[$user]['inventory'][$p]);
        }
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    header('Location: inventory.php?category='.urlencode($category)); 
    exit();
}


$categories = array_keys($data[$user]['categories']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory — <?php echo htmlspecialchars($category); ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="topbar">
  <label for="nav-toggle" class="hamburger">☰</label>
  <div style="font-weight:750;">Inventory — <?php echo htmlspecialchars($category); ?></div>
</div>

<input type="checkbox" id="nav-toggle" />
<div class="layout">
  <nav class="sidebar">
    <h3 style="margin-top:0;">Hello, <?php echo htmlspecialchars($user); ?>!</h3>
    <ul class="side-list">
      <?php foreach ($categories as $cat): ?>
        <li><b><a href="inventory.php?category=<?php echo urlencode($cat); ?>"><?php echo htmlspecialchars($cat); ?></a></b></li>
      <?php endforeach; ?>
    </ul>

    <a href="#addCategoryModal" class="add-link" style="display:block; margin-bottom:8px;">+ Add Category</a>
    <a href="#addModal" class="add-link" style="display:block; margin-bottom:8px;">+ Add Product</a>

    <div class="logout">
      <a href="logout.php" class="btn no">Logout</a>
    </div>
  </nav>

  <main class="main">
    <div class="controls">
      <div style="font-weight:700; font-size:23px;"><?php echo htmlspecialchars($category); ?></div>
      <div class="note">Products in this category</div>
    </div>

    <div class="products">
      <?php if (empty($products)): ?>
        <div style="grid-column:1/-1; text-align:center; padding:28px; background:#fff; border-radius:12px;">No products yet.</div>
      <?php else: ?>
        <?php foreach ($products as $pname => $prod): ?>
          <div class="card">
            <div class="actions">
              <a class="action-btn" style="background:#0ea5e9;" href="edit_product.php?name=<?php echo urlencode($pname); ?>">Edit</a>
              <a class="action-btn" style="background:#ef4444;" href="delete_product.php?name=<?php echo urlencode($pname); ?>&category=<?php echo urlencode($category); ?>">Delete</a>
            </div>

            <?php
              $imgPath = isset($prod['image']) && $prod['image'] !== '' && file_exists('uploads/'.$prod['image']) ? 'uploads/'.htmlspecialchars($prod['image']) : 'uploads/placeholder.png';
            ?>
            <img class="image" src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($pname); ?>">

            <div class="card-title"><?php echo htmlspecialchars($pname); ?></div>
            <p><b>Price:</b> ₱<?php echo number_format($prod['price'],2); ?></p>
            <p class="note"><b>Quantity:</b></p>
            <div class="qty-row">
              <a class="small-btn minus" href="inventory.php?category=<?php echo urlencode($category); ?>&minus=<?php echo urlencode($pname); ?>">−</a>
              <div class="qty"><?php echo (int)$prod['quantity']; ?></div>
              <a class="small-btn plus" href="inventory.php?category=<?php echo urlencode($category); ?>&plus=<?php echo urlencode($pname); ?>">+</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>


<div id="addModal" class="modal">
  <div class="modal-box">
    <h3>Add Product</h3>
    <form method="post" action="add_product.php" enctype="multipart/form-data">
      <label class="label">Name</label>
      <input type="text" name="name" required>

      <label class="label">Quantity</label>
      <input type="number" name="quantity" min="1" value="1" required>

      <label class="label">Price</label>
      <input type="number" name="price" step="0.01" min="0" value="0.00" required>

      <label class="label">Category</label>
      <select name="category" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($cat === $category) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
        <?php endforeach; ?>
      </select>

      <label class="label">Image (optional: PNG/JPG)</label>
      <input type="file" name="image" accept="image/png,image/jpeg">

      <input type="hidden" name="return_category" value="<?php echo htmlspecialchars($category); ?>">

      <div style="margin-top:12px;">
        <button class="btn yes" type="submit">Add</button>
        <button class="btn btn-cancel"><a class="modal-close" href="inventory.php?category=<?php echo urlencode($category); ?>">Cancel</a></button>
      </div>
    </form>
  </div>
</div>


<div id="addCategoryModal" class="modal">
  <div class="modal-box">
    <h3>Add Category</h3>
    <form method="post" action="add_category.php">
      <input type="text" name="category" required placeholder="Category">
      <div style="margin-top:12px;">
        <button class="btn create" type="submit">Create</button>
        <button class="btn btn-back"><a href="inventory.php?category=<?php echo urlencode($category); ?>" class="modal-close">Cancel</a></button>
      </div>
    </form>
  </div>
</div>

</body>
</html>