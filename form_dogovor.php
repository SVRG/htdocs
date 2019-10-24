<?php

if(isset($_GET['help']))
{
    echo /** @lang HTML */
    "
    <b>Команды управления:</b><br>
    help - выводит подсказку<br>
    edit - разрешает редактирование договора и партий после оплаты<br>
    del - разрешает удаление партии<br>
    hist - история по договору<br>
    ";
    exit("----");
}

include_once "security.php";

$UserG = array('admin', 'oper');
$UserG1 = array('admin', 'oper', 'manager');

include_once("class_doc.php");

if (!isset($_GET['kod_dogovora']) and !isset($_POST['kod_dogovora']))
    exit("Не задан Договор");
$Dogovor = new Doc();
if (isset($_GET['kod_dogovora']))
    $Dogovor->kod_dogovora = (int)$_GET['kod_dogovora'];
else
    $Dogovor->kod_dogovora = (int)$_POST['kod_dogovora'];
$Dogovor->getData();
try {
    $Dogovor->Events();
} catch (phpmailerException $e) {
}

$Part = new Part();
$Part->kod_dogovora = $Dogovor->kod_dogovora;
if (isset($_POST['kod_part'])) // Если был передан код партии
    $Part->kod_part = $_POST['kod_part'];
$Part->Events();

$Kontakt = new Kontakt();
$Kontakt->kod_dogovora = $Dogovor->kod_dogovora;
$Kontakt->Events();

Docum::Events();

//---------------------------------------------------------------------------
// Удаление документов
if (isset($_POST['DelDocum'])) {
    $docum = new Docum();
    $docum->Delete($_POST['DelDocum']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $Dogovor->Data['nomer'] ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <script src="widgets/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="widgets/selectize/dist/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="widgets/selectize/dist/css/selectize.default.css">
    <script type="text/javascript" src="js/index.js"></script>
</head>
<body>
<?php include("header.php");
if (in_array($_SESSION['MM_UserGroup'], $UserG))
    $Del = 1;
else
    $Del = 0;
?>
<div class="style1" id="pagecell1">
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

                if (!$edit) {
                    if (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                        $Dogovor->formDogovor(0, 1);
                    } else
                        $Dogovor->formDogovor();
                }

                if (isset($_GET['hist']))
                    echo Doc::formHistory($Dogovor->kod_dogovora);

                echo $Dogovor->formDocum(); // Документы договора
                echo $Dogovor->formAttributes(); // Аттрибуты
                echo $Dogovor->formLinks(); // Ссылки на другие договоры

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
                            $_POST['Flag'] = null;
                            $add_kont = true;
                        }

                    if (!$add_kont) {
                        // Вывод контактов
                        $Dogovor->formDocKontakts(1);
                    }
                } elseif (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                    $Dogovor->formDocKontakts(1);
                } else
                    $Dogovor->formDocKontakts(0);

                ?></td>
        </tr>
        <tr valign="top">
            <td colspan="2" align="left">
                <?php
                if (isset($_POST['Flag'])) {
                    if ($_POST['Flag'] == 'AddPartForm')
                        echo $Part->formAddEdit(0);
                    elseif ($_POST['Flag'] == 'AddNaklad' and isset($_POST['kod_part'])) {
                        $Part->kod_part = $_POST['kod_part'];
                        try {
                            echo $Part->formPart(1);
                        } catch (Exception $e) {
                        }
                    }
                }
                // Партии
                try {
                    echo $Part->formParts(1, "", 0);
                    if(isset($_GET['hist']))
                        echo "<b>Удаленные</b>".$Part->formPartsDeleted();
                } catch (Exception $e) {
                }
                ?>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td width="50%" align="left" valign="top">
                <?php

                // Платежи - ------------------
                echo $Dogovor->formPlat();
                ?>  </td>
            <td width="50%" align="left" valign="top">
                <?php
                // Счета ------------------------------------
                echo $Dogovor->formInvoice();
                ?></td>
        </tr>
        <tr>
            <td align="left" valign="top">
            </td>
            <td align="left" valign="top">
                <?php
                //-----------------------------------------------------------------------------------------------------
                // Примечание
                echo $Dogovor->formPrim();
                ?></td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    <!--
    var sprytextfield2 = new Spry.Widget.ValidationTextField("SNumR", "none", {minChars: 1});
    var sprytextfield3 = new Spry.Widget.ValidationTextField("SDateR", "date", {format: "dd.mm.yyyy"});
    //var sprytextfield4 = new Spry.Widget.ValidationTextField("SSummR", "currency");
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