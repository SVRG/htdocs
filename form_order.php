<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 08/09/17
 * Time: 16:18
 */
include_once "class_doc.php";
include_once "class_org.php";

if (!isset($_GET['kod_dogovora']) and !isset($_GET['kod_part']))
    exit("Не выбран договор/партия");

$D = new Doc();
$prefix = "D";
if(isset($_GET['kod_part']))
{
    $PData = Part::getData($_GET['kod_part']);
    if($PData!==false)
    {
        $D->kod_dogovora = $PData['kod_dogovora'];
        $prefix = "P".$_GET['kod_part']."-";
    }
}
else
    $D->kod_dogovora = $_GET['kod_dogovora'];

$D->getData();

$Isp = new Org();
$Isp->kod_org = $D->Data['kod_ispolnit'];
echo "Заказчик: " . $D->Data['ispolnit_nazv_krat'];
$Isp->formRecv();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo "Заказ №" . $D->Data['kod_dogovora'] . ' от ' . func::NowE(); ?></title>
</head>
<body>
<?php
$Org = new Org();
$Org->kod_org = $D->Data['kod_org'];

$db = new Db();
$rows = $db->rows("SELECT * FROM view_rplan WHERE kod_dogovora=$D->kod_dogovora");
$cnt = $db->cnt;

echo "<br>Заказ №$prefix" . $D->Data['kod_dogovora'] . ' от ' . func::NowE();

echo "<table border='1' cellspacing='0'>";
echo "<tr>
            <td>№</td>
            <td>Наименование</td>
            <td>Кол-во</td>
            <td width='150'>Цена без НДС</td>
            <td width='150'>Сумма</td>  
            <td width='10'>НДС</td>     
            <td width='150'>Сумма с НДС</td>
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

    $n = $i + 1;
    echo "<tr>
            <td align='center'>$n</td>
            <td align='left'>$name</td>
            <td align='center'>$numb</td>
            <td align='right'></td>
            <td align='right'></td>  
            <td align='center'></td>     
            <td align='right'></td>
          </tr>";
}

echo "</table>";

echo "<br><br>Генеральный директор _______________ С.А. Тихомиров";