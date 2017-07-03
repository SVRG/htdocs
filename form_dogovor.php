<?php
if (!isset($_SESSION)) {
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

$UserG = array('admin', 'oper');
$UserG1 = array('admin', 'oper', 'manager');

include_once "security.php";

include_once("class_doc.php");

if (!isset($_GET['kod_dogovora']) and !isset($_POST['kod_dogovora']))
    exit("Не задан Договор");
$Dogovor = new Doc();
if (isset($_GET['kod_dogovora']))
    $Dogovor->kod_dogovora = $_GET['kod_dogovora'];
else
    $Dogovor->kod_dogovora = $_POST['kod_dogovora'];
$Dogovor->getData();
$Dogovor->Events();

$Part = new Part();
$Part->kod_dogovora = $Dogovor->kod_dogovora;
if (isset($_POST['kod_part'])) // Если был передан код партии
    $Part->kod_part = $_POST['kod_part'];
$Part->Events();

$Kontakt = new Kontakt();
$Kontakt->kod_dogovora = $Dogovor->kod_dogovora;
$Kontakt->Events();

//---------------------------------------------------------------------------
// Удаление документов
if (isset($_POST['DelDocum'])) {
    $docum = new Docum();
    $docum->Delete($_POST['DelDocum']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title><?php echo $Dogovor->Data['nomer'] ?></title>
    <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>

</head>
<body>
<?php include("header.php");
if (in_array($_SESSION['MM_UserGroup'], $UserG))
    $Del = 1;
else
    $Del = 0;
?>
<!-- end masthead -->
<div class="style1" id="pagecell1">
    <!--pagecell1-->

    <table width="100%" border="0">
        <tr valign="top">
            <td align="left" bgcolor="#ECEEFD">

                <?php

                $edit = false;
                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'DocEditForm') {
                        echo $Dogovor->formAddEdit(1);
                        echo func::Cansel();
                        $edit = true;
                    }

                if(!$edit)
                {
                    if (in_array($_SESSION['MM_UserGroup'], $UserG))
                    {
                        $Dogovor->formDogovor(0, 1);
                        echo Func::ActButton('', 'Редактировать Договор', 'DocEditForm');
                    }
                    else
                        $Dogovor->formDogovor();
                }

                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'DocClose') {
                        echo Func::ActButton('', 'Подтвердить Закрытие', 'DocCloseConf');
                        echo Func::Cansel();
                    }


                echo $Dogovor->formDocum($Del); // Документы договора

                ?>
            </td>
            <td align="left">

                <?php
                //-----------------------------------------------------------------------------------------------------
                // Добавление Контакта
                if (in_array($_SESSION['MM_UserGroup'], $UserG1)) {

                    $add_kont = false;
                    // Добаление Контакта в Договор
                    if (isset($_POST['Flag']))
                        if ($_POST['Flag'] == 'AddKontakt') {
                            echo $Kontakt->formAddEdit();
                            echo func::Cansel();
                            $_POST['Flag'] = null;
                            $add_kont = true;
                        }

                        if(!$add_kont)
                        {
                            // Вывод контактов
                            $Dogovor->formDocKontakts(1);
                            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Новый Контакт', 'AddKontakt');
                        }
                }
                elseif (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                    $Dogovor->formDocKontakts(1);
                } else
                    $Dogovor->formDocKontakts(0);

                ?></td>
        </tr>
        <tr valign="top">
            <td colspan="2" align="left">Партии:
                    <?php
                    if (isset($_POST['Flag'])){
                        if ($_POST['Flag'] == 'AddPartForm')
                            echo $Part->formAddEdit(0);
                        elseif($_POST['Flag'] == 'AddNaklad' and isset($_POST['kod_part']))
                        {
                            $Part->kod_part = $_POST['kod_part'];
                            echo $Part->formPart(1);
                        }
                    }
                    // Партии
                    echo $Part->formParts(1, "", 0);
                    ?>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td width="50%" align="left" valign="top">
                <?php

                // Платежи - ------------------
                echo $Dogovor->formPlat(1);

                // Ввод платежей
                if (in_array($_SESSION['MM_UserGroup'], $UserG))
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Платеж', 'AddPP');

                if (isset($_POST['Flag'])) {
                    if ($_POST['Flag'] == 'AddPP') {
                        echo $Dogovor->formAddPP();
                    }
                }
                ?>  </td>
            <td width="50%" align="left" valign="top">
                <?php
                // Счета ------------------------------------
                echo $Dogovor->formInvoice();

                // Добавить Счет
                if (in_array($_SESSION['MM_UserGroup'], $UserG))
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Счет', 'AddInvoice');

                if (isset($_POST['Flag'])) {
                    if ($_POST['Flag'] == 'AddInvoice') {
                        echo $Dogovor->formAddInvoice();
                        $_POST['Flag'] = null;
                    }
                }
                ?></td>
        </tr>
        <tr>
            <td align="left" valign="top">
            </td>
            <td align="left" valign="top">
                <?php
                //-----------------------------------------------------------------------------------------------------
                // Примечание
                if (in_array($_SESSION['MM_UserGroup'], $UserG1)) {
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Примечание', 'AddPrim');

                    $add_prim = 0;
                    if (isset($_POST['Flag']))
                        if ($_POST['Flag'] == 'AddPrim')
                            $add_prim = 1;

                    echo $Dogovor->formPrim($add_prim,1);
                }
                ?></td>
        </tr>
        <tr>
            <td align="left" valign="top"></td>
            <td align="left" valign="top">&nbsp;</td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    <!--
    var sprytextfield2 = new Spry.Widget.ValidationTextField("SNumR", "none", {minChars: 1});
    var sprytextfield3 = new Spry.Widget.ValidationTextField("SDateR", "date", {format: "dd.mm.yyyy"});
    var sprytextfield4 = new Spry.Widget.ValidationTextField("SSummR", "currency");
    var sprytextfield5 = new Spry.Widget.ValidationTextField("SDateNR", "date", {
        format: "dd.mm.yyyy",
        isRequired: false
    });
    var sprytextfield6 = new Spry.Widget.ValidationTextField("STextNR", "none", {isRequired: true});
    var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
    var sprytextfield7 = new Spry.Widget.ValidationTextField("sprytextfield7");
    var sprytextfield8 = new Spry.Widget.ValidationTextField("sprytextfield8");
    var sprytextfield9 = new Spry.Widget.ValidationTextField("sprytextfield9");
    //-->
</script>
</body>
</html>