<?php
include_once "security.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>График Поставок</title>
</head>
<body>
<?php include("header.php"); ?>
<div id="pagecell1">
    <?php
    $t = date('H:i:s');
    include_once "class_doc.php";
    $D = new Doc();

    $sgp = 0;
    if (isset($_GET['sgp'])) {
        $sgp = (int)$_GET['sgp'];
        if ($sgp == 1)
            try {
                echo $D->formDocsOpen();
            } catch (Exception $e) {
            } // Открытые договоры
        elseif ($sgp == 2)
            try {
                echo $D->formRPlan(1);
            } catch (Exception $e) {
            } // Внешние
        elseif ($sgp == 3)
            echo $D->formSGPHistory(); // История по складу
        elseif ($sgp == 4)
            try {
                echo $D->formRPlanNeOplach();
            } catch (Exception $e) {
        echo "Ошибка: formRPlanNeOplach";
        } // Не оплаченные
        elseif ($sgp == 5)
            try {
                echo $D->formRPlanOplach();
            } catch (Exception $e) {
            } // Оплаченные
        elseif ($sgp == 6)
            echo $D->formSGPHistory(1); // История по складу
        elseif ($sgp == 7)
            try {
                echo $D->formDocsOpen(1);
            } catch (Exception $e) {
            } // Внешние открытые договоры
        elseif ($sgp == 8)
            try {
                echo $D->formProduction($_GET['kod_elem']);
            } catch (Exception $e) {
            }
        elseif ($sgp == 9)
            try {
                echo $D->formDocsNoDocuments();
            } catch (Exception $e) {
            }
    } else
        try {
            echo $D->formRPlan(0);
        } catch (Exception $e) {
        } // Обычный

    echo 'begin:' . $t . ' end:' . date('H:i:s');
    ?>
</div>
</body>
</html>
