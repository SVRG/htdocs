<?php
if (!isset($_SESSION)) {
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

include_once("class_org.php");

$org = new Org();
//---------------------------------------------------------------------------
// Добавление Организации
if(isset($_POST['FormName']))
    if($_POST['FormName']=="FormAddEdit")
        if (isset($_POST['poisk']) and isset($_POST['nazv_krat']) and isset($_POST['nazv_poln'])) {
            $org->AddOrg($_POST['poisk'], $_POST['nazv_krat'], $_POST['nazv_poln']);
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Список Организаций</title>
    <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php include("header.php"); ?>
<div class="style1" id="pagecell1">
    <?php

    $UserG = array('admin', 'oper', 'manager');
    $add_edit = false;

    if (in_array($_SESSION['MM_UserGroup'], $UserG)) {

        echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Организацию', 'AddOrg');
        if (isset($_POST['Flag']))
            if ($_POST['Flag'] == 'AddOrg') {
                echo $org->formAddEdit();
                $add_edit = true;
            }
    }

    // Вывод список организаций
    if(!$add_edit)
    {
        if(isset($_GET['dolg']))
            echo $org->formDolgOrg();
        else
            $org->formOrgList(true);
    }

    ?>
</div>
<script type="text/javascript">
    <!--
    var sprytextfield_poisk = new Spry.Widget.ValidationTextField("sprytextfield_poisk", "none", {isRequired: true});
    var sprytextfield_nazv_krat = new Spry.Widget.ValidationTextField("sprytextfield_nazv_krat", "none", {isRequired: true});
    var sprytextfield_nazv_poln = new Spry.Widget.ValidationTextField("sprytextfield_nazv_poln", "none", {isRequired: true});
    //-->
</script>
</body>
</html>
