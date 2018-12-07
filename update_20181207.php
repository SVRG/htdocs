<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 2018-12-06
 * Time: 19:12
 */
include "class_db.php";

$db = new Db();
// Добавляем в таблицу партии поле сумма
$sql = /** @lang MySQL */
    "alter table parts
	add sum_part double default 0 null;";
$db->query($sql);

// Обновляем цены с НДС
$sql = /** @lang MySQL */
    "UPDATE parts SET price_it = ROUND(price * (100+nds)/100,2) WHERE price_it=0 OR isnull(price_it);";
$db->query($sql);

// Обновляем цены без НДС
$sql = /** @lang MySQL */
    "UPDATE parts SET price = ROUND(price * 100 / (100+nds),2) WHERE price=0 OR isnull(price);";
$db->query($sql);

// Проверка - кол-во записей должно равняться кол-ву записей в таблице партии
$sql = /** @lang MySQL */
    "SELECT * FROM parts WHERE price_it = ROUND(price*(100+nds)/100,2);";
$db->query($sql);

// Обновляем суммы
$sql = /** @lang MySQL */
    "UPDATE parts SET sum_part = ROUND(price_it * numb,2);";
$db->query($sql);

// Обновляем запросы
$sql = /** @lang MySQL */
    "DROP view view_rplan;";
$db->query($sql);
$sql = /** @lang MySQL */
    "create view view_rplan as select `trin`.`dogovory`.`kod_dogovora`                                                                     AS `kod_dogovora`,
       `trin`.`dogovory`.`nomer`                                                                            AS `nomer`,
       `trin`.`dogovory`.`doc_type`                                                                         AS `doc_type`,
       `trin`.`org`.`kod_org`                                                                               AS `kod_org`,
       `trin`.`org`.`nazv_krat`                                                                             AS `nazv_krat`,
       `trin`.`parts`.`modif`                                                                               AS `modif`,
       `trin`.`parts`.`numb`                                                                                AS `numb`,
       `trin`.`parts`.`data_postav`                                                                         AS `data_postav`,
       `trin`.`parts`.`nds`                                                                                 AS `nds`,
       `trin`.`parts`.`sum_part`                                                                            AS `sum_part`,
       `trin`.`parts`.`val`                                                                                 AS `val`,
       `trin`.`parts`.`price`                                                                               AS `price`,
       `trin`.`elem`.`kod_elem`                                                                             AS `kod_elem`,
       `trin`.`elem`.`obozn`                                                                                AS `obozn`,
       `trin`.`elem`.`shifr`                                                                                AS `shifr`,
       `trin`.`parts`.`kod_part`                                                                            AS `kod_part`,
       ifnull(`trin`.`dogovory`.`zakryt`, 0)                                                                AS `zakryt`,
       `trin`.`dogovory`.`kod_ispolnit`                                                                     AS `kod_ispolnit`,
       `trin`.`elem`.`name`                                                                                 AS `name`,
       `ispolnit`.`nazv_krat`                                                                               AS `ispolnit_nazv_krat`,
       ifnull(`view_sklad_summ_otgruz`.`summ_otgruz`, 0)                                                    AS `numb_otgruz`,
       (`trin`.`parts`.`numb` -
        ifnull(`view_sklad_summ_otgruz`.`summ_otgruz`, 0))                                                  AS `numb_ostat`,
       `trin`.`parts`.`price_or`                                                                            AS `price_or`,
       `trin`.`parts`.`data_nach`                                                                           AS `data_nach`,
       `trin`.`parts`.`price_it`                                                                            AS `price_it`
from (((((`trin`.`dogovory` join `trin`.`parts` on ((`trin`.`dogovory`.`kod_dogovora` = `trin`.`parts`.`kod_dogovora`))) join `trin`.`org` on ((`trin`.`org`.`kod_org` = `trin`.`dogovory`.`kod_org`))) join `trin`.`elem` on ((`trin`.`elem`.`kod_elem` = `trin`.`parts`.`kod_elem`))) join `trin`.`org` `ispolnit` on ((`ispolnit`.`kod_org` = `trin`.`dogovory`.`kod_ispolnit`)))
       left join `trin`.`view_sklad_summ_otgruz` on ((`trin`.`parts`.`kod_part` = `view_sklad_summ_otgruz`.`kod_part`)))
where (`trin`.`parts`.`del` = 0);";
$db->query($sql);

$sql = /** @lang MySQL */
    "DROP view view_pplan;";
