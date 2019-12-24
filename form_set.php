<?php
include_once "security.php";
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 2018-12-25
 * Time: 16:14
 */
include "class_part.php";

if (!isset($_GET['kod_part'])) {
    echo Part::formSetList();
    exit("</body></html>");
}
if ((int)$_GET['kod_part'] <= 0)
    exit("Не выбрана партия");

$set_id = 0;
if(isset($_GET['set_id']))
    if((int)$_GET['set_id'] > 0)
        $set_id = (int)$_GET['set_id'];

$kod_part = (int)$_GET['kod_part'];
$part = new Part();
$part->kod_part = $kod_part;
$part_data = $part::getData($kod_part);

if (isset($_POST['Flag'])) {
    // Если подтверждение добавления
    if ($_POST['Flag'] == "addItemToSet") {
        $part->addItemToSet((int)$_POST['kod_item'], 1);
    } // Если подтверждение удаления
    // Если подтверждение добавления
    if ($_POST['Flag'] == "addItemToSetAll") {
        $part->addItemToSet((int)$_POST['kod_item'], 0);
    } elseif ($_POST['Flag'] == "delItemFromSet") {
        $part->deleteItemFromSet((int)$_POST['kod_item']);
    } // Если подтверждение изменения количества
    elseif ($_POST['Flag'] == "editNumb") {
        $part->setItemNumb((int)$_POST['kod_item'], (double)$_POST['numb']);
    }// Если подтверждение удаления всей комплектации
    elseif ($_POST['Flag'] == "delSet") {
        if (func::user_group() == "admin")
            $part->deleteSet();
    }
}

if (func::user_group() == "admin") {
    if (isset($_GET['copyFrom'])) {
        $kod_part_source = (int)$_GET['copyFrom'];
        $part->copyItemsToPart($kod_part_source);
    }
}

$elem = new Elem();
$elem->kod_elem = $part_data['kod_elem'];

$d = new Doc();
$d->kod_dogovora = $part_data['kod_dogovora'];
$d->getData();

$type = "Счет";
if (strpos($part_data['nomer'], "НВС") !== false)
    $type = "Договор";

