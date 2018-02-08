<link rel="stylesheet" type="text/css" href="menu/menu.css">
<div>
    <div class="glink" id="utility">
        Добро пожаловать:
        <?php
        include_once "class_func.php";
        echo func::user();
        echo "<br><a href=" . $_SERVER['PHP_SELF'] . "?doLogout=true>Выход</a>";
        ?>
    </div>
</div>
<?php include('menu/menu.php'); ?>