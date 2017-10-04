<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 08/09/17
 * Time: 16:18
 */
include_once "class_doc.php";
include_once "class_org.php";

if (!isset($_GET['kod_dogovora']))
    exit("Не выбран договор");

$D = new Doc();
$D->kod_dogovora = $_GET['kod_dogovora'];
$D->getData();

$Isp = new Org();
$Isp->kod_org = $D->Data['kod_ispolnit'];
echo "Поставщик: " . $D->Data['ispolnit_nazv_krat'];
$Isp->formRecv();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo "Счет №" . $D->Data['nomer'] . ' от ' . func::Date_from_MySQL($D->Data['data_sost']); ?></title>
</head>
<body>
<?php
$Org = new Org();
$Org->kod_org = $D->Data['kod_org'];

echo "<br>Счет №" . $D->Data['nomer'] . ' от ' . func::Date_from_MySQL($D->Data['data_sost']);
echo "<br>Заказчик: " . $D->Data['nazv_krat'];
//echo $D->formParts('');
$db = new Db();
$rows = $db->rows("SELECT * FROM view_rplan WHERE kod_dogovora=$D->kod_dogovora");
$cnt = $db->cnt;

echo "<table border='1' cellspacing='0'>";
echo "<tr>
            <td>№</td>
            <td>Наименование</td>
            <td>Кол-во</td>
            <td>Цена без НДС</td>
            <td>Сумма</td>  
            <td>НДС</td>     
            <td>Сумма с НДС</td>
          </tr>";
$total_nds = 0;
$total_summ = 0;
$total_summ_with_nds = 0;


for ($i = 0; $i < $cnt; $i++) {
    $row = $rows[$i];
    $name = $row['name'];
    $modif = $row['modif'];
    if ($modif != '')
        $modif = "($modif)";
    else
        $modif = "";
    $numb = $row['numb']; // Общее количество / осталось отгрузить / отгружено
    $price_str = func::Rub($row['price']);
    $summ = $row['price'] * $row['numb'];
    $summ_str = func::Rub($row['price'] * $row['numb']);

    $nds_str = ($row['nds'] * 100) . '%';
    $summ_nds = $summ * ($row['nds']);
    $summ_with_nds = $summ + $summ_nds;
    $summ_with_nds_str = func::Rub($summ_with_nds);

    $total_nds += $summ_nds;
    $total_summ += $summ;
    $total_summ_with_nds += $summ_with_nds;

    $n = $i + 1;
    echo "<tr>
            <td align='center'>$n</td>
            <td align='left'>$name</td>
            <td align='center'>$numb</td>
            <td align='right'>$price_str</td>
            <td align='right'>$summ_str</td>  
            <td align='center'>$nds_str</td>     
            <td align='right'>$summ_with_nds_str</td>
          </tr>";
}
$total_summ_with_nds_text = func::num2str($total_summ_with_nds);
$total_summ_with_nds = func::Rub($total_summ_with_nds);
$total_nds_text = func::num2str($total_nds);
$total_nds = func::Rub($total_nds);

echo "<tr><th colspan='6' align='right'>Итого</th><td align='right'>$total_summ_with_nds</td></tr>";
echo "<tr><th colspan='7' align='right'>$total_summ_with_nds_text</th></tr>";
echo "<tr><th colspan='6' align='right'>В том числе НДС</th><td align='right'>$total_nds</td></tr>";
echo "<tr><th colspan='7' align='right'>$total_nds_text</th></tr>";

echo "</table>";