<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

$UserG = array('admin', 'oper', 'manager');
$UserG1 = array('admin', 'oper');

include_once "security.php";

include_once("class_part.php");
include_once("class_doc.php");

// todo - переделаль в нормальный вид
$kod_part = 0;
if (!isset($_GET['kod_part'])) {
    if (!isset($_GET['kod_dogovora']))
        exit("Не задан Код партии и договора");
    else {
        $kod_part = Part::getFirstPartKod($_GET['kod_dogovora']);
    }
} else
    $kod_part = $_GET['kod_part'];

// Если код передан в форме
if (isset($_POST['kod_part'])) {
    if ($_POST['kod_part'] != 0)
        $kod_part = $_POST['kod_part'];
}

$Dogovor = new Doc();
$Dogovor->kod_dogovora = (int)$_GET['kod_dogovora'];
$Dogovor->Events();

$Part = new Part();
$Part->kod_part = $kod_part;
$Part->kod_dogovora = (int)$_GET['kod_dogovora'];
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
                echo $Part->formPart(1); // Партия + Форма добавления накладной
            else {
                echo $Part->formPart(0);
            } // Партия

            if (in_array($_SESSION['MM_UserGroup'], $UserG1)) {
                // Форма Редактирования партии
                if ($_POST['Flag'] == 'EditPartForm') {
                    echo $Part->formAddEdit(1);
                } elseif ($_POST['Flag'] == 'AddPartForm')
                    echo $Part->formAddEdit(0);
            }
        } else
            echo $Part->formPart(0); // Партия

        // График платежей
        echo $Part->formPayGraph(true);

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
    var sprytextfield_AVPr = new Spry.Widget.ValidationTextField("sprytextfield_AVPr", "currency", {isRequired: true});
    var sprytextfield_data = new Spry.Widget.ValidationTextField("sprytextfield_data", "date", {format: "dd.mm.yyyy"});
    var sprytextfield3 = new Spry.Widget.ValidationTextField("Numb", "currency");
    var sprytextfield4 = new Spry.Widget.ValidationTextField("data", "date", {format: "dd.mm.yyyy", isRequired: true});
    var sprytextfield5 = new Spry.Widget.ValidationTextField("naklad", "none");
    var spryselect12 = new Spry.Widget.ValidationSelect("operator");
    //-->
</script>
</body>