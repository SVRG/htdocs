<link rel="stylesheet" type="text/css" href="menu/menu.css">
<div>
    <div class="glink" id="utility">
        <?php
        include_once "class_search.php";
        include_once "class_func.php";
        echo "<div class='btn'><div>" . Search::formSearch() . "</div>";
        echo "<dic>Добро пожаловть: " . func::user() . "</dic>";
        echo "<div><a href=" . $_SERVER['PHP_SELF'] . "?doLogout=true>::Выход</a></div>";
        echo "</div>";
        ?>
    </div>
</div>
<?php include('menu/menu.php'); ?>