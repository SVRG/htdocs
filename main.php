<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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
    //include("nav.php");
    $t = date('H:i:s');
    include("class_doc.php");
    $D = new Doc();

    $sgp = 0;
    if (isset($_GET['sgp'])) {
        if ($_GET['sgp'] == 1)
            $sgp = 1;
        elseif ($_GET['sgp'] == 2)
            $sgp = 2;
        else $sgp = -1;
    }

    if ($sgp == -1)
        echo $D->getRPlan(0);
    elseif ($sgp == 1)
        echo $D->getRPlan(0);
    elseif ($sgp == 2)
        echo $D->getRPlan(1);
    elseif (isset($_GET['sgphistory']))
        echo $D->SGPHistory();

    /*
     *
         elseif (isset($_GET['RP'])) // Оплачено
            echo Doc::getRPlan_by_Elem(' WHERE zakryt=0 AND summa_plat>1  AND kod_ispolnit=683 ');
        elseif (isset($_GET['RP_VN'])) // todo - план реализации внешний?
            echo Doc::getRPlan_by_Elem(" WHERE zakryt=0 AND kod_ispolnit<>683 ");
        elseif (isset($_GET['RPN'])) // Нет оплаты
            echo Doc::getRPlan_by_Elem(" WHERE zakryt=0 AND summa_plat=0  AND kod_ispolnit=683 ");
        else // Закрытые договоры НВС
            echo Doc::getRPlan_by_Elem(" WHERE zakryt=1 AND kod_ispolnit=683 ");
    */

    echo 'begin:' . $t . ' end:' . date('H:i:s');
    ?>
</div>
</body>
</html>
