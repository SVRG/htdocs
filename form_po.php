<?php
/**
 * Purchase Order (PO) - Форма заказа
 * Request For Quotation (RFQ) - Запрос стоимости
 * Created by PhpStorm.
 * User: svrg
 * Date: 04/04/18
 * Time: 18:30
 */
include_once "class_doc.php";
include_once "class_org.php";

if (!isset($_GET['kod_dogovora']) and !isset($_GET['kod_part']))
    exit("Не выбран договор/партия");

$D = new Doc();
$prefix = "NVS-PO-";
$type = "Заказ";

if(isset($_GET['rfq']))
{
    $prefix = "NVS-RFQ-";
    $type = "Запрос";
}

$kod_part = 0;
if (isset($_GET['kod_part'])) {
    $PData = Part::getData($_GET['kod_part']);
    $kod_part = $PData['kod_part'];
    if ($PData !== false) {
        $D->kod_dogovora = $PData['kod_dogovora'];
        $prefix .= "P" . (int)$_GET['kod_part']."-";
    }
} else
    $D->kod_dogovora = $_GET['kod_dogovora'];

$D->getData();
$data_sost = func::Date_from_MySQL($D->Data['data_sost']);
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html lang="ru">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link rel="stylesheet" type="text/css" href="menu/print.css">
        <title><?php echo $prefix . $D->Data['kod_dogovora'] . ' от ' . $data_sost; ?></title>
    </head>
    <body>
<?php
if ($D->Data['kod_ispolnit'] != config::$kod_org_main)
    echo "<img src='" . config::$logo_img . "' alt='" . config::$from_name . "'><br>";

// Адрес Заказчика
$kod_org = $D->Data['kod_org'];
$db = new Db();

$rows = $db->rows(/** @lang MySQL */
    "SELECT * FROM adresa WHERE kod_org=$kod_org AND del=0 AND type=2");
$adres = "";
if ($db->cnt > 0)
    $adres = $rows[0]['adres'];
else {
    $rows = $db->rows(/** @lang MySQL */
        "SELECT * FROM adresa WHERE kod_org=$kod_org AND del=0 AND type=1");
    if ($db->cnt > 0)
        $adres = $rows[0]['adres'];
}
$Org = new Org();
$Org->kod_org = $D->Data['kod_org'];
$Org->getData();

echo "<br>Заказчик: " . $D->Data['nazv_krat'];
echo "<br>Юридический адрес: " . $adres;
echo "<br><b>ИНН " . $Org->Data['inn'] . " КПП " . $Org->Data['kpp'] . "</b><br>";

$Isp = new Org();
$Isp->kod_org = $D->Data['kod_ispolnit'];
$Isp->getData();
echo "<br>Поставщик: " . $D->Data['ispolnit_nazv_krat'];
echo "<br><b>ИНН " . $Isp->Data['inn'] . " КПП " . $Isp->Data['kpp'] . "</b>";

// Адрес Поставщика
$rows = $db->rows(/** @lang MySQL */
    "SELECT * FROM adresa WHERE kod_org=$Isp->kod_org AND del=0 AND type=2");
$adres_u = ""; // Юридический адрес
if ($db->cnt > 0)
    $adres_u = $rows[0]['adres'];

echo "<br>Юридический адрес: $adres_u";
$rows = $db->rows(/** @lang MySQL */
    "SELECT * FROM adresa WHERE kod_org=$Isp->kod_org AND del=0 AND type=1");
$adres_p = ""; // Почтовый адрес
if ($db->cnt > 0)
    $adres_p = $rows[0]['adres'];
echo "<br>Почтовый адрес: $adres_p";

$db = new Db();
$part_select = "";
if($kod_part>0)
    $part_select = " AND kod_part=$kod_part";
$rows = $db->rows("SELECT * FROM view_rplan WHERE kod_dogovora=$D->kod_dogovora $part_select");
$cnt = $db->cnt;

echo "<h3>$type $prefix" . $D->Data['kod_dogovora'] . ' от ' . $data_sost . "</h3>";

echo "<table border='1' cellspacing='0'>";
echo "<tr>
            <td>№</td>
            <td>Наименование</td>
            <td>Кол-во</td>
            <td width='150'>Цена c НДС</td>
            <td width='150'>Сумма с НДС</td>
            <td width='150'>Дата поставки</td>
          </tr>";

$total_summ = 0;

for ($i = 0; $i < $cnt; $i++) {
    $row = $rows[$i];
    $name = $row['name'];
    $modif = $row['modif'];

    if ($modif !== "")
        $name = elem::getNameForInvoice($row);

    $numb = $row['numb']; // Общее количество

    $price = Part::getPriceWithNDS($row);
    $price_str = "";
    if($price > 0)
        $price_str = func::Rub($price);

    $sum_part = $row['sum_part'];
    $total_summ+=$sum_part;
    $summ_str = "";
    if($sum_part > 0)
        $summ_str = func::Rub($sum_part);

    $data_str = "";
    $data_postav = func::Date_from_MySQL($row['data_postav']);

    $n = $i + 1;
    echo "<tr>
            <td align='center'>$n</td>
            <td align='left'>$name $modif</td>
            <td align='center'>$numb</td>
            <td align='right'>$price_str</td>     
            <td align='right'>$summ_str</td>
            <td align='center'>$data_postav</td>
          </tr>";
}

if($total_summ>0)
    $total_summ = func::Rub($total_summ);
else
    $total_summ = "";

echo "<tr><th colspan='4' align='right'>Итого с учетом НДС</th><td align='right' nowrap><b>$total_summ</b></td></tr>";

echo "</table>";

echo "<br><br>Генеральный директор _______________" . config::$invoice_sign_gd;