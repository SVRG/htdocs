CREATE VIEW view_elem_org AS
  SELECT
    `view_rplan`.`kod_org`   AS `kod_org`,
    `view_rplan`.`kod_elem`  AS `kod_elem`,
    `view_rplan`.`nazv_krat` AS `nazv_krat`,
    sum(`view_rplan`.`numb`) AS `numb`
  FROM (`trin`.`view_dogovor_summa_plat`
    JOIN `trin`.`view_rplan`
      ON ((`view_dogovor_summa_plat`.`kod_dogovora` = `view_rplan`.`kod_dogovora`)))
  GROUP BY `view_rplan`.`kod_org`, `view_rplan`.`kod_elem`, `view_rplan`.`nazv_krat`
  ORDER BY sum(`view_rplan`.`numb`) DESC;
