<?php
session_start();
$file = 'database.json';

if (!file_exists($file)) file_put_contents($file, json_encode([]));
$data = json_decode(file_get_contents($file), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $username = trim($_POST['username']);
 $password = $_POST['password'];

if (isset($data[$username]) && $data[$username]['password'] === $password) {
 $_SESSION['user'] = $username;

 header('Location: dashboard.php'); 
 exit();

 } else {
 $error = "Invalid username or password.";
 }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">
 <h2>Login</h2>
 <form method="post" action="">
 <input type="text" name="username" placeholder="Username" required>
 <input type="password" name="password" placeholder="Password" required>
 <button class="btn btn-login" type="submit">Login</button>
 </form>
 <p class="note" style="margin-top:10px;" >Don't have an account? <a class="go" href="register.php">Register</a></p>
 <p style="color:#b91c1c;" ><?php echo $error ?? ''; ?></p>
</div>
</body>
</html>