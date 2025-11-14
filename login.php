php
<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // LOGIN SEDERHANA (Hardcode)
    if ($username == "admin" && $password == "admin123") {
        $_SESSION['login'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Admin - SKA Polinela</title>
    <style>
        body {font-family: Arial; background:#f0f0f0; display:flex; justify-content:center; align-items:center; height:100vh;}
        .box {background:#fff; padding:30px; width:320px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.2);}
        input {width:100%; padding:10px; margin-bottom:10px; border:1px solid #aaa; border-radius:5px;}
        button {width:100%; padding:10px; background:#0056b3; color:white; border:none; border-radius:5px;}
        .error {color:red; margin-bottom:10px;}
    </style>
</head>
<body>

<div class="box">
    <h3>Login Admin</h3>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username admin" required>
        <input type="password" name="password" placeholder="Password admin" required>
        <button type="submit" name="login">LOGIN</button>
    </form>
</div>

</body>
</html>
