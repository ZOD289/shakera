<?php
session_start();
$file = 'database.json';

if (!file_exists($file)) file_put_contents($file, json_encode([]));
$data = json_decode(file_get_contents($file), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $error = "Enter username and password.";
   
    } elseif (isset($data[$username])) {
        $error = "Username already exists.";
    
    } else {
        $data[$username] = [
            'password' => $password,
            'categories' => ['All' => []],
            'inventory' => new stdClass() 
        ];

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        header('Location: login.php'); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">
  <h2>Create account</h2>
  <form method="post" action="">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button class="btn btn-regis" type="submit">Register</button>
  </form>
  <p class="note" style="margin-top:10px;">Already have an account? <a class="go" href="login.php">Login</a></p>
  <p style="color:#b91c1c;"><?php echo $error ?? ''; ?></p>
</div>
</body>
</html>