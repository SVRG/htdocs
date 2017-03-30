<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include "security.php";

$UserG = array('admin', 'oper');
include_once("class_doc.php");
$d = new Doc();
//----------------------------------------------------------------------------------------------------------------------
// Добавляем договор
if (isset($_POST['AddDocForm'])) {
    if(isset($_POST['nomer'], $_POST['data_sost'], $_POST['kod_org'], $_POST['VN']))
        $d->Add($_POST['nomer'], $_POST['data_sost'], $_POST['kod_org'], $_POST['VN']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//----------------------------------------------------------------------------------------------------------------------

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Список Договоров</title>
    <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>

    <script type="text/javascript">
        <!--
        function MM_reloadPage(init) {  //reloads the window if Nav4 resized
            if (init == true) with (navigator) {
                if ((appName == "Netscape") && (parseInt(appVersion) == 4)) {
                    document.MM_pgW = innerWidth;
                    document.MM_pgH = innerHeight;
                    onresize = MM_reloadPage;
                }
            }
            else if (innerWidth != document.MM_pgW || innerHeight != document.MM_pgH) location.reload();
        }
        MM_reloadPage(true);
        //-->
    </script>

    <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php include("header.php"); ?>
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <div id="pageName">
        <?php
        $ed = 1;
        if (isset($_POST['Flag'])) {
            if ($_POST['Flag'] == 'AddDoc')
                $d->ShowDoc($ed);
        }

        if(isset($_POST['Flag']))
        if(in_array($_SESSION['MM_UserGroup'], $UserG) and $_POST['Flag'] != 'AddDoc')
        {
            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Новый Договор', 'AddDoc');
        }
        echo $d->getFullDocList();
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
