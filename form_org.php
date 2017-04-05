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

$c = new Kontact();
$c->kod_org = $kod_org;

//---------------------------------------------------------------------------
// Добавление Реквизитов
if (isset($_POST['AddRecvForm'])) {
    $Org->SetRecv($_POST['inn'], $_POST['kpp'], $_POST['r_sch'], $_POST['bank_rs'], $_POST['k_sch'], $_POST['bank_ks'], $_POST['bik'], $_POST['okpo'], $_POST['okonh'], $_POST['www'], $_POST['e_mail']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------
// Добавление Контактов
if (isset($_POST['AddContact']) and (isset($_POST['FName']) or isset($_POST['Name']))) {
    $Org->AddCont($_POST['Dolg'], $_POST['FName'], $_POST['Name'], $_POST['PName']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------
// Вставка Телефон в Контакт
if (isset($_POST['AddPhone']))
    if (isset($_POST['ContID']) and isset($_POST['Numb'])) {
        $c = new Kontact();
        $c->kod_kontakta = $_POST['ContID'];
        $c->AddPhone($_POST['Numb']);

        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//---------------------------------------------------------------------------
// Добавление Телефона в Организацию
if (isset($_POST['AddOrgPhone']) and (isset($_POST['phone']))) {
    $Org->AddPhone($_POST['phone'], $_POST['prim']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------
// Добавление Адреса в организацию
if (isset($_POST['AddOrgAdr']) and (isset($_POST['adres']))) {
    $Org->AddAdr($_POST['adres'], $_POST['type']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------
// Добавить Телефон в Контакт
if (isset($_POST['AddPhone']) and isset($_POST['Numb']) and isset($_POST['ContID'])) {
    $Org->AddCont($_POST['Dolg'], $_POST['FName'], $_POST['Name'], $_POST['PName']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}
//---------------------------------------------------------------------------
// Сохранить
if(isset($_POST['FormName']))
    if($_POST['FormName']=="FormAddEdit")
        if (isset($_POST['poisk']) and isset($_POST['nazv_krat']) and isset($_POST['nazv_poln'])) {
            if ($_POST['poisk'] != '' and $_POST['nazv_krat'] != '' and $_POST['nazv_poln'] != '') {
                $Org->Save($_POST['poisk'], $_POST['nazv_krat'], $_POST['nazv_poln']);
            }
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
    <title><?php echo $Org->Data['nazv_krat']; ?></title>
    <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <script src="SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php include("header.php"); ?>
<!-- end masthead -->
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <table width="100%" border="0">
        <tr>
            <td width="502" valign="top" bgcolor="#ECEEFD"><?php
                //---------------------------------------------------------------------------------
                // Реквизиты
                $nazv_krat = $Org->Data['nazv_krat'];
                $nazv_poln = $Org->Data['nazv_poln'];

                if ($nazv_krat != $nazv_poln)
                    echo '<br><h1>' . $nazv_krat . '</h1><br>' . $nazv_poln . '<br>';
                else
                    echo '<br><h1>' . $nazv_krat . '</h1><br>';

                echo $Org->Data['poisk'];

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

                echo '<br>Задолженность: ' . $Org->Dolg();

                ?>
                <div id="CollapsiblePanel1" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Реквизиты</div>
                    <div class="CollapsiblePanelContent">
                        <?php

                        // Документы
                        echo '<br>' . $Org->Docum();

                        $Edit = 0;

                        // Реквизиты
                        $Edit = 0;
                        if (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                            if (isset($_POST['Flag']))
                                if ($_POST['Flag'] == 'SetRecv')
                                    $Edit = 1;
                        }
                        $Org->ShowRecv($Edit);

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

                        echo $Org->getAdressList($Add);
                        //---------------------------------------------------------------------------------
                        // Телефоны
                        $Add = 0;
                        if (isset($_POST['Flag']))
                            if ($_POST['Flag'] == 'AddOrgPhone')
                                $Add = 1;

                        echo $Org->getPhones($Add);

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
                            echo $c->Contacts(1, "Org");
                        else
                            echo $c->Contacts(0, "Org");
                        ?>
                    </div>
                </div>
                <?php
                if (in_array($_SESSION['MM_UserGroup'], $UserG))
                    echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Контакт', 'AddCont');

                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'AddCont') {
                        echo '<form id="form1" name="form1" method="post" action="">
                                  <table width="436" border="0">
                                    <tr>
                                      <td width="133">Должность</td>
                                      <td width="287">
                                        <input type="text" name="Dolg" id="Dolg" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Фамилия</td>
                                      <td><span id="sprytextfield2">
                                        <input type="text" name="FName" id="FName" />
                                      </span></td>
                                    </tr>
                                    <tr>
                                      <td>Имя</td>
                                      <td><span id="sprytextfield3">
                                        <input type="text" name="Name" id="Name" />
                                      </span></td>
                                    </tr>
                                    <tr>
                                      <td>Отчество</td>
                                      <td><span id="sprytextfield4">
                                        <input type="text" name="PName" id="PName" />
                                      </span></td>
                                    </tr>
                                    <tr>
                                      <td>Примечание</td>
                                      <td><span id="sprytextfield5">
                                        <input type="text" name="Prim" id="Prim" />
                                      </span></td>
                                    </tr>
                                  </table>
                                  <p>
                                    <input type="submit" name="button" id="button" value="Добавить" />
                                    <input type="hidden" name="AddContact" id="button" value="1" />
                                  </p>
                                </form>';
                        Func::Cansel();
                    }
                ?>
                <div id="CollapsiblePanel3" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Номенклатура по Договорам</div>
                    <div class="CollapsiblePanelContent">
                        <?php echo $Org->OrgNomen(); ?>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div id="CollapsiblePanel4" class="CollapsiblePanel">
                    <div class="CollapsiblePanelTab">Договоры</div>
                    <div class="CollapsiblePanelContent"><?php echo $Org->getDocs(); ?></div>
                </div>
            </td>
        </tr>
    </table>
    <script type="text/javascript">
        <!--
        var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen: true});
        var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen: true});
        var CollapsiblePanel3 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel3", {contentIsOpen: true});
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