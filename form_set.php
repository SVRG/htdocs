<?php
include_once "security.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="menu/print.css">
    <title>Комплектация</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="widgets/selectize/dist/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="widgets/selectize/dist/css/selectize.default.css">
    <script type="text/javascript" src="js/index.js"></script>
</head>
<body>
<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 2018-12-25
 * Time: 16:14
 */
include "class_part.php";

if (!isset($_GET['kod_part']))
    exit("Не выбрана партия");
if((int)$_GET['kod_part'] <= 0)
    exit("Не выбрана партия");

$kod_part = (int)$_GET['kod_part'];
$part = new Part();
$part->kod_part = $kod_part;
$part_data = $part::getData($kod_part);

if (isset($_POST['Flag'])) {
    // Если подтверждение добавления
    if ($_POST['Flag'] == "addItemToSet") {
        $part->addItemToSet((int)$_POST['kod_item'], 1);
    } // Если подтверждение удаления
    elseif ($_POST['Flag'] == "delItemFromSet") {
        $part->deleteItemFromSet((int)$_POST['kod_item']);
    } // Если подтверждение изменения количества
    elseif ($_POST['Flag'] == "editNumb") {
        $part->setItemNumb((int)$_POST['kod_item'], (double)$_POST['numb']);
    }
}

$elem = new Elem();
$elem->kod_elem = $part_data['kod_elem'];

$d = new Doc();
$d->kod_dogovora = $part_data['kod_dogovora'];
$d->getData();

$type = "Счет";
if(strpos($part_data['nomer'],"НВС") !== false)
    $type = "Договор";

echo "<h3>$type №" . $part_data['nomer'] . " от " . func::Date_from_MySQL($d->Data['data_sost']) . " " . $part_data['nazv_krat'] . "</h3>";
echo "<h3>" . $elem::getNameForInvoice($part_data) . " - " . $part_data['numb'] . " шт. (Сумма: " . func::Rub($part_data['sum_part']) . ")</h3>";

$db = new Db();
$where = "";

// Комплектация
$rows = $db->rows(/** @lang MySQL */
    "SELECT * FROM part_set WHERE kod_part=$kod_part AND del=0;");
if ($db->cnt == 0)
    echo "Нет данных";

$line = "<table width='100%' border='1'>
        <tr>
        <td>№</td>
        <td>Наименование</td>
        <td>Код</td>
        <td width='50'>Кол-во</td>
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
            <title>Комплектация</title>
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

    $line = /** @lang HTML */
        "<tr>
                <td>$n $btn</td>
                <td>$name</td>
                <td>$kod_1c</td>
                <td align='right'>$numb $btn_edit</td>
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
$ostat = $part_data['sum_part'] - $sum_it;
$prc = func::rnd((100 * $ostat) / $part_data['sum_part']);
$ostat_str = func::Rub($ostat); // Преобразуем в строку
echo "Сумма комплектации с НДС: " . func::Rub($sum_it) . " (Прибыль: $ostat_str / $prc%)"; // Сумма с НДС
echo "<br>";


// Добавление элементов
$btn = "";
if (isset($_GET['add'])) {
    echo "<h3>Список выбора</h3>";
    $numb_min = $part_data['numb'];

    // Список выбора - только те записи, которых нет в комплектации
    if (!isset($_GET['all']))
        $sql = /** @lang MySQL */
            "SELECT * 
                FROM sklad_1c 
                WHERE numb>=$numb_min
                AND kod_1c NOT IN (SELECT part_set.kod_1c FROM part_set WHERE part_set.kod_part=$kod_part AND del=0)
        ORDER BY name ASC;";
    else
        $sql = /** @lang MySQL */
            "SELECT * 
                FROM sklad_1c
              ORDER BY name ASC;";
    $rows = $db->rows($sql);

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
                                    var kod_item, $kod_item_str;
                
                                    $kod_dogovora = $("#kod_item").selectize({
                                        onChange: function(value) {
                        if (!value.length) return "";
                    }
                                    });
                        kod_item = $kod_item_str[0].selectize;
                </script>';
    echo $res;
    // Атокомплит

    if ($db->cnt == 0)
        exit("Нет данных");

    echo "<table width='100%' border='1'>
        <tr>
        <td>№</td>
        <td></td>
        <td>Наименование</td>
        <td>Код</td>
        <td width='50'>Кол-во</td>
        <td width='50'>%</td>
        </tr>";

    for ($i = 0; $i < $db->cnt; $i++) {
        $row = $rows[$i];
        $kod_item = $row['kod_item'];
        $name = $row['name'];
        $kod_1c = $row['kod_1c'];
        $numb = $row['numb'];
        $price = func::rnd($row['price'] * (100 + config::$nds_main) / 100); // Цена позиции

        if ($numb >= $part_data['numb'])
            $sum_item = func::rnd($price * $part_data['numb']); // Сумма позиции, которая может быть добавлена к комплектации
        else
            $sum_item = func::rnd($price * $numb);

        $ostat_p = $part_data['sum_part'] - ($sum_it + $sum_item); // Остаток
        $prc_p = func::rnd(($ostat_p * 100) / $part_data['sum_part']); // Если добавить данную позицию то получим такой процент прибыли

        if ($ostat_p < 0)
            continue;

        $n = $i + 1;

        $btn = func::ActButton2("", "Добавить", "addItemToSet", "kod_item", $kod_item);

        echo /** @lang HTML */
        "<tr>
                <td>$n</td>
                <td>$btn</td>
                <td>$name</td>
                <td>$kod_1c</td>
                <td align='right'>$numb</td>
                <td align='right'>$prc_p</td>                
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