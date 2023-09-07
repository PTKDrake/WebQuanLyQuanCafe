<?php
include "../auth.php";
$position = $_REQUEST['position'] ?? null;
if ($position == null) {
    header("Location: ../tables.php");
    die();
} else {
    $table_position = getTablePosition($position);
    if ($table_position == null) {
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
        <h3 class="heading">Table <?php echo $position ?><span>Details</span></h3>
        <div class="row">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sum = 0;
                foreach (getTableItems($position) as $item) {
                    ?>
                    <tr>
                        <td><?php echo $item['name'] ?></td>
                        <td><?php echo $item['price'] ?></td>
                        <td><?php echo $item['quantity'] ?></td>
                        <td><?php
                            $s = $item['price'] * $item['quantity'];
                            $sum += $s;
                            echo $s;
                            ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <span class="h3">Total: <?php echo $sum ?></span>
            <div class="col-md-7">
                <div><a class="btn-custom" href="item_order.php?position=<?php echo $position ?>">Order more</a></div>
            </div>
            <div class="col-md-5">
                <?php if ($sum > 0) { ?>
                    <a class="btn-custom" href="checkout.php?position=<?php echo $position ?>">Checkout</a>
                <?php } ?>
            </div>
        </div>
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