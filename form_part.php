<?php
if(isset($_GET['help']))
{
    echo /** @lang HTML */
    "
    <b>Команды управления:</b><br>
    help - выводит подсказку<br>
    edit - разрешает редактирование партий после оплаты<br>
    del - разрешает удаление партии<br>
    hist - история по партии<br>
    ";
    exit("----");
}
include_once "security.php";
$UserG = array('admin', 'oper', 'manager');
$UserG1 = array('admin', 'oper');
include_once("class_part.php");
include_once("class_doc.php");

// todo - переделаль в нормальный вид
$kod_part = 0;
$kod_dogovora = 0;
$Dogovor = new Doc();
$Part = new Part();

// Если код партии передан в форме POST
if (isset($_POST['kod_part'])) {
    if ($_POST['kod_part'] != 0) {
        $kod_part = (int)$_POST['kod_part'];
        $p_data = $Part->getData($kod_part);
        $kod_dogovora = $p_data['kod_dogovora'];
    }
} // Если код партии передан в запросе GET
elseif (isset($_GET['kod_part'])) {
    $kod_part = (int)$_GET['kod_part'];
    if (!isset($_GET['kod_dogovora'])) {
        $p_data = $Part->getData($kod_part);
        $kod_dogovora = $p_data['kod_dogovora'];
    } else
        $kod_dogovora = (int)$_GET['kod_dogovora'];
} // Если код договора передан в запросе GET
elseif (isset($_GET['kod_dogovora'])) {
    $kod_dogovora = (int)$_GET['kod_dogovora'];
    $kod_part = Part::getFirstPartKod($kod_dogovora);
} else
    exit("Не задан Код партии и Код договора");

$Dogovor->kod_dogovora = $kod_dogovora;
try {
    $Dogovor->Events();
} catch (phpmailerException $e) {
}

$Part->kod_part = $kod_part;
$Part->kod_dogovora = $kod_dogovora;
$Part->Events();
?>
<!DOCTYPE html>
<head>
    <title>Партия</title>
    <script src="widgets/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <link href="widgets/SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css"/>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Test</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <script src="js/jquery.min.js"></script>
    <script src="widgets/selectize/dist/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="widgets/selectize/dist/css/selectize.default.css">
    <script src="js/index.js"></script>
</head>
<body>
<?php
include_once("header.php");

?>

<!-- end masthead -->
<div class="style1" id="pagecell1">
    <!--pagecell1-->
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

    if (in_array($_SESSION['MM_UserGroup'], $UserG)) {

        if (isset($_POST['Flag'])) {

            if ($_POST['Flag'] == 'AddNaklad')
                try {
                    echo $Part->formPart(1);
                } catch (Exception $e) {
                } // Партия + Форма добавления накладной
            else {
                try {
                    echo $Part->formPart(0);
                } catch (Exception $e) {
                }
            } // Партия

            if (in_array($_SESSION['MM_UserGroup'], $UserG1)) {
                // Форма Редактирования партии
                if ($_POST['Flag'] == 'EditPartForm') {
                    echo $Part->formAddEdit(1);
                } elseif ($_POST['Flag'] == 'AddPartForm')
                    echo $Part->formAddEdit(0);
            }
        } else
            try {
                echo $Part->formPart(0);
            } catch (Exception $e) {
            } // Партия

        // График платежей
        echo $Part->formPayGraph(true);

        if (func::user_group() == "admin" and isset($_GET['hist']))
            echo Part::formHistory($kod_part);

        if (isset($_POST['Flag'])) {
            // Форма для добавления Расчета
            // Авторасчет
            if ($_POST['Flag'] == 'AddAVOK') {
                echo $Part->formAddAVOK();
            }

        }
        echo $Dogovor->formParts(1);
        echo '<br>';
    } else {
        echo $Part->formPayGraph(false);

        if (isset($Err))
            echo $Err;
    }
    ?>
</div>
<script type="text/javascript">
    let sprytextfield_AVPr = new Spry.Widget.ValidationTextField("sprytextfield_AVPr", "currency", {isRequired: true});
    let sprytextfield_data = new Spry.Widget.ValidationTextField("sprytextfield_data", "date", {format: "dd.mm.yyyy"});
    let sprytextfield3 = new Spry.Widget.ValidationTextField("Numb", "currency");
    let sprytextfield4 = new Spry.Widget.ValidationTextField("data", "date", {format: "dd.mm.yyyy", isRequired: true});
    let sprytextfield5 = new Spry.Widget.ValidationTextField("naklad", "none");
    let spryselect12 = new Spry.Widget.ValidationSelect("operator");
    //-->
</script>
</body>