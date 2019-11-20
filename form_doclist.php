<?php
include "security.php";
include_once "class_search.php";

$UserG = array('admin', 'oper');
include_once("class_doc.php");
$Dogovor = new Doc();
$Dogovor->Events();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<head>
    <title>Список Договоров</title>
    <script src="widgets/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Test</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="widgets/selectize/dist/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="widgets/selectize/dist/css/selectize.default.css">
    <script type="text/javascript" src="js/index.js"></script>
</head>
<body>
<?php include("header.php"); ?>
<div class="style1" id="pagecell1">
    <div id="pageName">
        <?php
        if (isset($_SESSION['search']) and isset($_GET['search'])) {
            echo Search::formDocSerch();
            if (!isset($_GET['kod_org'])) // Не нужно искать в компаниях, если задан код организации в фильтре
            {
                if (!isset($_GET['kod_elem'])) // Если не задан фильтр по коду элемента и коду организации то поиск по контактам
                {
                    echo Search::formOrgSearch();
                    echo Search::formKontSerch();
                }
            }
        } else {
            if (isset($_GET['add'])) {
                if (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                    echo $Dogovor->formAddEdit();
                    $add = true;
                }
            } elseif (isset($_GET['search_sellist'])) {
                echo Doc::formSearch(); // Форма поиска
            } else
                echo $Dogovor->formDocList();
        }
        ?>
    </div>
</div>
<script type="text/javascript">
    <!--
    var sprytextfield2 = new Spry.Widget.ValidationTextField("SNumR", "none", {minChars: 1});
    var sprytextfield3 = new Spry.Widget.ValidationTextField("SDateR", "date", {format: "dd.mm.yyyy"});
    //-->
</script>
</body>
</html>
