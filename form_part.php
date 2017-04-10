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

    $P->AddEdit($_POST['kod_elem'], $_POST['numb'], $_POST['data_postav'], $_POST['price'], $_POST['modif'], $_POST['nds'], $_POST['val']);

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
if(isset($_POST['AddEditNacl']))
    if (isset($_POST['numb']) and isset($_POST['naklad']) and isset($_POST['data']) and isset($_POST['kod_oper'])) {
        $P->AddNacl($_POST['numb'], $_POST['naklad'], $_POST['data'], $_POST['kod_oper'], $_SESSION['MM_Username']);

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
    <?php
        $D->formDogovor();


        $UserG = array('admin', 'oper', 'manager');

        if (in_array($_SESSION['MM_UserGroup'], $UserG)) {

            if (isset($_POST['Flag'])) {

                if ($_POST['Flag'] == 'AddNacl')
                    $P->formPart(1); // Партия + Форма добавления накладной
                else {
                    $P->formPart(0);
                } // Партия

                // Форма Редактирования партии
                if ($_POST['Flag'] == 'EditPartForm')
                    $P->formAddEdit();

            } else
                $P->formPart(0); // Партия

            // График платежей
            $P->formPayGraph(true);

            // Кнопки по расчетам
            //echo '<br>'.Func::ActButton($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'],'Добавить Расчет','AddRS');
            echo '<br>';
            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Авто-Расчет', 'AddAVOK');
            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Авто-Расчет 100%', 'AddRasch100');
            echo '<br>';

            if (isset($_POST['Flag'])) {
                // Форма для добавления Расчета
                // Авторасчет
                $dt = Func::NowE();
                if ($_POST['Flag'] == 'AddAVOK') {
                    echo "<form id='form1' name='form1' method='post' action=''>
                              <table width='293' border='0'>
                                    <tr>
                                        <td width='105'>Процент АВ</td>
                                            <td width='172'>
                                            <span id='sprytextfield_AVPr'>
                                                <input name='AVPr' type='text' id='text1' value='100'/>
                                                <span class='textfieldRequiredMsg'>A value is required.</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Дата</td>
                                        <td>
                                            <span id='sprytextfield_data'>
                                                <input type='text' name='data' id='data' value='$dt'/>
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
            $D->formParts(1);
            echo '<br>';
        } else {
            $P->formPayGraph(false);
            if (isset($Err)) echo $Err;
        }
        ?>
</div>
<script type="text/javascript">
    <!--
    var sprytextfield_AVPr = new Spry.Widget.ValidationTextField("sprytextfield_AVPr", "currency",{isRequired: true});
    var sprytextfield_data = new Spry.Widget.ValidationTextField("sprytextfield_data", "date", {format: "dd.mm.yyyy"});
    var sprytextfield3 = new Spry.Widget.ValidationTextField("Numb", "currency");
    var sprytextfield4 = new Spry.Widget.ValidationTextField("data", "date", {format: "dd.mm.yyyy", isRequired: true});
    var sprytextfield5 = new Spry.Widget.ValidationTextField("naklad", "none");
    var spryselect12 = new Spry.Widget.ValidationSelect("operator");
    //-->
</script>
</body>
</html>
