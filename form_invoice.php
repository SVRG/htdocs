<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 08/09/17
 * Time: 16:18
 */
if (!isset($_SESSION)) {
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";
include_once "class_doc.php";
include_once "class_org.php";

if (!isset($_GET['kod_dogovora']))
    exit("Не выбран договор");

$D = new Doc();
$D->kod_dogovora = (int)$_GET['kod_dogovora'];
$D->getData();
$nomer = $D->Data['nomer'];
$data_sost = func::Date_from_MySQL($D->Data['data_sost']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8"/>
    <title><?php echo "Счет №$nomer от $data_sost"; ?></title>
    <style type="text/css">
        TABLE {
            border-collapse: collapse; /* Убираем двойные линии между ячейками */
            font-size: medium;
        }

        body {
            font-family: Arial, arial, sans-serif;
            font-size: medium;
            width: 790px;
        }

        @media print {
            body {
                font-family: Arial, arial, sans-serif;
                font-size: medium;
                alignment: center;
            }

            @page {
                size: A4;
                margin: 0 5mm 0 15mm; <!-- Отступы при печати top right bottom left -->
            }

            table {
                border-collapse: collapse;
            <!-- Убираем двойные линии между ячейками --> font-size: medium;
                width: 100%;
            }

            header {

            }

            th {
                font-style: normal;
            }
        }
    </style>
</head>
<body>
<br>
<?php
if ($D->Data['kod_ispolnit'] == config::$kod_org_main)
    echo "<img src='" . config::$logo_img . "' alt='" . config::$from_name . "'><br>";

$Isp = new Org();
$Isp->kod_org = $D->Data['kod_ispolnit'];
echo "<b>Поставщик: " . $D->Data['ispolnit_nazv_krat'] . "</b>";
$Isp->getData();

$dogovor_nomer = "";
$schet_data = array();
if (isset($_GET['kod_scheta'])) {
    $db = new Db();
    $kod_sceta = (int)$_GET['kod_scheta'];
    $rows = $db->rows(/** @lang MySQL */
        "SELECT * FROM scheta WHERE kod_scheta=$kod_sceta");

    if ($db->cnt > 0) {
        $schet_data = $rows[0];
        $nomer = $schet_data['nomer'];
        $data_sost = func::Date_from_MySQL($schet_data['data']);
        $dogovor_nomer = '<br>Договор: №' . $D->Data['nomer'] . ' от ' . func::Date_from_MySQL($D->Data['data_sost']);
    }
}

echo "<br><b>ИНН" . $Isp->Data['inn'] . " КПП" . $Isp->Data['kpp'] . "</b><br>";

$db = new Db();
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
echo "<br>р/с: " . $Isp->Data['r_sch'] . " в " . $Isp->Data['bank_rs'];
echo "<br>к/с: " . $Isp->Data['k_sch'];
echo "<br>БИК " . $Isp->Data['bik'] . "<br>";

// Адрес Заказчика
$kod_org = $D->Data['kod_org'];
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

echo "<p><h3>Счет №$nomer от $data_sost</h3></p>";
echo "Заказчик: " . $D->Data['nazv_krat'];
echo "<br>Юридический адрес: " . $adres;
echo $dogovor_nomer;
echo "<table border='1' cellspacing='0' cellpadding='3'>";
echo "<tr>
            <td>№</td>
            <td>Наименование</td>
            <td>Ед. изм.</td>
            <td>Кол-во</td>
            <td>Цена без НДС</td>
            <td>Сумма</td>  
            <td>НДС</td>     
            <td>Сумма с НДС</td>
          </tr>";
$total_nds = 0;
$total_summ = 0;
$total_summ_with_nds = 0;

if (count($schet_data) == 0 or isset($_GET['d'])) { // Счет выставлен по договору

    $rows = $db->rows("SELECT * FROM view_rplan WHERE kod_dogovora=$D->kod_dogovora");
    $cnt = $db->cnt;

    if ($cnt == 0)
        return "Нет партий";
    for ($i = 0; $i < $cnt; $i++) {
        $row = $rows[$i];
        $name = $row['name'];
        $modif = $row['modif'];
        if ($modif !== "")
            $modif = "($modif)";
        else
            $modif = "";

        $numb = func::rnd($row['numb']);                            // Количество
        $summ = func::rnd(func::rnd($row['price']) * $numb); // Сумма без НДС
        $summ_with_nds = Part::getPartSumma($row);                  // Сумма партии с НДС
        $nds = func::rnd($row['nds']) * 100;                          // Ставка НДС
        $summ_nds = func::rnd($summ * $nds / 100);               // Сумма НДС

        $summ_str = func::Rub($summ);
        $price_str = func::Rub($row['price']);
        $nds_str = ($row['nds'] * 100) . '%';
        $summ_with_nds_str = func::Rub($summ_with_nds);

        $total_nds += $summ_nds;
        $total_summ += $summ;
        $total_summ_with_nds += $summ_with_nds;

        $n = $i + 1;
        echo "<tr>
            <td align='center'>$n</td>
            <td align='left'>$name $modif</td>
            <td>шт.</td>
            <td align='center'>$numb</td>
            <td align='right' nowrap>$price_str</td>
            <td align='right' nowrap>$summ_str</td>  
            <td align='center'>$nds_str</td>     
            <td align='right' nowrap>$summ_with_nds_str</td>
          </tr>";
    }

    $total_summ_with_nds_text = func::num2str($total_summ_with_nds);
    $total_summ_with_nds = func::Rub($total_summ_with_nds);
    $total_nds_text = func::num2str($total_nds);
    $total_nds = func::Rub($total_nds);
} else {
    $name = $schet_data['prim'];
    $numb = 1;
    $summ_with_nds = $schet_data['summa']; // Сумма с НДС
    $nds = $summ_with_nds - func::rnd($schet_data['summa'] * 100 / 118); // Сумма НДС

    $summ_with_nds_str = func::Rub($schet_data['summa']); // Строка
    $price_str = func::Rub(func::rnd($schet_data['summa']) - $nds);
    $summ_str = $price_str;
    $nds_str = func::Rub($nds);
    echo "<tr>
            <td align='center'>1</td>
            <td align='left'>$name</td>
            <td>шт.</td>
            <td align='center'>$numb</td>
            <td align='right' nowrap>$price_str</td>
            <td align='right' nowrap>$summ_str</td>  
            <td align='center'>$nds_str</td>     
            <td align='right' nowrap>$summ_with_nds_str</td>
          </tr>";

    $total_summ_with_nds_text = func::num2str($schet_data['summa']);
    $total_summ_with_nds = func::Rub($schet_data['summa']);
    $total_nds_text = func::num2str($nds);
    $total_nds = func::Rub($nds);
}


echo "<tr><th colspan='7' align='right'>Итого с учетом НДС</th><td align='right' nowrap><b>$total_summ_with_nds</b></td></tr>";
echo "<tr><th colspan='8' align='right'>$total_summ_with_nds_text</th></tr>";
echo "<tr><th colspan='7' align='right'>В том числе НДС</th><td align='right' nowrap>$total_nds</td></tr>";
echo "<tr><th colspan='8' align='right'>$total_nds_text</th></tr>";
if ($D->Data['kod_ispolnit'] == config::$kod_org_main)
    echo "<tr><th colspan='8' align='left'>В случае увеличения курса ЦБ РФ Евро или Доллара к рублю на момент поступления денег на расчетный счет Поставщика более чем на 3% по сравнению с курсом валют, установленным ЦБ РФ на дату выставления счета, Поставщик оставляет за собой право пересчитать цены.</th></tr>";
echo "</table>";
echo "<br>";

if (isset($_GET['p']) and $D->Data['kod_ispolnit'] == config::$kod_org_main) {

    $invoice_sign_gd = config::$invoice_sign_gd;
    $invoice_sign_gb = config::$invoice_sign_gb;

    echo "<br><br>";
    echo "<table border='0' cellpadding='10'>
            <tr>
                <td>Генеральный директор</td>
                <td> _______________ </td>
                <td>$invoice_sign_gd</td>
            </tr>
            <tr>
                <td>МП</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Главный бухгалтер</td>
                <td>_______________ </td>
                <td>$invoice_sign_gb</td>
            </tr>";
} elseif ($D->Data['kod_ispolnit'] == config::$kod_org_main) {
    echo /** @lang HTML */
    "<img src='img/sign.png' width='776'></image>";
}
?>
</body>
</html>
