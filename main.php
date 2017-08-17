<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>График Поставок</title>
</head>
<body>
<?php include("header.php"); ?>
<!-- end masthead -->
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <?php
    $t = date('H:i:s');
    include("class_doc.php");
    $D = new Doc();

    $sgp = 0;
    if (isset($_GET['sgp'])) {
        $sgp = $_GET['sgp'];
    }

    if ($sgp == 0)
        echo $D->formRPlan(0);
    elseif ($sgp == 1)
        echo $D->formDocsOpen();
    elseif ($sgp == 2)
        echo $D->formRPlan(1);
    elseif (isset($_GET['sgphistory']))
        echo $D->formSGPHistory();

    echo 'begin:' . $t . ' end:' . date('H:i:s');
    ?>
</div>
</body>
</html>
