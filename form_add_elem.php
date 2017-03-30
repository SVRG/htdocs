<?php
$MM_authorizedUsers = "oper,admin";
$MM_donotCheckaccess = "true";

include_once ("security.php");


include_once("class_elem.php");

// Добавить Элемент
if (isset($_POST['Ob']) and isset($_POST['Name'])) {
    $d = new Elem();
    $d->AddElem($_POST['Ob'], $_POST['Name'], $_POST['Shifr']);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Список Договоров</title>
    <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <script src="SpryAssets/SpryValidationTextarea.js" type="text/javascript"></script>
    <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <link href="SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css"/>
</head>
    <body>
    <?php include("header.php"); ?>
        <div class="style1" id="pagecell1">
            <!--pagecell1-->
            <div id="pageName">
                <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

                    <table width="802" border="1">
                        <tr>
                            <td width="112">Обозначение</td>
                            <td width="518"><span id="sprytextfield1">
                                <input title="Ob" name="Ob" type="text" id="Ob" size="100%"/>
                                <span class="textfieldRequiredMsg">A value is required.</span></span>&nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td>Наименование</td>
                            <td><span id="sprytextarea1">
                                <textarea title="Name" name="Name" id="Name" cols="100%" rows="5"></textarea>
                                <span class="textareaRequiredMsg">A value is required.</span></span>
                            </td>
                        </tr>
                        <tr>
                            <td>Шифр</td>
                            <td>
                                <textarea title="Shifr" name="Shifr" id="Shifr" cols="100%" rows="5"></textarea>
                            </td>
                        </tr>
                    </table>
                    <p>
                        <input type="submit" name="button" id="button" value="Submit"/>
                    </p>
                </form>
                <?php
                echo Func::ActButton("form_nomen.php", 'Перейти к списку')
                ?>
            </div>
        </div>
        <script type="text/javascript">
            <!--
            var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
            var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
            //-->
        </script>
    </body>
</html>
