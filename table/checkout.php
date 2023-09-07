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
        <h3 class="heading">Table <?php echo $position ?><span>Checkout</span></h3>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $total_paid = $_POST['total_paid'];
            if (makeBill($position, $total_paid)) {
                echo "<h3>Successfully paid</h3>";
            }
        }
        ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Items</th>
                <th>Price</th>
                <th>Amount</th>
                <th>Total</th>
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
        <form name="receipt" method="post" action="checkout.php?position=<?php echo $position ?>" class="form">
            <input hidden name="position" value="<?php echo $position ?>">
            <div class="form-group">
                <input aria-label="" placeholder="Total paid" class="box" id="total_paid" name="total_paid"
                       type="number" min="<?php echo $sum ?>" value="<?php echo $sum ?>" required>
            </div>
            <div>
                <button class="btn-custom" type="submit">Checkout</button>
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