$db->query($sql);
$sql = /** @lang MySQL */
    "create view view_pplan as select `trin`.`dogovory`.`kod_dogovora`                                                                     AS `kod_dogovora`,
       `trin`.`dogovory`.`nomer`                                                                            AS `nomer`,
       `trin`.`dogovory`.`doc_type`                                                                         AS `doc_type`,
       `trin`.`org`.`kod_org`                                                                               AS `kod_org`,
       `trin`.`org`.`nazv_krat`                                                                             AS `nazv_krat`,
       `trin`.`parts`.`modif`                                                                               AS `modif`,
       `trin`.`parts`.`numb`                                                                                AS `numb`,
       `trin`.`parts`.`data_postav`                                                                         AS `data_postav`,
       `trin`.`parts`.`nds`                                                                                 AS `nds`,
       `trin`.`parts`.`sum_part`                                                                            AS `sum_part`,
       `trin`.`parts`.`val`                                                                                 AS `val`,
       `trin`.`parts`.`price`                                                                               AS `price`,
       `trin`.`elem`.`kod_elem`                                                                             AS `kod_elem`,
       `trin`.`elem`.`obozn`                                                                                AS `obozn`,
       `trin`.`elem`.`shifr`                                                                                AS `shifr`,
       `trin`.`parts`.`kod_part`                                                                            AS `kod_part`,
       ifnull(`trin`.`dogovory`.`zakryt`, 0)                                                                AS `zakryt`,
       `trin`.`dogovory`.`kod_ispolnit`                                                                     AS `kod_ispolnit`,
       `trin`.`elem`.`name`                                                                                 AS `name`,
       `ispolnit`.`nazv_krat`                                                                               AS `ispolnit_nazv_krat`,
       ifnull(`view_sklad_summ_postup`.`summ_postup`, 0)                                                    AS `numb_postup`,
       (`trin`.`parts`.`numb` -
       ifnull(`view_sklad_summ_postup`.`summ_postup`, 0))                                                   AS `numb_ostat`,
       `trin`.`parts`.`price_or`                                                                            AS `price_or`,
       `trin`.`parts`.`data_nach`                                                                           AS `data_nach`,
       `trin`.`parts`.`price_it`                                                                            AS `price_it`
from (((((`trin`.`dogovory` join `trin`.`parts` on ((`trin`.`dogovory`.`kod_dogovora` = `trin`.`parts`.`kod_dogovora`))) join `trin`.`org` on ((`trin`.`org`.`kod_org` = `trin`.`dogovory`.`kod_org`))) join `trin`.`elem` on ((`trin`.`elem`.`kod_elem` = `trin`.`parts`.`kod_elem`))) join `trin`.`org` `ispolnit` on ((`ispolnit`.`kod_org` = `trin`.`dogovory`.`kod_ispolnit`)))
       left join `trin`.`view_sklad_summ_postup` on ((`trin`.`parts`.`kod_part` = `view_sklad_summ_postup`.`kod_part`)))
where (`trin`.`parts`.`del` = 0);";
$db->query($sql);

$sql = /** @lang MySQL */
    "DROP view view_dogovor_summa;";
$db->query($sql);
$sql = /** @lang MySQL */
    "create view view_dogovor_summa as 
      select parts.kod_dogovora AS `kod_dogovora`,sum(round(sum_part,2)) AS `dogovor_summa`
    from parts
    group by kod_dogovora
    order by kod_dogovora desc;";
$db->query($sql);

$sql = /** @lang MySQL */
    "drop view view_part_summ;";
$db->query($sql);

$sql = /** @lang MySQL */
    "DROP view view_dogovor_summa_plat;";
$db->query($sql);
$sql = /** @lang MySQL */
    "create view view_dogovor_summa_plat as
        select round(sum(`trin`.`plat`.`summa`),2) AS `summa_plat`,`trin`.`plat`.`kod_dogovora` AS `kod_dogovora`
        from `trin`.`plat`
        where (`trin`.`plat`.`del` = 0)
        group by `trin`.`plat`.`kod_dogovora`
        order by `trin`.`plat`.`kod_dogovora` desc;";
$db->query($sql);

// Проверка по суммам
$sql = "SELECT
dogovory.kod_dogovora,
summa_plat-dogovor_summa AS diff
FROM
view_dogovor_summa JOIN view_dogovor_summa_plat ON view_dogovor_summa.kod_dogovora=view_dogovor_summa_plat.kod_dogovora
JOIN dogovory ON dogovory.kod_dogovora=view_dogovor_summa.kod_dogovora
WHERE
zakryt=1 AND (summa_plat-dogovor_summa)<>0
ORDER BY (summa_plat-dogovor_summa) DESC;";