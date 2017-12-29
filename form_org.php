<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

$kod_org = 0;
if (isset($_POST['kod_org']))
    $kod_org = $_POST['kod_org'];
elseif (isset($_GET['kod_org']))
    $kod_org = $_GET['kod_org'];

$UserG = array('admin', 'oper', 'manager');

include_once('class_org.php');
$Org = new Org();
$Org->kod_org = $kod_org;
$Org->getData();
$Org->Events();


$Kontakt = new Kontakt();
$Kontakt->kod_org = $kod_org;
$Kontakt->Events();

$Doc = new Doc();
$Doc->kod_org = $Org->kod_org;
$Doc->Events();

Docum::Events();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title><?php echo $Org->Data['nazv_krat']; ?></title>
    <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <script src="SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php include("header.php"); ?>
<div id="pagecell1">
    <table width="100%" border="0">
        <tr>
            <td width="502" valign="top" bgcolor="#ECEEFD"><?php
                //---------------------------------------------------------------------------------
                // Реквизиты
                $nazv_krat = $Org->Data['nazv_krat'];
                $nazv_poln = $Org->Data['nazv_poln'];

                if ($nazv_krat != $nazv_poln)
                    echo '<br><h1>' . $Org->getFormLink() . '</h1><br>' . $nazv_poln . '<br>';
                else
                    echo '<br><h1>' . $Org->getFormLink() . '</h1><br>';

                echo $Org->Data['poisk'];

                if($_SESSION['MM_UserGroup']==="admin")
                    echo Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Удалить', 'DelOrg',"kod_org_del",$Org->kod_org);

                // SAve-------------------------
                $Edit = 0;
                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'formAddEdit') {
                        echo $Org->formAddEdit(1);
                        $Edit = 1;
                    }

                if ($Edit == 0)
                    if (in_array($_SESSION['MM_UserGroup'], $UserG))
                        echo Func::ActButton('', 'Изменить Название', 'formAddEdit');
                echo '<br>';
                // SAve-------------------------

                // Задолженность

                echo '<br>Задолженность: ' . $Org->getDolg();

                ?>
                <div id="CollapsiblePanel1" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Реквизиты</div>
                    <div class="CollapsiblePanelContent">
                        <?php

                        // Документы
                        echo '<br>' . $Org->Docum();

                        // Связи
                        if (isset($_POST['Flag']))
                            if ($_POST['Flag'] == 'AddOrgLinkForm')
                            {
                                echo $Org->formAddOrgLink();
                            }
                        echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Связь', 'AddOrgLinkForm');
                        echo Org::formOrgLinks($Org->kod_org);

                        // Реквизиты
                        $Edit = 0;
                        if (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                            if (isset($_POST['Flag']))
                                if ($_POST['Flag'] == 'SetRecv')
                                    $Edit = 1;
                        }
                        $Org->formRecv($Edit);

                        if (in_array($_SESSION['MM_UserGroup'], $UserG))
                            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Изменить реквизиты', 'SetRecv');

                        //---------------------------------------------------------------------------------
                        // Адреса
                        $Add = 0;
                        if (isset($_POST['Flag']))
                            if ($_POST['Flag'] == 'AddOrgAdr')
                                $Add = 1;
                        if (in_array($_SESSION['MM_UserGroup'], $UserG) and $Add != 1) {
                            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Адрес', 'AddOrgAdr');
                        }

                        echo $Org->formAdressList($Add);
                        //---------------------------------------------------------------------------------
                        // Телефоны
                        $Add = 0;
                        if (isset($_POST['Flag']))
                            if ($_POST['Flag'] == 'AddOrgPhone')
                                $Add = 1;

                        echo $Org->formPhones($Add);

                        if (in_array($_SESSION['MM_UserGroup'], $UserG) and $Add != 1) {
                            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Телефон', 'AddOrgPhone');
                        }
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
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Контакт', 'AddKontakt');

                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'AddKontakt') {
                        echo $Kontakt->formAddEdit();
                        Func::Cansel(1);
                    }
                ?>
                <div id="CollapsiblePanel3" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Номенклатура по Договорам</div>
                    <div class="CollapsiblePanelContent">
                        <?php echo $Org->formOrgNomen(); ?>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div id="CollapsiblePanel4" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Договоры</div>
                    <div class="CollapsiblePanelContent">
                        <?php

                        echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Договор', 'AddDogovor');

                        if(isset($_POST['Flag']))
                                if($_POST['Flag']=="AddDogovor")
                                    echo $Doc->formAddEdit();

                        if($kod_org!=683) // Чтобы не выводить все договоры
                            echo $Org->formDocs();
                        ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <script type="text/javascript">
        <!--
        var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen: true});
        var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen: false});
        var CollapsiblePanel3 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel3", {contentIsOpen: false});
        var CollapsiblePanel4 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel4", {contentIsOpen: true});
        var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {isRequired: true});
        var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {isRequired: false});
        var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {isRequired: false});
        var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "none", {isRequired: false});
        var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "none", {isRequired: false});
        var sprytextfield6 = new Spry.Widget.ValidationTextField("sprytextfield6");
        var sprytextfield_poisk = new Spry.Widget.ValidationTextField("sprytextfield_poisk", "none", {isRequired: true});
        var sprytextfield_nazv_krat = new Spry.Widget.ValidationTextField("sprytextfield_nazv_krat", "none", {isRequired: true});
        var sprytextfield_nazv_poln = new Spry.Widget.ValidationTextField("sprytextfield_nazv_poln", "none", {isRequired: true});
        //-->
    </script>
</div>
</body>
</html>