 <link rel="stylesheet" href="img/emx_nav_left.css" type="text/css"/>
    <div id="masthead">
        <blockquote class="glink">
            <div class="glink" id="utility">
                Добро пожаловать: <?php
                include_once "class_func.php";
                echo func::user();
                echo "<br><a href=". $_SERVER['PHP_SELF'] . "?doLogout=true>Выход</a>";?>
            </div>
        </blockquote>
    </div>
<?php include('menu/menu.php'); ?>