<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

$UserG = array('admin', 'oper');
$UserG1 = array('admin', 'oper', 'manager');

//---------------------------------------------------------------------------
include_once("class_kont.php");

$Cont = new Kontact();
if (isset($_GET['kod_kontakta']))
    $Cont->Set($_GET['kod_kontakta']);
elseif (isset($_POST['kod_kontakta']))
    $Cont->Set($_POST['kod_kontakta']);
else
    die('Контакт не выбран либо ссылка на несуществующий контакт!');

//---------------------------------------------------------------------------
// Вставка Телефон в Контакт
if (isset($_POST['AddPhone']))
    if (isset($_POST['Numb'])) {
            $Cont->AddPhone($_POST['Numb']);

        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//---------------------------------------------------------------------------

//---------------------------------------------------------------------------
// Сохранение изменений контакта
if (isset($_POST['SaveContForm'])) {
    $Cont->Save($_POST['Dolg'], $_POST['FName'], $_POST['Name'], $_POST['SName']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------
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
$Org->kod_org = $Cont->kod_org;
$Org->getData();

?>
<div class="style1" id="pagecell1">
    <table width="100%" border="0" cellspacing="10">
        <tr>
            <td width="50%" valign="top" bgcolor="#ECEEFD"><h1><?php echo $Cont->Name; ?></h1>
                <p><?php


                    if (in_array($_SESSION['MM_UserGroup'], $UserG))
                        echo Func::ActButton('', 'Изменить', 'EditCont');

                    if (isset($_POST['Flag']))
                        if ($_POST['Flag'] == 'EditCont') {
                            echo $Cont->SaveForm();
                            Func::Cansel();
                        }

                    ?><br>
                    Контактная информация<?php echo $Cont->Phones(); ?></p>
                <form id="form1" name="form1" method="post" action="">
        <span id="sprytextfield1">
          <input title="" type="text" name="Numb" id="Numb"/>
          <span class="textfieldRequiredMsg">A value is required.</span></span>
                    <label>
                        <input type="submit" name="button" id="button" value="Добавить"/>
                    </label>
                    <input type="hidden" name="AddPhone" id="AddPhone"/>
                </form>
                <p>&nbsp;</p></td>
            <td width="50%" valign="top"><?php echo '<h1>' . $Org->getFormLink() . '</h1>'; ?>
                <div id="CollapsiblePanel1" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Реквизиты</div>
                    <div class="CollapsiblePanelContent"><?php $Org->ShowRecv(); ?></div>
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
                            echo $Cont->ShowDocs();
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
