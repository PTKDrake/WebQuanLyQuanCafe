<?php
include "auth.php";
$id = '';
$name = '';
$description = '';
if(isset($_GET['id'])) {
    $category = getCategory($_GET['id']);
    if($category != null){
        $id = $category['id'];
        $name = $category['name'];
        $description = $category['description'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coffee - Item Edit</title>
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
                    <?php
                    if ($user != null) {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="tables.php">Tables</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                Item
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="item_create.php">Create</a></li>
                                <li><a class="dropdown-item" href="item_list.php">List</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                Category
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="category_create.php">Create</a></li>
                                <li><a class="dropdown-item" href="category_list.php">List</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="history.php">History</a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
            if ($user == null) {
                ?>
                <a class="btn btn-info" href="login.php">Login</a>
                <?php
            } else {
                ?>
                <a class="btn btn-info" href="logout.php">Logout</a>
                <?php
            }
            ?>
        </div>
    </nav>
</header>
<div id="content" class="home">
    <div class="container">
        <h3 class="heading">Category<span>Edit</span></h3>
        <form action="category_edit.php" method="post" class="form">
            <div class="form-group">
                <input aria-label="" placeholder="Id" class="box" id="id" name="id" value="<?php echo $id?>">
            </div>
            <div class="form-group">
                <input aria-label="" placeholder="Name" class="box" id="name" name="name" value="<?php echo $name?>">
            </div>
            <div class="form-group">
                <input aria-label="" placeholder="Description" class="box" id="description" name="description" value="<?php echo $description?>">
            </div>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $id = $_POST['id'] ?? null;
                $name = $_POST['name'] ?? null;
                $description = $_POST['description'] ?? null;
                if($name == null || $id == null){
                    echo "<h3>Missing name field</h3>";
                }else{
                    if(editCategory($id, $name, $description) == null){
                        echo "<h3>Category not found</h3>";
                    }else{
                        echo "<h3>Updated category</h3>";
                    }
                }
            }
            ?>
            <div class="form-group">
                <button type="submit" class="btn-custom">Submit</button>
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