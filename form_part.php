<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

include_once("class_part.php");
include_once("class_doc.php");

if (!isset($_GET['kod_part']))
    exit("Не задан Код партии");

$D = new Doc();
$D->kod_dogovora = $_GET['kod_dogovora'];

$P = new Part();
$P->kod_part = $_GET['kod_part'];
$P->kod_dogovora = $_GET['kod_dogovora'];

//---------------------------------------------------------------------------
// Edit Партии
if (isset($_POST['EditPart'])) {

    $P->Edit($_POST['SelElemID'], $_POST['Numb'], $_POST['SDateR'], $_POST['PriceTF'], $_POST['Mod'], $_POST['NDS'], $_POST['VAL']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

//---------------------------------------------------------------------------
// Добавление Расчета
if (isset($_POST['Summ']) and isset($_POST['Date']) and isset($_POST['Type'])) {
    $P->AddRasch($_POST['Summ'], $_POST['Date'], $_POST['Type']);
    $_POST['Summ'] = 0;

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

//---------------------------------------------------------------------------
// Добавление Расчета 100% во все партии
if (isset($_POST['Flag']))
    if ($_POST['Flag'] == 'AddRasch100') {
        $P->AddRasch100();

        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//---------------------------------------------------------------------------
// Удаляем Расчет
if (isset($_POST['Flag']))
    if ($_POST['Flag'] == 'DelRasch' and isset($_POST['RsID'])) {
        $P->DelRasch($_POST['RsID']);
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//---------------------------------------------------------------------------
// Удаляем Партию
if (isset($_GET['Delete'])) {
    $P->Delete();
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}


//---------------------------------------------------------------------------
// Добавление Платежа в Расчет
if (isset($_POST['RsSumm']) and isset($_POST['RsID']) and isset($_POST['SelPPID'])) {
    $P->AddPayToRas($_POST['RsSumm'], $_POST['RsID'], $_POST['SelPPID']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

//---------------------------------------------------------------------------
// Добавление Накладной
if (isset($_POST['Numb']) and isset($_POST['Nacl']) and isset($_POST['DateR']) and isset($_POST['Oper'])) {
    $P->AddNacl($_POST['Numb'], $_POST['Nacl'], $_POST['DateR'], $_POST['Oper'], $_SESSION['MM_Username']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}


//---------------------------------------------------------------------------
// Формирование Расчетов по схеме АВ-ОК
if (isset($_POST['AVPr']) and isset($_POST['Date'])) {
    $pr = (double)$_POST['AVPr'];
    $pr = round($pr / 100, 2);
    if ($pr > 0 and $pr <= 1) {
        $P->SetPayGraph($pr, $_POST['Date']);
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    } else
        $Err = "Процент Аванса должен быть от 1 до 100.";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Партия</title>
    <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <script src="SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <link href="SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php
include_once("header.php");

?>

<!-- end masthead -->
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <p><?php
        $D->ShowDoc();


        $UserG = array('admin', 'oper', 'manager');

        if (in_array($_SESSION['MM_UserGroup'], $UserG)) {

            if (isset($_POST['Flag'])) {

                if ($_POST['Flag'] == 'AddNacl')
                    $P->ShowPart(1); // Партия + Форма добавления накладной
                else {
                    $P->ShowPart(0);
                } // Партия

                // Форма Редактирования партии
                if ($_POST['Flag'] == 'EditPartForm')
                    $P->EditForm();

            } else
                $P->ShowPart(0); // Партия

            echo '<br>' . Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Редактировать Партию', 'EditPartForm');

            // График платежей
            $P->PayGraph(true);

            // Кнопки по расчетам
            //echo '<br>'.Func::ActButton($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'],'Добавить Расчет','AddRS');
            echo '<br>';
            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Авто-Расчет', 'AddAVOK');
            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Авто-Расчет 100%', 'AddRasch100');
            echo '<br>';

            if (isset($_POST['Flag'])) {
                // Форма для добавления Расчета
                if ($_POST['Flag'] == 'AddRS') {
                    echo '<form id="form1" name="form1" method="post" action="">
                              <table width="293" border="1">
                                <tr>
                                    <td width="105">Сумма</td>
                                    <td width="172">
                                        <span id="sprytextfield1">
                                            <input name="Summ" type="text" id="text1" />
                                            <span class="textfieldRequiredMsg">A value is required.</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Дата</td>
                                    <td>
                                        <span id="sprytextfield2">
                                            <input type="text" name="Date" id="text2" />
                                            <span class="textfieldRequiredMsg">A value is required.</span>
                                            <span class="textfieldInvalidFormatMsg">Invalid format.</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Тип</td>
                                    <td>
                                        <span id="spryselect1">
                                              <select name="Type" id="select1">
                                                  <option value="1">Аванс</option>
                                                  <option value="2">Ок. Расчет</option>
                                              </select>
                                              <span class="selectRequiredMsg">Please select an item.</span>
                                        </span>
                                  </td>
                                </tr>
                              </table>
                                <input type="submit" name="button" id="button" value="Submit" />
                          </form>';
                }

// Авторасчет
                $dt = Func::NowE();
                if ($_POST['Flag'] == 'AddAVOK') {
                    echo "<form id='form1' name='form1' method='post' action=''>
                              <table width='293' border='1'>
                                    <tr>
                                        <td width='105'>Процент АВ</td>
                                            <td width='172'>
                                            <span id='sprytextfield1'>
                                                <input name='AVPr' type='text' id='text1' value='100'/>
                                                <span class='textfieldRequiredMsg'>A value is required.</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Дата</td>
                                        <td>
                                            <span id='sprytextfield2'>
                                                <input type='text' name='Date' id='text2' value='$dt'/>
                                                <span class='textfieldRequiredMsg'>A value is required.</span>
                                                <span class='textfieldInvalidFormatMsg'>Invalid format.</span>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                <input type='submit' name='button' id='button' value='Submit' />
                                <input type='hidden' name='SubmitAddAVOK' value='1' />
                          </form>";

                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена');
                }

            }
            $D->ShowPart(1, 1);
            echo '<br>';
        } else {
            $P->PayGraph(false);
            if (isset($Err)) echo $Err;
        }
        ?>
    </p>
</div>
<script type="text/javascript">
    <!--
    var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "currency");
    var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "date", {format: "dd.mm.yyyy"});
    var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
    var sprytextfield3 = new Spry.Widget.ValidationTextField("Numb", "currency");
    var sprytextfield4 = new Spry.Widget.ValidationTextField("DateR", "date", {format: "dd.mm.yyyy", isRequired: true});
    var sprytextfield5 = new Spry.Widget.ValidationTextField("Nacl", "none");
    var spryselect12 = new Spry.Widget.ValidationSelect("Oper");
    //-->
</script>
</body>
</html>
