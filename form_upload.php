<?php
include_once "security.php";

if (!isset($_GET['Desc']))
    exit("Error: Не задан объект");

include_once('class_docum.php');
include_once('class_doc.php');

$Text = '';
// Договор
if ($_GET['Desc'] == 'IncludeToDoc' and isset($_GET['kod_dogovora'])) {
    $d = new Doc();
    $d->kod_dogovora = (int)$_GET['kod_dogovora'];
    $Text = $d->getFormLink();
} // Элемент
elseif ($_GET['Desc'] == 'IncludeToElem' and isset($_GET['kod_elem'])) {
    $E = new Elem();
    $E->kod_elem = (int)$_GET['kod_elem'];
    $Text = $E->getFormLink();
} // Организация
elseif ($_GET['Desc'] == 'IncludeToOrg' and isset($_GET['kod_org'])) {
    $Org = new Org();
    $Org->kod_org = (int)$_GET['kod_org'];
    $Text = $Org->getFormLink();
} else {
    exit("Error: Не задан объект");
}

$btn = ''; // Кнопка
$CopyOK = false;

if (isset($_FILES["filename"]))
    if ($_FILES["filename"]["tmp_name"] !== "") {
        $dest = $_FILES["filename"]["tmp_name"];

        $info = new SplFileInfo($_FILES["filename"]["name"]);
        $ext = $info->getExtension();

        $fname = Func::rus2lat2(Func::_strip($_FILES["filename"]["name"]));

        $date = Func::NowDoc();
        $rnd = rand();

        $new_file_name = $date . '-' . $rnd . '-' . $fname . '.' . $ext;

        $path = realpath($_SERVER["DOCUMENT_ROOT"]);
        $path .= '/docs';

        if (copy($dest, $path . '/' . $new_file_name)) {

            if (file_exists($path . '/' . $new_file_name))
                $CopyOK = true;

            $path = 'docs/' . $new_file_name;
            $name = $_POST['Type'];

            if ($name == '')
                $name = '*empty';

            $docum = new Docum();

// ------------------------------------------------------------------------------
            if ($_GET['Desc'] == 'IncludeToDoc' and isset($_GET['kod_dogovora'])) {
                $docum->Add($name, $path, $_GET['kod_dogovora'], 'Doc');
                header('Location: http://' . $_SERVER['HTTP_HOST'] . '/form_dogovor.php?kod_dogovora=' . $_GET['kod_dogovora']);
            } // ------------------------------------------------------------------------------
            elseif ($_GET['Desc'] == 'IncludeToElem' and isset($_GET['kod_elem'])) {
                $docum->Add($name, $path, $_GET['kod_elem'], 'Elem');
                header('Location: http://' . $_SERVER['HTTP_HOST'] . '/form_elem.php?kod_elem=' . $_GET['kod_elem']);
            } // ------------------------------------------------------------------------------
            elseif ($_GET['Desc'] == 'IncludeToOrg' and isset($_GET['kod_org'])) {
                $docum->Add($name, $path, $_GET['kod_org'], 'Org');
                header('Location: http://' . $_SERVER['HTTP_HOST'] . '/form_org.php?kod_org=' . $_GET['kod_org']);
            }
        }
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Загрузка Файла</title>
</head>
<body>
<?php include("header.php");
?>

<!-- end masthead -->
<div class="style1" id="pagecell1">
    <h3>Загрузить файл</h3>
    <?php
    if (isset($Text))
        echo '<h1>' . $Text . '</h1>';

    ?>
    <form action="<?php $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data">
        <br>
        <table width="200" border="0">
            <tr>
                <td>Примечание</td>
                <td><label>
                        <input title="prim" name="Type" value="<?php if (isset($Type)) echo (string)$Type; ?>"/>
                    </label>
                </td>
            </tr>
            <tr>
                <td>Файл</td>
                <td><input type="file" name="filename"/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" value="Загрузить"/></td>
            </tr>
        </table>
        <input type="hidden" name="FileAploadConfirm"/>
    </form>
    <?php
    if (isset($_FILES["filename"], $_POST['FileAploadConfirm']) and $CopyOK) {
        echo $btn;
        echo("Файл успешно загружен <br>");
        echo("Характеристики файла: <br>");
        echo("Имя файла: ");
        echo($_FILES["filename"]["name"]);
        echo("<br>Размер файла: ");
        echo($_FILES["filename"]["size"]);
    } elseif (isset($_POST['FileAploadConfirm']))
        echo("Ошибка загрузки файла");
    else {
        // ------------------------------------------------------------------------------
        if ($_GET['Desc'] == 'IncludeToDoc' and isset($_GET['kod_dogovora'])) {
            $btn = Func::ActButton('form_dogovor.php?kod_dogovora=' . $_GET['kod_dogovora'], 'Отмена');
        } // ------------------------------------------------------------------------------
        elseif ($_GET['Desc'] == 'IncludeToElem' and isset($_GET['kod_elem'])) {
            $btn = Func::ActButton('form_elem.php?kod_elem=' . $_GET['kod_elem'], 'Отмена');
        } // ------------------------------------------------------------------------------
        elseif ($_GET['Desc'] == 'IncludeToOrg' and isset($_GET['kod_org'])) {
            $btn = Func::ActButton('form_org.php?kod_org=' . $_GET['kod_org'], 'Отмена');
        }

        echo $btn;
    }
    ?>
</div>
</body>
</html>
