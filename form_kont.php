<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

$UserG = array('admin', 'oper');
$UserG1 = array('admin', 'oper', 'manager');

//---------------------------------------------------------------------------
include_once("class_kont.php");

$Kontakt = new Kontakt();
if (isset($_GET['kod_kontakta']))
    $Kontakt->Set($_GET['kod_kontakta']);
elseif (isset($_POST['kod_kontakta']))
    $Kontakt->Set($_POST['kod_kontakta']);
else
    die('Контакт не выбран либо ссылка на несуществующий контакт!');

$Kontakt->Events();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Контакт</title>
    <script src="/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <script src="SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
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

    <link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <link href="SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php
include_once("header.php");
include_once("class_org.php");
include_once("class_doc.php");


$Doc = new Doc();
$Org = new Org();
$Org->kod_org = $Kontakt->kod_org;
$Org->getData();

?>
<div class="style1" id="pagecell1">
    <table width="100%" border="0" cellspacing="10">
        <tr>
            <td width="50%" valign="top" bgcolor="#ECEEFD"><h1><?php echo $Kontakt->Name; ?></h1>
                <p><?php


                    if (in_array($_SESSION['MM_UserGroup'], $UserG))
                        echo Func::ActButton('', 'Изменить', 'EditCont');

                    if (isset($_POST['Flag']))
                        if ($_POST['Flag'] == 'EditCont') {
                            echo $Kontakt->formAddEdit(1);
                            Func::Cansel();
                        }

                    ?>
                    <br>
                    Контактная информация<?php echo $Kontakt->formPhones(-1,1); ?>
            </td>
            <td width="50%" valign="top"><?php echo '<h1>' . $Org->getFormLink() . '</h1>'; ?>
                    <div id="CollapsiblePanel1" class="CollapsiblePanel">
                        <div class="CollapsiblePanelTab">Реквизиты</div>
                        <div class="CollapsiblePanelContent"><?php $Org->formRecv(); ?></div>
                    </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" valign="top">
                <div id="CollapsiblePanel2" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Договоры</div>
                    <div class="CollapsiblePanelContent">
                        <?php
                        // Вывод договоров по контакту
                            echo Doc::formDocsByKontakt($Kontakt->kod_kontakta);
                        ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    <!--
    var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen: true});
    var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen: true});
    var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
    //-->
</script>
</body>
</html>
