<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";
include_once("class_elem.php");
$E = new Elem();
$E->Events();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Номенклатура</title>
</head>
<body>
<?php include("header.php"); ?>
<div class="style1" id="pagecell1">
    <?php

    $UserG = array('admin', 'oper');

    $add = false;
    if (isset($_POST['Flag']))
        if ($_POST['Flag'] == 'formAdd') {
            echo $E->formAddEdit();
            $add = true;
        }

    if (!$add)
        if (in_array($_SESSION['MM_UserGroup'], $UserG)) {
            echo Func::ActButton('', 'Добавить Элемент', 'formAdd');
            echo $E->formNomen();
        }
    ?>
</div>
</body>
</html>
