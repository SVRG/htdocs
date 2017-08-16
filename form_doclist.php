<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include "security.php";

$UserG = array('admin', 'oper');
include_once("class_doc.php");
$Dogovor = new Doc();
$Dogovor->Events();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Список Договоров</title>
    <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php include("header.php"); ?>
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <div id="pageName">
        <?php
        $add = false;
        if (isset($_POST['Flag']))
            if ($_POST['Flag'] == 'AddDoc'){
                echo $Dogovor->formAddEdit();
                echo func::Cansel();
                $add=true;
        }

        if(!$add)
        {
            if(in_array($_SESSION['MM_UserGroup'], $UserG))
                echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Новый Договор', 'AddDoc');

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
