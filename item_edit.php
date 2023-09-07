<?php
include "auth.php";
$id = '';
$name = '';
$description = '';
$price = '';
$in_stock = '';
$category_id = '';
if(isset($_GET['id'])) {
    $item = getItem($_GET['id']);
    if($item != null){
        $id = $item['id'];
        $name = $item['name'];
        $description = $item['description'];
        $price = $item['price'];
        $in_stock = $item['in_stock'];
        $category_id = $item['category_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
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
        <h3 class="heading">Item<span>Edit</span></h3>
        <form action="item_edit.php" method="post" enctype="multipart/form-data" class="form">
            <div class="form-group">
                <input aria-label="" placeholder="Id" class="box" id="id" name="id" value="<?php echo $id ?>">
            </div>
            <div class="form-group">
                <select class="form-select box" aria-label="" form="item_create_form" name="category">
                    <option hidden value="-1">Category</option>
                    <?php
                    foreach (getCategories() as $category) {
                        echo "<option value='{$category[0]}' " . ($category[0] == $category_id ? 'selected' : '') . ">{$category[1]}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input aria-label="" placeholder="Name" class="box" id="name" name="name" value="<?php echo $name ?>">
            </div>
            <div class="form-group">
                <input aria-label="" placeholder="Description" class="box" id="description" name="description" value="<?php echo $description ?>">
            </div>
            <div class="form-group">
                <input aria-label="" placeholder="Price" class="box" id="price" name="price" type="number" min="0" value="<?php echo $price ?>">
            </div>
            <div class="form-group">
                <input aria-label="" placeholder="In stock" class="box" id="in_stock" name="in_stock" type="number" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'');"  value="<?php echo $in_stock ?>">
            </div>
            <div class="form-group">
                <input aria-label="" placeholder="Image" class="form-control box" id="image" name="image" accept="image/*" type="file">
            </div>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $id = $_POST['id'] ?? null;
                $name = $_POST['name'] ?? null;
                $category = $_POST['category'] ?? null;
                if($category == '-1') $category = null;
                $description = $_POST['description'] ?? null;
                $price = $_POST['price'] ?? null;
                $in_stock = $_POST['in_stock'] ?? null;
                if($id == null){
                    echo "<h3>Missing fields</h3>";
                }else{
                    if(editItem($id, $category, $name, $in_stock, $price, ($_FILES['image']['tmp_name'] != '') ? file_get_contents($_FILES['image']['tmp_name']) : null, $description) == null){
                        echo "<h3>Item exists</h3>";
                    }else{
                        echo "<h3>Updated item</h3>";
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