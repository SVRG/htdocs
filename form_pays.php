<?php
include_once "security.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru" xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Платежи</title>
</head>
<body>
<?php include("header.php"); ?>
<!-- end masthead -->
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <div id="pageName">
        <?php
        $t = date('H:i:s');
        include("class_doc.php");

        $D = new Doc();

        if (isset($_GET['m'])) {
            if ((int)$_GET['m'] > 0 and (int)$_GET['m'] <= 12)
            {
                if(isset($_GET['VN']))
                    echo $D->formCurrentMonthPays((int)$_GET['m'],true);
                else
                    echo $D->formCurrentMonthPays((int)$_GET['m']);
            }
        } else
        {
            if(isset($_GET['VN']))
                echo $D->formCurrentMonthPays(0,true);
            else
                echo $D->formCurrentMonthPays(0);
        }

        echo 'begin:' . $t . ' end:' . date('H:i:s');
        ?>
    </div>
</div>
</body>
</html>
