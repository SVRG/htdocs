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
        die ('Необходимо перейти в Элемент');

//----------------------------------------------------------------------------------------------------------------------
// Удаление эелента и замен на Комплектующие=1001
if(isset($_GET['setCompl']))
{
    $E->DeleteReplace($E->kod_elem);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

//----------------------------------------------------------------------------------------------------------------------
// Удаление документов
if (isset($_POST['DelDocum'])) {
    $docum = new Docum();
    $docum->Delete($_POST['DelDocum']);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

//----------------------------------------------------------------------------------------------------------------------
// Сохранить
if (isset($_POST['Flag']))
    if ($_POST['Flag'] == 'SaveElem' and isset($_POST['obozn']) and isset($_POST['name'])) {
            $E->Save($_POST['obozn'], $_POST['name'], $_POST['shablon'], $_POST['shifr']);

        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }


?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
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

            if (in_array($_SESSION['MM_UserGroup'], $UserG))
                $Del = 1;
            else
                $Del = 0;

            $Form = '';
            if (in_array($_SESSION['MM_UserGroup'], $UserG))
                $Form = Func::ActButton('', 'Изменить', 'EditForm');

            if (isset($_POST['Flag']))
                if ($_POST['Flag'] == 'EditForm') {
                    $Form = $E->Form();
                }

            ?>

            <!-- end masthead -->
            <div class="style1" id="pagecell1">
                <!--pagecell1-->
                <table width="100%" border="0">
                    <tr valign="top">
                        <td align="left" bgcolor="#ECEEFD">
                            <?php
                            echo $E->getPhoto() . '<br>';

                            echo '<h1>' . $E->Name('name',0). '</h1><br>'. $E->Data['shifr'] .'<br>'. $Form . '<br>';
                            ?>
                            <div id="CollapsiblePanel1" class="CollapsiblePanel">
                                <div class="CollapsiblePanelTab">Прикрепленные Файлы</div>
                                <div class="CollapsiblePanelContent">
                                    <?php echo '<br>' . $E->Docum($Del);?>
                                </div>
                            </div>
                        </td>
                        <td align="left">
                            <?php
                            echo ' <div id="CollapsiblePanel2" class="CollapsiblePanel">
                                        <div class="CollapsiblePanelTab" tabindex="0">Потребители</div>
                                        <div class="CollapsiblePanelContent">' . $E->OrgByElem() . '</div>
                                  </div>';
                            ?>
                        </td>
                    </tr>
                </table>
                <div id="CollapsiblePanel3" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Договоры</div>
                    <div class="CollapsiblePanelContent">
                        <?php
                        echo $E->getDocs();
                        ?>
                    </div>
                </div>


                <script type="text/javascript">
                    <!--
                    var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen: false});
                    var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen: false});
                    var CollapsiblePanel3 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel3", {contentIsOpen: false});
                    //-->
                </script>
            </div>
        </body>
    </html>