$doc_nomer = "$type №" . $part_data['nomer'] . " от " . func::Date_from_MySQL($d->Data['data_sost']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="menu/print.css">
    <title>Комплектация <?php echo $doc_nomer; ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="widgets/selectize/dist/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="widgets/selectize/dist/css/selectize.default.css">
    <script type="text/javascript" src="js/index.js"></script>
</head>
<body>
<?php
$part_numb_max = $part_data['numb'];
$sum_part = $part_data['sum_part'];
if(isset($_GET['numb']))
    if($part_numb_max > (int)$_GET['numb'])
        {
            $part_numb_max = (int)$_GET['numb'];
            $sum_part = $part_numb_max*$part_data['price_it'];
        }

echo "<h3>$doc_nomer " . $part_data['nazv_krat'] . "</h3>";
echo "<h3>" . $elem::getNameForInvoice($part_data) . " - " . $part_numb_max . " шт. (Сумма: " . func::Rub($sum_part) . ")</h3>";

$db = new Db();

// Комплектация
$rows = $db->rows(/** @lang MySQL */
    "SELECT * FROM part_set WHERE kod_part=$kod_part AND del=0 AND set_id=$set_id;");
if ($db->cnt == 0)
    echo "Нет данных";
elseif (func::user_group() == "admin" and isset($_GET['del']))
    echo func::ActButton2("", "Удалить", "delSet", "kod_part", $kod_part);

$price_row = ""; // Цена
if (isset($_GET['price']))
    $price_row = "<td width='100'>Цена</td>";

$line = "<table width='100%' border='1'>
        <tr>
        <td>№</td>
        <td>Наименование</td>
        <td>Код</td>
        <td width='50'>Кол-во</td>
        $price_row
</tr>";
echo $line;

// Формируем строку для сохранения в файл
$res = /** @lang HTML */
    "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
        \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
        <html lang=\"ru\">
        <head>
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
            <link rel=\"stylesheet\" type=\"text/css\" href=\"menu/print.css\">
            <title>Комплектация $doc_nomer</title>
        </head>
        <body>" . $line;

$sum = 0;
for ($i = 0; $i < $db->cnt; $i++) {
    $row = $rows[$i];
    $kod_item = $row['kod_item'];
    $name = $row['name'];
    $kod_1c = $row['kod_1c'];
    $numb = $row['numb'];
    $sum += (double)$row['sum'];
    $n = $i + 1;

    $btn = "";
    if (isset($_GET['del']))
        $btn = func::ActButton2("", "Удалить", "delItemFromSet", "kod_item", $kod_item);

    $btn_edit = "";
    if (isset($_GET['edit']))
        $btn_edit = func::ActButton2("", "Изменить", "editItemNumb", "kod_item", $kod_item);

    // Форма редактирования количества
    if (isset($_POST['Flag']))
        if ($_POST['Flag'] == "editItemNumb")
            if ((int)$_POST['kod_item'] == $kod_item) {
                $btn_edit = "<form method='post'>
                        <input type='number' name='numb' value='$numb'>
                        <input type='submit' value='Подтвердить'>
                        <input type='hidden' name='kod_item' value='$kod_item'>
                        <input type='hidden' name='Flag' value='editNumb'>
                     </form>";
                $btn_edit .= func::Cansel();
            }

    $price_row = ""; // Цена
    if (isset($_GET['price'])) {
        $price = func::rnd($row['price'] * (100 + config::$nds_main) / 100); // Цена позиции
        $price_row = "<td align='right'>" . func::Rub($price) . "</td>"; //
    }

    $line = /** @lang HTML */
        "<tr>
                <td>$n $btn</td>
                <td>$name</td>
                <td>$kod_1c</td>
                <td align='right'>$numb $btn_edit</td>
                $price_row
          </tr>";
    echo $line;
    $res .= $line . "\n";
}

$line = "</table>";
echo $line;

$res .= $line . "\n";

// Сумма комплектации
//echo func::Rub($sum); // Без НДС
$sum_it = func::rnd($sum * (100 + config::$nds_main) / 100); // Сумма комплектации с НДС
$ostat = $sum_part - $sum_it;
if ($sum_part == 0)
    $sum_part = 1;
$prc = func::rnd((100 * $ostat) / $sum_part);
$ostat_str = func::Rub($ostat); // Преобразуем в строку
echo "Сумма комплектации с НДС: " . func::Rub($sum_it) . " (Прибыль: $ostat_str / $prc%)"; // Сумма с НДС
echo "<br>";


// Добавление элементов
$btn = "";
if (isset($_GET['add'])) {
    echo "<h3>Список выбора</h3>";

    $search = "";
    if (isset($_POST['search']))
        $search = func::clearString($_POST['search']);
    elseif (isset($_SESSION['set_search']))
        $search = func::clearString($_SESSION['set_search']);

    $where = "";
    if ($search != "") {
        $where = "AND name LIKE '%" . $db->real_escape_string($search) . "%'";

        if (isset($_GET['all']))
            $where = "WHERE name LIKE '%" . $db->real_escape_string($search) . "%'";
        $_SESSION['set_search'] = $search;
    } else
        unset($_SESSION['set_search']);

    // Список выбора - только те записи, которых нет в комплектации
    if (!isset($_GET['all']))
        $sql = /** @lang MySQL */
            "SELECT * 
                FROM sklad_1c 
                WHERE numb>=$part_numb_max $where
                AND kod_1c NOT IN (SELECT part_set.kod_1c FROM part_set WHERE part_set.kod_part=$kod_part AND del=0 AND set_id=$set_id)
        ORDER BY name;";
    else {
        if (isset($_GET['n']))
            if ($where == "")
                $where .= " WHERE numb=$part_numb_max ";
            else
                $where .= " AND numb=$part_numb_max ";

        if (isset($_GET['w'])) // удаляем условия отбора
            $where = "";

        $sql = /** @lang MySQL */
            "SELECT * 
                FROM sklad_1c $where
              ORDER BY name;";
    }
    $rows = $db->rows($sql);

    echo /** @lang HTML */
    "<form method='post'>
        <table border='0'>
        <tr>
        <td><label>Фильтр </label><input type='text' name='search' value='$search'></td>
        <td><input type='submit' value='Применить'></td>
        </tr>
        </table>
     </form>";

    // Автокоплит
    $res = /** @lang HTML */
        "<form method='post'>
         <select id='kod_item' name='kod_item' placeholder=\"Выбрать наименование...\">";

    for ($i = 0; $i < $db->cnt; $i++) {
        $row = $rows[$i];
        $kod_item_str = $row['kod_item'];
        $kod_1c_str = $row['kod_1c'];
        $name = $row['name'];
        $numb = $row['numb'];

        if($numb < config::$min_price)
            continue;

        $res .= /** @lang HTML */
            "<option value='$kod_item_str'>$name $numb $kod_1c_str</option>\r\n";
    }
    $res .= /** @lang HTML */
        "<input type='hidden' name='Flag' value='addItemToSet'>
        <input type='submit' value='Добавить'>
        </form>";

    $res .= /** @lang HTML */
        '</select>
                    <script type="text/javascript">
                                    let kod_item, $kod_item_str;
                
                                    $kod_item_str = $("#kod_item").selectize({
                                        onChange: function(value) {
                        if (!value.length) return "";
                    }
                                    });
                        kod_item = $kod_item_str[0].selectize;
                </script>';
    echo $res;
    // Автокомплит

    if ($db->cnt == 0)
        exit("Нет данных");

    $price_row = ""; // Цена
    if (isset($_GET['price']))
        $price_row = "<td width='100'>Цена</td>";

    echo "<table width='100%' border='1'>
        <tr>
        <td>№</td>
        <td></td>
        <td>Наименование</td>
        <td>Код</td>
        <td width='50'>Кол-во</td>
        <td width='50'>%</td>
        $price_row
        </tr>";

    for ($i = 0; $i < $db->cnt; $i++) {
        $row = $rows[$i];
        $kod_item = $row['kod_item'];
        $name = $row['name'];
        $kod_1c = $row['kod_1c'];
        $numb = $row['numb'];
        $price = func::rnd($row['price'] * (100 + config::$nds_main) / 100); // Цена позиции

        $price_row = ""; // Цена
        if (isset($_GET['price']))
            $price_row = "<td align='right'>" . func::Rub($price) . "</td>"; //

        if ($numb >= $part_numb_max)
            $sum_item = func::rnd($price * $part_numb_max); // Сумма позиции, которая может быть добавлена к комплектации
        else
            $sum_item = func::rnd($price * $numb);

        $ostat_p = $sum_part - ($sum_it + $sum_item); // Остаток
        if ($ostat_p < 0)
            continue;

        if ($sum_part > 0)
            $prc_p = (int)(($ostat_p * 100) / $sum_part); // Если добавить данную позицию то получим такой процент прибыли
        else
            $prc_p = 0;

        $n = $i + 1;

        $btn = func::ActButton2("", "Добавить", "addItemToSet", "kod_item", $kod_item);

        $btn_all = func::ActButton2("", "Добавить", "addItemToSetAll", "kod_item", $kod_item);

        echo /** @lang HTML */
        "<tr>
                <td>$n</td>
                <td>$btn</td>
                <td>$name</td>
                <td>$kod_1c</td>
                <td align='right'>$numb $btn_all</td>
                <td align='right'>$prc_p</td>   
                $price_row             
          </tr>";
    }
    echo "</table>";
}


// Ставим 0 в выбранных позициях
//$db->query(/** @lang MySQL */
//    "UPDATE sklad_1c SET numb=0 WHERE $where;");

if (isset($_GET['fileName'])) {
    $file = "set_$kod_part.html";
// Открываем файл для получения существующего содержимого
//    $current = file_get_contents($file);
// Формируем запись
    $res = mb_convert_encoding($res, 'UTF-8', 'auto');
// Пишем содержимое в файл
    file_put_contents($file, $res);
}
?>
</body>
</html>