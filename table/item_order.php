<?php
include "../auth.php";
$position = $_REQUEST['position'] ?? null;
if($position == null){
    header("Location: ../tables.php");
    die();
}else{
    $table_position = getTablePosition($position);
    if($table_position == null){
        header("Location: ../tables.php");
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coffee - Home</title>
    <link rel="icon" href="../resources/icon.png" type="image/gif" sizes="16x16">
    <link href="../resources/css/style.css" rel="stylesheet">
    <link href="../resources/css/bootstrap.css" rel="stylesheet">
    <script src="../resources/js/popper.js"></script>
    <script src="../resources/js/bootstrap.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
        <div class="container-fluid">
            <div id="logo">
                <a href="../index.php">
                    <img alt="" src="../resources/icon.png" width="30" height="30"/>
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
                        <a class="nav-link" aria-current="page" href="../index.php">Home</a>
                    </li>
                    <?php
                    if ($user != null) {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="../tables.php">Tables</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                Item
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../item_create.php">Create</a></li>
                                <li><a class="dropdown-item" href="../item_list.php">List</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                Category
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../category_create.php">Create</a></li>
                                <li><a class="dropdown-item" href="../category_list.php">List</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="../history.php">History</a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
            if ($user == null) {
                ?>
                <a class="btn btn-info" href="../login.php">Login</a>
                <?php
            } else {
                ?>
                <a class="btn btn-info" href="../logout.php">Logout</a>
                <?php
            }
            ?>
        </div>
    </nav>
</header>
<div id="content" class="home">
    <div class="container">
        <h3 class="heading">Table <?php echo $position ?><span>Order</span></h3>
        <form id="order_form" action="item_order.php?position=<?php echo $position ?>" method="post" class="form">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $id = $_POST['id'] ?? null;
                $amount = $_POST['amount'] ?? null;
                if ($id == null || $amount == null) {
                    echo "<h3>Missing fields</h3>";
                } else {
                    if(addTableItem($position, $id, $amount)) {
                        echo "<h3>Ordered item</h3>";
                    }else{
                        echo "<h3>Not enough item</h3>";
                    }
                }
            }
            ?>
            <input hidden name="position" value="<?php echo $position ?>">
            <select class="form-select box" aria-label="" name="id" form="order_form" required>
                <option hidden>Item</option>
                <?php
                foreach (getItems() as $item) {
                    if ($item['in_stock'] > 0)
                        echo "<option value='{$item['id']}'>Name: {$item['name']} - Price: {$item['price']} - In stock: {$item['in_stock']}</option>";
                }
                ?>
            </select>
            <div class="form-group">
                <input aria-label="" placeholder="Amount" class="box" id="amount" name="amount" type="number" min="0"
                       oninput="this.value=this.value.replace(/[^0-9]/g,'');" required>
            </div>
            <div class="form-group">
                <a class="btn-custom" href="index.php?position=<?php echo $position ?>">Table details</a>
                <button type="submit" class="btn-custom">Order</button>
            </div>
        </form>
    </div>
</div>
<footer>
    <div class="credit">
        <a href="../index.php">
            <img alt="" class="f-logo" src="../resources/icon.png" width="30" height="30"/><span
                    class="copy">Nhóm 4</span>
        </a>
    </div>
</footer>
</body>
</html>