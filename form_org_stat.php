<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 17/01/18
 * Time: 08:24
 */
include_once "security.php";
include_once "class_org.php";
$kod_org = (int)$_GET['kod_org'];
$org = new Org();
$org->kod_org = $kod_org;
$org->getData();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo $org->Data['nazv_krat']; ?></title>
    <script src="widgets/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
    <script src="widgets/SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
    <link href="widgets/SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php include "header.php"; ?>
<div id="pagecell1">
    <?php
    echo '<h1>' . $org->getFormLink() . '</h1>';
    echo '<br>Задолженность: ' . $org->getDolg() . "<br>";

    $sql = "";
    if (isset($_GET['y'])) {
        $y = (int)$_GET['y'];

        $data_s = "$y-01-01"; // Начало периода
        $data_e = ($y + 1) . "-01-01"; // Конец периода
        $sql = /** @lang SQL */
            "SELECT view_rplan.kod_elem, 
                            view_rplan.name, 
                            sum(view_rplan.numb) AS summ_numb, 
                            view_rplan.kod_org, 
                            view_dogovor_summa_plat.summa_plat
                        FROM view_rplan INNER JOIN view_dogovor_summa_plat ON view_rplan.kod_dogovora = view_dogovor_summa_plat.kod_dogovora
                        WHERE view_rplan.kod_org=$org->kod_org
                        AND view_rplan.data_postav >= '$data_s' AND view_rplan.data_postav <= '$data_e'
                        AND
                        view_dogovor_summa_plat.summa_plat>0
                        GROUP BY view_rplan.kod_elem
                        ORDER BY summ_numb DESC
                      ";
    }
    echo "<br><table width='50%'><tr><td><b>Номенклатура по договорам:</b></td></tr><tr><td>" . $org->formOrgNomen($sql) . "</td></tr></table>";
    echo "<br><b>Сумма платежей:</b><br>" . $org->formOrgPays(false); // Платежи за период
    echo "<br><b>Договоры:</b>";
    try {
        echo $org->formDocs();
    } catch (Exception $e) {
    } // Договоры за период
    $doc = new Doc();
    $doc->kod_org = $kod_org;
    echo $doc->formSGPHistory(); // История отгрузок
    ?>
</div>
</body>
</html>
