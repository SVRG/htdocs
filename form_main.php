<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>График Поставок</title>
</head>
<body>
<?php include("header.php"); ?>
<div id="pagecell1">
    <?php
    $t = date('H:i:s');
    include("class_doc.php");
    $D = new Doc();

    $sgp = 0;
    if (isset($_GET['sgp'])) {
        $sgp = $_GET['sgp'];
    }

    if($sgp == 1)
        echo $D->formDocsOpen(); // Открытые договоры
    elseif ($sgp == 2)
        echo $D->formRPlan(1); // Внешние
    elseif ($sgp == 3)
        echo $D->formSGPHistory(); // История по складу
    elseif ($sgp == 4)
        echo $D->formRPlanNeOplach(); // Не оплаченные
    elseif ($sgp == 5)
        echo $D->formRPlanOplach(); // Оплаченные
    elseif ($sgp == 6)
        echo $D->formSGPHistory(1); // История по складу
    elseif ($sgp == 7)
        echo $D->formDocsOpen(1); // Внешние открытые договоры
    else
        echo $D->formRPlan(0); // Обычный

    echo 'begin:' . $t . ' end:' . date('H:i:s');
    ?>
</div>
</body>
</html>
