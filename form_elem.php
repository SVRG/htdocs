<?php
include_once "security.php";
include_once("class_elem.php");
$UserA = array('admin');
$UserG = array('admin', 'oper');
$UserG1 = array('admin', 'oper', 'manager');

$UN = array('tikhomirov');

$E = new Elem();

if (isset($_GET['kod_elem'])) {
    $E->kod_elem = $_GET['kod_elem'];
} else
    if (isset($_POST['kod_elem']))
        $E->kod_elem = $_POST['kod_elem'];
    else
        die ('Необходимо указать Код Элемента');

$E->Events();

Docum::Events();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Элемент</title>
    <script src="widgets/SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css"/>

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Test</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="widgets/selectize/dist/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="widgets/selectize/dist/css/selectize.default.css">
    <script type="text/javascript" src="js/index.js"></script>
</head>
<body>
<?php
include("header.php");
$Del = 0;
if (in_array($_SESSION['MM_UserGroup'], $UserG))
    $Del = 1;
?>

<!-- end masthead -->
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <table width="100%" border="0">
        <tr valign="top">
            <td align="left" bgcolor="#ECEEFD">
                <?php
                $link_modif = "<a href='form_nomen.php?kod_elem=$E->kod_elem'><img alt='Modif' title='Посмотреть модификации' src='img/view_properties.png'></a>";
                $E->getData();
                $web_link = Elem::formWEBLink($E->Data);

                echo $E->formPhoto() . '<br>';
                echo $E->formName('all', 1) . $link_modif." $web_link";
                echo $E->formPriceList();
                ?>
                <div id="CollapsiblePanel1" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Прикрепленные Файлы</div>
                    <div class="CollapsiblePanelContent">
                        <?php echo $E->formDocum(); ?>
                    </div>
                </div>
            </td>
            <td align="left">
                <?php
                echo ' <div id="CollapsiblePanel2" class="CollapsiblePanel">
                                        <div class="CollapsiblePanelTab" tabindex="0">Потребители</div>
                                        <div class="CollapsiblePanelContent">
                                            <a href="form_kontlist.php?kod_elem=' . $E->kod_elem . '">Контакты</a>' . $E->formOrgByElem() . '
                                        </div>
                                  </div>';

                echo ' <div id="CollapsiblePanel5" class="CollapsiblePanel">
                                        <div class="CollapsiblePanelTab" tabindex="0">Поставщики</div>
                                        <div class="CollapsiblePanelContent">
                                            ' . $E->formSuppliers() . '
                                        </div>
                                  </div>';

                echo ' <div id="CollapsiblePanel4" class="CollapsiblePanel">
                                        <div class="CollapsiblePanelTab" tabindex="0">Спецификация</div>
                                        <div class="CollapsiblePanelContent">'.
                                            $E->formSpecTotal()
                                    .' </div>
                                  </div>';
                ?>
            </td>
        </tr>
    </table>
    <div id="CollapsiblePanel3" class="CollapsiblePanel">
        <div class="CollapsiblePanelTab">Договоры</div>
        <div class="CollapsiblePanelContent">
            <?php
            echo $E->formDocs();
            ?>
        </div>
    </div>
    <script type="text/javascript">
        <!--
        var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen: false});
        var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen: false});
        var CollapsiblePanel3 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel3", {contentIsOpen: true});
        var CollapsiblePanel4 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel4", {contentIsOpen: false});
        var CollapsiblePanel5 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel5", {contentIsOpen: false});
        //-->
    </script>
</div>
</body>
</html>