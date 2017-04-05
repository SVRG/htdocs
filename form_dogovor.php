<?php
if (!isset($_SESSION)) {
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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


$P = new Part();
$P->kod_dogovora = $D->kod_dogovora;

if(isset($_POST['kod_part'])) // Если был передан код партии
    $P->kod_part = $_POST['kod_part'];

$UserG = array('admin', 'oper');
$UserG1 = array('admin', 'oper', 'manager');
$Edit = false;
$ADMIN = array('admin');

//---------------------------------------------------------------------------
// Вставка Партии
if (isset($_POST['AddPart'])) {
    $P->AddEdit($_POST['kod_elem'], $_POST['numb'], $_POST['data_postav'], $_POST['price'], $_POST['modif'], $_POST['nds'], $_POST['val']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

//---------------------------------------------------------------------------
// Отметка о Получении накладной
if (isset($_POST['PostNacl'])) {
    $P->kod_dogovora = $D->kod_dogovora;

    $P->PostNacl($_POST['PostNacl']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

//---------------------------------------------------------------------------
// Вставка Платежа
if (isset($_POST['AddPP']))
    if (isset($_POST['PPNum']) and isset($_POST['PPSumm']) and isset($_POST['PPDate'])) {
        if (!isset($_POST['PPPrim']))
            $_POST['PPPrim'] = '';
        $D->AddPay($_POST['PPNum'], $_POST['PPSumm'], Func::Date_to_MySQL($_POST['PPDate']), $_POST['PPPrim']);

        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//---------------------------------------------------------------------------
// Вставка Счета
if (isset($_POST['AddInv']))
    if (isset($_POST['InvNum']) and isset($_POST['InvSumm']) and isset($_POST['InvDate'])) {

        $D->AddInvoice($_POST['InvNum'], $_POST['InvSumm'], $_POST['InvDate'], $_POST['InvPrim']);
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//---------------------------------------------------------------------------
// Вставка Контакта
if (isset($_POST['AddCont'])) {
    $D->AddCont($_POST['Dolg'], $_POST['SName'], $_POST['Name'], $_POST['PName']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);

}

//---------------------------------------------------------------------------
// Вставка Контакта из Списка
if (isset($_POST['AddContFromList'])) {
    $c = new Kontact();
    $c->Set($_POST['SLContID']);
    $c->AddKontaktToDoc($D->kod_dogovora);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

//---------------------------------------------------------------------------
// Вставка Примечения
if (isset($_POST['AddPrim']))
    if (isset($_POST['Prim'])) {
        $D->AddPrim($_POST['Prim'],$_SESSION['MM_Username']);
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//---------------------------------------------------------------------------
// Вставка Телефон в Контакт
if (isset($_POST['AddPhone']))
    if (isset($_POST['kod_kontakta']) and isset($_POST['Numb'])) {
        $c = new Kontact();
        $c->kod_kontakta = $_POST['kod_kontakta'];
        $c->AddPhone($_POST['Numb']);

        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//---------------------------------------------------------------------------
// Закрытие Договора
if (isset($_POST['Flag']))
    if ($_POST['Flag'] == 'DocCloseConf') {
        $D->Close();
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//---------------------------------------------------------------------------
// Отмена Закрытия Договора
if (isset($_POST['Flag']))
    if ($_POST['Flag'] == 'DocOpen') {
        $D->Close(0);
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//---------------------------------------------------------------------------
// Редактирование Договора
if (isset($_POST['Flag']))
    if ($_POST['Flag'] == 'DocEdit') {
        $D->Edit($_POST['Numb'], $_POST['Date'], $_POST['SLOrgID'], $_POST['IspID']);
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//---------------------------------------------------------------------------
// Удаление Счета
    elseif ($_POST['Flag'] == 'DelInv') {
        $D->DelInvoice($_POST['InvID']);

        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//---------------------------------------------------------------------------
// Удаление документов
if (isset($_POST['DelDocum'])) {
    $docum = new Docum();
    $docum->Delete($_POST['DelDocum']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------
// Добавление Накладной
if(isset($_POST['AddEditNacl']))
    if (isset($_POST['numb']) and isset($_POST['naklad']) and isset($_POST['data']) and isset($_POST['kod_oper'])) {
        $P->AddNacl($_POST['numb'], $_POST['naklad'], $_POST['data'], $_POST['kod_oper'], $_SESSION['MM_Username']);

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
                    $D->ShowDoc(0, 1);
                else
                    $D->ShowDoc();

                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'DocClose') {
                        echo Func::ActButton('', 'Подтвердить Закрытие', 'DocCloseConf');
                        Func::Cansel();
                    }


                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'DocEditForm')
                        $D->EditForm();

                echo Func::ActButton('', 'Редактировать Договор', 'DocEditForm');

                echo $D->Docum($Del); // Документы договора

                ?>
            </td>
            <td align="left">

                <?php
                //-----------------------------------------------------------------------------------------------------
                // Добавление Контакта
                if (in_array($_SESSION['MM_UserGroup'], $UserG1)) {

                    // Вывод контактов
                    $D->ShowContacts(1);
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Контакт', 'AddCont');

                    // Добаление Контакта в Договор
                    if (isset($_POST['Flag']))
                        if ($_POST['Flag'] == 'AddCont') {
                            echo '<form id="form1" name="form1" method="post" action="">
                                      <table width="416" border="0">
                                        <tr>
                                          <td width="185">Должность</td>
                                          <td width="215">
                                            <input type="text" name="Dolg" id="Dolg" />
                                          </td>
                                        </tr>
                                        <tr>
                                          <td>Фамилия</td>
                                          <td>
                                            <input type="text" name="SName" id="SName" />
                                          </td>
                                        </tr>
                                        <tr>
                                          <td>Имя</td>
                                          <td>
                                            <input type="text" name="Name" id="Name" />
                                          </td>
                                        </tr>
                                        <tr>
                                          <td>Отчество</td>
                                          <td>
                                          <input type="text" name="PName" id="PName" />
                                          </td>
                                        </tr>
                                        <tr>
                                        <td>
                                        <select name="Status" id="select">
                                            <option value="2" selected="selected">По Договору</option>
                                            <option value="3">По Финансированию</option>
                                            <option value="4">По Отгрузке</option>
                                            <option value="1">Подписант</option>
                                        </select>
                                          </td>
                                        <td><input type="submit" name="button" id="button" value="Submit" /></td>
                                        </tr>
                                      </table>
                                    <input type="hidden" name="AddCont" value="1" />
                                    </form>';
                            Func::Cansel();
                            $_POST['Flag'] = null;

                        }
                } elseif (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                    $D->ShowContacts(1);
                } else $D->ShowContacts(0);

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
                echo $D->getPP();

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
                $D->ShowScheta();

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

                    echo $D->getPrim($add_prim);
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