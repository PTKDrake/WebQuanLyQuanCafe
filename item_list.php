<?php
include "auth.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coffee - Item List</title>
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
                            <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                Item
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="item_create.php">Create</a></li>
                                <li><a class="dropdown-item active" href="#">List</a></li>
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
        <h3 class="heading">Item<span>List</span></h3>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $id = $_POST['id'] ?? '';
            if ($id != '') {
                if (removeItem($id)) {
                    echo "<h3>Removed item</h3>";
                }
            }
        }
        ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>In stock</th>
                <th>Description</th>
                <th>Image</th>
                <th><a class="btn btn-primary" href="item_create.php">Add</a></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach (getItems() as $item) {
                ?>
                <tr>
                    <td><?php echo $item['name']?></td>
                    <td><?php echo $item['price']?></td>
                    <td><?php echo $item['in_stock']?></td>
                    <td><?php echo $item['description']?></td>
                    <td><?php echo '<img style="width: 100px; height:100px" src="data:image/jpeg;base64,'.base64_encode($item['image']).'"/>';?></td>
                    <td><a class="btn btn-primary" href="item_edit.php?id=<?php echo $item['id']?>">Edit</a> <form action="item_list.php" method="post"><input name='id' hidden value='<?php echo $item['position']?>'><button type='submit' class='btn btn-primary'>Remove</button></form></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

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

<script>
    function add() {
        location.assign("Add.html");
    }

    function edit() {
        location.assign("Edit.html");
    }
</script>