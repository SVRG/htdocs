<?php
/**
 * Форма коммерческого предложения
 * Created by PhpStorm.
 * User: svrg
 * Date: 09/02/18
 * Time: 18:35
 */
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
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8"/>
    <title><?php echo "Коммерческое предложение N$nomer от $data_sost"; ?></title>
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
        p {
            text-align: justify; /* Выравнивание по ширине */
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
echo "<br>БИК " . $Isp->Data['bik'];
echo "<br>";

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

echo "<p><h3>Коммерческое предложение №$nomer от $data_sost</h3></p>";
echo "Заказчик: <b>" . $D->Data['nazv_krat']."</b><br>";
echo "Тут должен быть контакт";
echo /** @lang HTML */
"<p>Группа Компаний \"НАВИС Групп\" в лице компании \"НВС Навигационные Технологии\" благодарит Вас за интерес, проявленный к нашей продукции и предлагает рассмотреть предложение на поставку следующих позиций:</p>";
echo /** @lang HTML */
"<table border='1' cellspacing='0' cellpadding='3'>";
echo "<tr>
            <td>№</td>
            <td>Наименование</td>
            <td>Кол-во</td>
            <td>Срок поставки</td>
            <td>Цена с НДС</td>
            <td>Сумма с НДС</td>
          </tr>";
$total_nds = 0;
$total_summ = 0;
$total_summ_with_nds = 0;

$rows = $db->rows("SELECT * FROM view_rplan WHERE kod_dogovora=$D->kod_dogovora");
$cnt = $db->cnt;

if ($cnt == 0)
    return "Нет партий";
for ($i = 0; $i < $cnt; $i++) {
    $row = $rows[$i];
    $name = $row['name'];
    $modif = $row['modif'];

    if ($modif !== "")
        $name = elem::getNameForInvoice($row);

    $numb = func::rnd($row['numb']);                                // Количество
    $price = Part::getPriceWithNDS($row);
    $summ = $price * $numb;                                         // Сумма c НДС

    $price_str = func::Rub($price);
    $summ_with_nds_str = func::Rub($summ);

    $total_summ_with_nds += $summ;

    //$data_postav = func::Date_from_MySQL($row['data_postav']); // Тут нужно писать текст N-дней с момента оплаты
    $data_postav = "14 дней с момента оплаты";

    $n = $i + 1;
    echo "<tr>
            <td align='center'>$n</td>
            <td align='left'>$name $modif</td>
            <td align='center'>$numb</td>
            <td>$data_postav</td>
            <td align='right' nowrap>$price_str</td> 
            <td align='right' nowrap>$summ_with_nds_str</td>
          </tr>";
}

$total_summ_with_nds_text = func::num2str($total_summ_with_nds);
$total_summ_with_nds = func::Rub($total_summ_with_nds);
$total_nds = func::Rub($total_nds);


echo "<tr><th colspan='5' align='right'>Итого с учетом НДС</th><td align='right' nowrap><b>$total_summ_with_nds</b></td></tr>";
if ($D->Data['kod_ispolnit'] == config::$kod_org_main)
    echo "<tr><td colspan='6' align='left'><p>Предложение действительно в течение 14-ти дней. В случае увеличения курса ЦБ РФ Евро или Доллара к рублю на момент поступления денег на расчетный счет Поставщика более чем на 3% по сравнению с курсом валют, установленным ЦБ РФ на дату выставления счета, Поставщик оставляет за собой право пересчитать цены.</p></td></tr>";
echo "</table>";

if (isset($_GET['p']) and $D->Data['kod_ispolnit'] == config::$kod_org_main) {

    $invoice_sign_gd = config::$invoice_sign_gd;
    $invoice_sign_gb = config::$invoice_sign_gb;

    echo "<br>";
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
            </tr>";
} elseif ($D->Data['kod_ispolnit'] == config::$kod_org_main) {
    echo /** @lang HTML */
    "<img src='img/sign.png' width='776'></image>";
}
?>
</body>
</html>
