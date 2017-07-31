<?php
if (!isset($_SESSION)) {
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

include_once("class_elem.php");
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
    <html>
    <!-- DW6 -->
    <head>
        <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
        <title>Элемент</title>
        <script src="SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
        <link href="SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css"/>
    </head>
        <body>
            <?php
            include("header.php");
            $Del = 0;
            if (in_array($_SESSION['MM_UserGroup'], $UserG))
                $Del = 1;

            $Form = '';
            if (in_array($_SESSION['MM_UserGroup'], $UserG))
                $Form = Func::ActButton('', 'Изменить', 'formAddEdit');

            if (isset($_POST['Flag']))
                if ($_POST['Flag'] == 'formAddEdit') {
                    $Form = $E->formAddEdit(1);
                }

            ?>

            <!-- end masthead -->
            <div class="style1" id="pagecell1">
                <!--pagecell1-->
                <table width="100%" border="0">
                    <tr valign="top">
                        <td align="left" bgcolor="#ECEEFD">
                            <?php
                            echo $E->formPhoto() . '<br>';

                            echo '<h1>' . $E->Name('name',0). '</h1><br>'. $E->Data['shifr'] .'<br>'. $Form . '<br>';
                            ?>
                            <div id="CollapsiblePanel1" class="CollapsiblePanel">
                                <div class="CollapsiblePanelTab">Прикрепленные Файлы</div>
                                <div class="CollapsiblePanelContent">
                                    <?php echo $E->formDocum();?>
                                </div>
                            </div>
                        </td>
                        <td align="left">
                            <?php
                            echo ' <div id="CollapsiblePanel2" class="CollapsiblePanel">
                                        <div class="CollapsiblePanelTab" tabindex="0">Потребители</div>
                                        <div class="CollapsiblePanelContent">
                                            <a href="form_kontlist.php?kod_elem='.$E->kod_elem.'">Контакты</a>' . $E->formOrgByElem() . '
                                        </div>
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
                    //-->
                </script>
            </div>
        </body>
    </html>