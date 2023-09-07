<?php
include "auth.php";
if($user != null){
    header("Location: index.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coffee - Login</title>
    <link rel="icon" href="resources/icon.png" type="image/gif" sizes="16x16">
    <link href="resources/css/style.css" rel="stylesheet">
    <link href="resources/css/bootstrap.css" rel="stylesheet">
    <script src="resources/js/popper.js"></script>
    <script src="resources/js/bootstrap.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
        <div class="container-fluid">
            <div id="logo">
                <a href="index.php">
                    <img alt="" src="resources/icon.png" width="30" height="30"/>
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php">Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<div id="content" class="home">
    <div class="container">
        <h3 class="heading">Login</h3>
        <form action="login.php" method="post" class="form">
            <div class="form-group">
                <input aria-label="" placeholder="Username" class="box" id="username" name="username" required>
            </div>
            <div class="form-group">
                <input aria-label="" placeholder="Password" class="box" id="password" type="password" name="password" required>
            </div>
            <a href="register.php">Create account</a>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $username = $_POST['username'] ?? null;
                $password = $_POST['password'] ?? null;
                if ($username == null || $password == null) {
                    echo "<h3>Fields missing.</h3>";
                } else {
                    $token = login($username, $password);
                    if ($token == null) {
                        echo "<h3>Wrong username/password</h3>";
                    }else{
                        setcookie('token', $token, time() + (86400 * 30));
                        header("Location: index.php");
                        die();
                    }
                }
            }
            ?>
            <div class="form-group">
                <button type="submit" class="btn-custom">Login</button>
            </div>
        </form>
    </div>
</div>
<footer>
    <div class="credit">
        <a href="index.php">
            <img alt="" class="f-logo" src="resources/icon.png" width="30" height="30"/><span
                class="copy">Nhóm 4</span>
        </a>
    </div>
</footer>
</body>
</html>