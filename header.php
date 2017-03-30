    <link rel="stylesheet" href="img/emx_nav_left.css" type="text/css"/>
    <div id="masthead">
        <blockquote class="glink">
            <div class="glink" id="utility">
                Добро пожаловать: <?php echo $_SESSION['MM_Username'];
                echo "<br><a href=". $_SERVER['PHP_SELF'] . "?doLogout=true>Выход</a>";?>
            </div>
        </blockquote>
    </div>
<?php include('menu/menu.php'); ?>