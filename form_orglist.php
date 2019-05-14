<?php
include_once "security.php";
include_once("class_org.php");

$org = new Org();
//---------------------------------------------------------------------------
// Добавление Организации
if (isset($_POST['FormName']))
    if ($_POST['FormName'] == "FormAddEdit")
        if (isset($_POST['poisk']) and isset($_POST['nazv_krat']) and isset($_POST['nazv_poln'])) {
            $kod_org = (int)$org->AddOrg($_POST['poisk'], $_POST['nazv_krat'], $_POST['nazv_poln']);
            if ($kod_org > 0)
                header("Location: http://" . $_SERVER['HTTP_HOST'] . "/form_org.php?kod_org=$kod_org");
            else
                header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
        }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Список Организаций</title>
    <script src="widgets/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php include("header.php"); ?>
<div class="style1" id="pagecell1">
    <?php

    $UserG = array('admin', 'oper', 'manager');
    $add_edit = false;

    if (in_array($_SESSION['MM_UserGroup'], $UserG)) {
        if (isset($_GET['add']))
            {
                echo $org->formAddEdit();
                $add_edit = true;
            }
    }

    // Вывод список организаций
    if (!$add_edit) {
        if (isset($_GET['dolg']))
            echo $org->formDolgOrg();
        elseif (isset($_GET['pays']))
            echo $org->formOrgPays();
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
