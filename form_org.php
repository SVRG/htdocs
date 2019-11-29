<?php
include_once "security.php";

$kod_org = 0;
if (isset($_POST['kod_org']))
    $kod_org = (int)$_POST['kod_org'];
elseif (isset($_GET['kod_org']))
    $kod_org = (int)$_GET['kod_org'];

$UserG = array('admin', 'oper', 'manager');

include_once('class_org.php');
$org = new Org();
$org->kod_org = $kod_org;
$org->getData();
$org->Events();


$Kontakt = new Kontakt();
$Kontakt->kod_org = $kod_org;
$Kontakt->Events();

$Doc = new Doc();
$Doc->kod_org = $org->kod_org;
try {
    $Doc->Events();
} catch (phpmailerException $e) {
}

Docum::Events();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo $org->Data['nazv_krat']; ?></title>
    <script src="widgets/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <script src="widgets/SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css"/>

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="widgets/selectize/dist/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="widgets/selectize/dist/css/selectize.default.css">
</head>
<body>
<?php include("header.php"); ?>
<div id="pagecell1">
    <table width="100%" border="0">
        <tr>
            <td width="502" valign="top" bgcolor="#ECEEFD"><?php
                //---------------------------------------------------------------------------------
                // Организация
                echo $org->formOrg();
                // Задолженность
                echo '<br>К оплате: ' . $org->getDolg();
                echo '<br>По отгрузкам: ' . $org->getDolgOtgruz();
                ?>
                <div id="CollapsiblePanel1" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Реквизиты</div>
                    <div class="CollapsiblePanelContent">
                        <?php
                        // Документы
                        echo $org->Docum();
                        // Реквизиты
                        echo $org->formRecv();
                        //---------------------------------------------------------------------------------
                        // Адреса
                        echo "<br>" . $org->formAdress();
                        //---------------------------------------------------------------------------------
                        // Телефоны
                        echo "<br>" . $org->formPhones();
                        // Связи
                        echo "<br>" . Org::formLinks($org->kod_org);
                        ?>
                    </div>
                </div>
            </td>
            <td width="501" valign="top">
                <div id="CollapsiblePanel2" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Контакты</div>
                    <div class="CollapsiblePanelContent">
                        <?php
                        if (in_array($_SESSION['MM_UserGroup'], $UserG))
                            echo $Kontakt->formKontakts(1, "Org");
                        else
                            echo $Kontakt->formKontakts(0, "Org");
                        ?>
                    </div>
                </div>
                <?php
                if (in_array($_SESSION['MM_UserGroup'], $UserG))
                    echo Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить', 'AddKontakt');

                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'AddKontakt') {
                        echo $Kontakt->formAddEdit();
                    }
                ?>
                <div id="CollapsiblePanel3" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Номенклатура по Договорам</div>
                    <div class="CollapsiblePanelContent">
                        <?php echo $org->formOrgNomen(); ?>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div id="CollapsiblePanel4" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Договоры</div>
                    <table class="CollapsiblePanelContent">
                        <table border="0" width="100%">
                            <tr>
                                <td><?php echo Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить', 'AddDogovor'); ?></td>
                                <td><?php echo Doc::formQuickAdd($kod_org); ?></td>
                            </tr>
                        </table>
                        <?php
                        if (isset($_POST['Flag']))
                            if ($_POST['Flag'] == "AddDogovor")
                                echo $Doc->formAddEdit();


                        if ($kod_org != config::$kod_org_main) // Чтобы не выводить все договоры
                            try {
                                echo $org->formDocs();
                            } catch (Exception $e) {
                            }
                        ?>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    <!--
    let CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen: true});
    let CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen: false});
    let CollapsiblePanel3 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel3", {contentIsOpen: false});
    let CollapsiblePanel4 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel4", {contentIsOpen: true});
    let sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {isRequired: true});
    let sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {isRequired: false});
    let sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {isRequired: false});
    let sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "none", {isRequired: false});
    let sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "none", {isRequired: false});
    let sprytextfield6 = new Spry.Widget.ValidationTextField("sprytextfield6");
    let sprytextfield_poisk = new Spry.Widget.ValidationTextField("sprytextfield_poisk", "none", {isRequired: true});
    let sprytextfield_nazv_krat = new Spry.Widget.ValidationTextField("sprytextfield_nazv_krat", "none", {isRequired: true});
    let sprytextfield_nazv_poln = new Spry.Widget.ValidationTextField("sprytextfield_nazv_poln", "none", {isRequired: true});
    //-->
</script>
</body>
</html>