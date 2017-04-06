<?php
if (!isset($_SESSION)) {
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

$UserG = array('admin', 'oper');
$UserG1 = array('admin', 'oper', 'manager');
$Edit = false;
$ADMIN = array('admin');

include_once "security.php";

include_once("class_doc.php");

if(!isset($_GET['kod_dogovora']) and !isset($_POST['kod_dogovora']))
    exit("Не задан Договор");
$D = new Doc();
if(isset($_GET['kod_dogovora']))
    $D->kod_dogovora = $_GET['kod_dogovora'];
else
    $D->kod_dogovora = $_POST['kod_dogovora'];
$D->getData();
$D->Events();

$P = new Part();
$P->kod_dogovora = $D->kod_dogovora;
if(isset($_POST['kod_part'])) // Если был передан код партии
    $P->kod_part = $_POST['kod_part'];
$P->Events();

$K = new Kontakt();
$K->kod_dogovora = $D->kod_dogovora;
$K->Events();

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
    <title><?php echo $D->Data['nomer'] ?></title>
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

                if (in_array($_SESSION['MM_UserGroup'], $UserG))
                    $D->formDogovor(0, 1);
                else
                    $D->formDogovor();

                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'DocClose') {
                        echo Func::ActButton('', 'Подтвердить Закрытие', 'DocCloseConf');
                        Func::Cansel();
                    }


                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'DocEditForm')
                        echo $D->formAddEdit(1);

                echo Func::ActButton('', 'Редактировать Договор', 'DocEditForm');

                echo $D->formDocum($Del); // Документы договора

                ?>
            </td>
            <td align="left">

                <?php
                //-----------------------------------------------------------------------------------------------------
                // Добавление Контакта
                if (in_array($_SESSION['MM_UserGroup'], $UserG1)) {

                    // Вывод контактов
                    $D->formDocKontakts(1);
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Контакт', 'AddCont');

                    // Добаление Контакта в Договор
                    if (isset($_POST['Flag']))
                        if ($_POST['Flag'] == 'AddCont') {
                            echo $K->formAddEdit();
                            $_POST['Flag'] = null;
                        }
                } elseif (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                    $D->formDocKontakts(1);
                } else $D->formDocKontakts(0);

                ?></td>
        </tr>
        <tr valign="top">
            <td colspan="2" align="left"><p>Партии:
                    <?php
                    if (isset($_POST['Flag']))
                        if ($_POST['Flag'] == 'AddPartForm') {
                            $P->formAddEdit(0);
                        }

                    // Кнопка Добавить Партию
                    if (in_array($_SESSION['MM_UserGroup'], $UserG))
                        echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Партию', 'AddPartForm');

                    // Партии
                    if (isset($_POST['Flag'])) {
                        if ($_POST['Flag'] == 'AddNacl')
                            $P->ShowPart(1); // Партия + Форма добавления накладной
                    }

                    echo $P->GetParts(1,1,"",0);

                    ?></p>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td width="50%" align="left" valign="top">
                <?php

                // Платежи - ------------------
                echo $D->formPP();

                // Ввод платежей
                if (in_array($_SESSION['MM_UserGroup'], $UserG))
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Платеж', 'AddPP');

                if (isset($_POST['Flag'])) {
                    if ($_POST['Flag'] == 'AddPP') {
                        echo '
                                <form id="form1" name="form1" method="post" action="">
                                  <table width="434" border="0">
                                    <tr>
                                      <td width="126">Номер ПП</td>
                                      <td width="292"><span id="SNumR">
                                      <input type="text" name="PPNum" id="PPNum" />
                                      <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldMinCharsMsg">Minimum
                                      number of characters not met.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Дата</td>
                                      <td><span id="SDateR">
                                      <input type="text" name="PPDate" id="PPDate" value="' . date('d.m.Y') . '"/>
                                      <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid
                                      format.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Сумма</td>
                                      <td><span id="SSummR">
                                      <input type="text" name="PPSumm" id="PPSumm" />
                                      <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid
                                      format.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Примечание</td>
                                      <td><span id="STextNR">
                                        <input type="text" name="PPPrim" id="PPPrim" />
                                      </span></td>
                                    </tr>
                                  </table>
                                  <p>
                                    <input type="submit" name="button" id="button" value="Добавить" />
                                    <input type="reset" name="button" id="button" value="Сброс" />
                                    <input type="hidden" name="AddPP" value="1" />
                                  </p>
                                </form>';
                        echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', '');
                        $_POST['Flag'] = null;
                    }
                }
                ?>  </td>
            <td width="50%" align="left" valign="top">
                <?php
                // Счета ------------------------------------
                $D->formScheta();

                // Добавить Счет
                if (in_array($_SESSION['MM_UserGroup'], $UserG))
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Счет', 'AddInvoice');

                if (isset($_POST['Flag'])) {
                    if ($_POST['Flag'] == 'AddInvoice') {
                        echo '
                            <form id="form1" name="form1" method="post" action="">
                              <table width="434" border="0">
                                <tr>
                                  <td width="126">Номер Счета</td>
                                  <td width="292"><span id="SNumR">
                                  <input type="text" name="InvNum" id="InvNum" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldMinCharsMsg">Minimum
                                  number of characters not met.</span></span></td>
                                </tr>
                                <tr>
                                  <td>Дата</td>
                                  <td><span id="SDateR">
                                  <input type="text" name="InvDate" id="InvDate" value="' . date('d.m.Y') . '" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Неправильный формат даты. Пример - 01.01.2001</span></span></td>
                                </tr>
                                <tr>
                                  <td>Сумма</td>
                                  <td><span id="SSummR">
                                  <input type="text" name="InvSumm" id="InvSumm" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid
                                  format.</span></span></td>
                                </tr>
                                <tr>
                                  <td>Примечание</td>
                                  <td><span id="STextNR">
                                    <input type="text" name="InvPrim" id="InvPrim" />
                                      <span class="textfieldRequiredMsg">Необходимо ввести значение.</span></span></td>
                                  </span></td>
                                </tr>
                              </table>
                              <p>
                                <input type="submit" name="button" id="button" value="Добавить" />
                                <input type="reset" name="button" id="button" value="Сброс" />
                                <input type="hidden" name="AddInv" id="button" value="1" />
                              </p>
                            </form>';
                        echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', '');
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

                    echo $D->formPrim($add_prim);
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
    var sprytextfield5 = new Spry.Widget.ValidationTextField("SDateNR", "date", {format: "dd.mm.yyyy",isRequired: false});
    var sprytextfield6 = new Spry.Widget.ValidationTextField("STextNR", "none", {isRequired: true});
    var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
    var sprytextfield7 = new Spry.Widget.ValidationTextField("sprytextfield7");
    var sprytextfield8 = new Spry.Widget.ValidationTextField("sprytextfield8");
    var sprytextfield9 = new Spry.Widget.ValidationTextField("sprytextfield9");
    //-->
</script>
</body>
</html>