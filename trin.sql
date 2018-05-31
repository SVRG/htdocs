create table adresa
(
  kod_adresa int auto_increment
    primary key,
  adres text null,
  kod_org int null,
  type int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table attributes
(
  kod_attr int auto_increment
    primary key,
  value text not null,
  kod_type_attr int default '1' not null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table docum
(
  kod_docum int auto_increment
    primary key,
  name varchar(255) null,
  path varchar(255) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table docum_dogovory
(
  kod_docum_dog int auto_increment
    primary key,
  kod_docum int null,
  kod_dogovora int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table docum_elem
(
  kod_docum_elem int auto_increment
    primary key,
  kod_docum int null,
  kod_elem int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table docum_org
(
  kod_docum_org int auto_increment
    primary key,
  kod_docum int null,
  kod_org int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table dogovor_attribute
(
  kod_dogovor_attr int auto_increment
    primary key,
  kod_dogovora int not null,
  kod_attr int not null,
  del int default '0' null,
  kod_user int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null
)
  engine=InnoDB charset=utf8
;

create table dogovor_prim
(
  kod_prim int auto_increment
    primary key,
  text text null,
  kod_dogovora int null,
  kod_part int null,
  user varchar(20) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null,
  status int default '0' null
)
  engine=InnoDB charset=utf8
;

create table dogovory
(
  kod_dogovora int auto_increment
    primary key,
  nomer varchar(255) null,
  data_sost date null,
  zakryt int default '0' null,
  data_zakrytiya date null,
  kod_org int null,
  kod_ispolnit int null,
  kod_gruzopoluchat int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table elem
(
  kod_elem int auto_increment
    primary key,
  obozn varchar(255) null,
  name varchar(255) null,
  shablon varchar(255) null,
  nomen int null,
  shifr varchar(255) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table indexes
(
  kod_index int auto_increment
    primary key,
  value int not null,
  type int not null,
  source_table int not null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table kontakty
(
  kod_kontakta int auto_increment
    primary key,
  kod_org int null,
  dolg varchar(255) null,
  famil varchar(255) null,
  name varchar(255) null,
  otch varchar(255) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table kontakty_data
(
  kod_dat int auto_increment
    primary key,
  kod_kontakta int null,
  data varchar(255) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table kontakty_dogovora
(
  kod_kont_dog int auto_increment
    primary key,
  kod_kontakta int null,
  kod_dogovora int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table log
(
  kod_log int auto_increment
    primary key,
  log text null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  user varchar(20) null
)
  engine=InnoDB charset=utf8
;

create table org
(
  kod_org int auto_increment
    primary key,
  poisk varchar(255) null,
  nazv_krat varchar(255) null,
  nazv_poln varchar(255) null,
  inn varchar(255) null,
  kpp varchar(255) null,
  ogrn varchar(20) null,
  r_sch varchar(255) null,
  bank_rs varchar(255) null,
  k_sch varchar(255) null,
  bank_ks varchar(255) null,
  bik varchar(255) null,
  okpo varchar(255) null,
  okonh varchar(255) null,
  e_mail varchar(255) null,
  www varchar(255) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table org_data
(
  kod_dat int auto_increment
    primary key,
  kod_org int null,
  data varchar(255) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table org_links
(
  kod_link int auto_increment
    primary key,
  master int null,
  slave int null,
  prim varchar(255) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table parts
(
  kod_part int auto_increment
    primary key,
  kod_elem int null,
  modif varchar(255) null,
  numb double default '1' null,
  data_nach date null,
  data_postav date null,
  price_or double default '0' null,
  price_it double null,
  price double null,
  kod_dogovora int null,
  val int null,
  nds double null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table plat
(
  kod_plat int auto_increment
    primary key,
  nomer varchar(255) null,
  summa double null,
  data date null,
  prim varchar(255) null,
  kod_dogovora int null,
  user varchar(255) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table price_list
(
  kod_price int auto_increment
    primary key,
  kod_elem int null,
  price double null,
  quantity int null,
  del int default '0' null,
  kod_user int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  edit int default '0' not null,
  type int default '1' not null
)
  engine=InnoDB charset=utf8
;

create table raschet
(
  kod_rascheta int auto_increment
    primary key,
  kod_part int null,
  summa double null,
  data date null,
  type_rascheta int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table raschety_plat
(
  kod_rasch_plat int auto_increment
    primary key,
  summa double null,
  kod_rascheta int null,
  kod_plat int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table scheta
(
  kod_scheta int auto_increment
    primary key,
  nomer varchar(255) null,
  data date null,
  summa double null,
  prim varchar(255) null,
  kod_dogovora int null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table sessions
(
  kod_ses int auto_increment
    primary key,
  login varchar(20) default '' null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  ip varchar(20) null
)
  engine=InnoDB charset=utf8
;

create table sklad
(
  kod_oborota int auto_increment
    primary key,
  kod_part int null,
  numb int null,
  kod_oper int null,
  naklad varchar(255) null,
  data date null,
  oper varchar(255) null,
  poluch int default '0' null,
  data_poluch date null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table specs
(
  kod_spec int auto_increment
    primary key,
  kod_elem_base int null,
  kod_elem_sub int null,
  quantity int null,
  type int null,
  user varchar(20) null,
  time_stamp timestamp default CURRENT_TIMESTAMP not null,
  del int(2) default '0' null,
  kod_user int null,
  edit int default '0' null
)
  engine=InnoDB charset=utf8
;

create table users
(
  kod_user int auto_increment
    primary key,
  login varchar(20) default '' not null,
  password varchar(80) null,
  famil varchar(40) null,
  rt varchar(40) null,
  salt varchar(40) null
)
  engine=MyISAM charset=cp1251
;

create view view_docum_elem as
  select
    `trin`.`docum_elem`.`kod_elem` AS `kod_elem`,
    `trin`.`docum`.`name`          AS `name`,
    `trin`.`docum`.`path`          AS `path`,
    `trin`.`docum`.`kod_docum`     AS `kod_docum`
  from (`trin`.`docum`
    join `trin`.`docum_elem` on ((`trin`.`docum_elem`.`kod_docum` = `trin`.`docum`.`kod_docum`)))
  where ((`trin`.`docum_elem`.`del` = 0) and (`trin`.`docum`.`del` = 0))
  order by `trin`.`docum_elem`.`kod_docum` desc;

create view view_dogovor_data as
  select
    `view_dogovory_nvs`.`kod_dogovora`                            AS `kod_dogovora`,
    `view_dogovory_nvs`.`nomer`                                   AS `nomer`,
    `view_dogovory_nvs`.`data_sost`                               AS `data_sost`,
    `view_dogovory_nvs`.`kod_org`                                 AS `kod_org`,
    `view_dogovory_nvs`.`zakryt`                                  AS `zakryt`,
    `view_dogovory_nvs`.`nazv_krat`                               AS `nazv_krat`,
    `view_dogovory_nvs`.`kod_ispolnit`                            AS `kod_ispolnit`,
    `view_dogovory_nvs`.`ispolnit_nazv_krat`                      AS `ispolnit_nazv_krat`,
    round(ifnull(`view_dogovor_summa`.`dogovor_summa`, 0), 2)     AS `dogovor_summa`,
    round(ifnull(`view_dogovor_summa_plat`.`summa_plat`, 0), 2)   AS `summa_plat`,
    (round(ifnull(`view_dogovor_summa`.`dogovor_summa`, 0), 2) -
     round(ifnull(`view_dogovor_summa_plat`.`summa_plat`, 0), 2)) AS `dogovor_ostat`
  from ((`trin`.`view_dogovory_nvs`
    left join `trin`.`view_dogovor_summa`
      on ((`view_dogovor_summa`.`kod_dogovora` = `view_dogovory_nvs`.`kod_dogovora`))) left join
    `trin`.`view_dogovor_summa_plat`
      on ((`view_dogovor_summa_plat`.`kod_dogovora` = `view_dogovory_nvs`.`kod_dogovora`)));

create view view_dogovor_summa as
  select
    `view_part_summ`.`kod_dogovora`   AS `kod_dogovora`,
    sum(`view_part_summ`.`part_summ`) AS `dogovor_summa`
  from `trin`.`view_part_summ`
  group by `view_part_summ`.`kod_dogovora`
  order by `view_part_summ`.`kod_dogovora` desc;

create view view_dogovor_summa_plat as
  select
    sum(`trin`.`plat`.`summa`)   AS `summa_plat`,
    `trin`.`plat`.`kod_dogovora` AS `kod_dogovora`
  from `trin`.`plat`
  where (`trin`.`plat`.`del` = 0)
  group by `trin`.`plat`.`kod_dogovora`
  order by `trin`.`plat`.`kod_dogovora` desc;

create view view_dogovory_elem as
  select
    `view_dogovory_nvs`.`kod_dogovora`       AS `kod_dogovora`,
    `view_dogovory_nvs`.`nomer`              AS `nomer`,
    `view_dogovory_nvs`.`data_sost`          AS `data_sost`,
    `view_dogovory_nvs`.`kod_org`            AS `kod_org`,
    `view_dogovory_nvs`.`nazv_krat`          AS `nazv_krat`,
    `view_dogovory_nvs`.`zakryt`             AS `zakryt`,
    `view_dogovory_nvs`.`kod_ispolnit`       AS `kod_ispolnit`,
    `view_dogovory_nvs`.`ispolnit_nazv_krat` AS `ispolnit_nazv_krat`,
    `trin`.`parts`.`kod_elem`                AS `kod_elem`
  from (`trin`.`parts`
    join `trin`.`view_dogovory_nvs` on ((`view_dogovory_nvs`.`kod_dogovora` = `trin`.`parts`.`kod_dogovora`)))
  where (`trin`.`parts`.`del` = 0);

create view view_dogovory_nvs as
  select
    `trin`.`dogovory`.`kod_dogovora` AS `kod_dogovora`,
    `trin`.`dogovory`.`nomer`        AS `nomer`,
    `trin`.`dogovory`.`data_sost`    AS `data_sost`,
    `trin`.`dogovory`.`kod_org`      AS `kod_org`,
    `trin`.`org`.`nazv_krat`         AS `nazv_krat`,
    `trin`.`dogovory`.`zakryt`       AS `zakryt`,
    `trin`.`dogovory`.`kod_ispolnit` AS `kod_ispolnit`,
    `org_ispolnit`.`nazv_krat`       AS `ispolnit_nazv_krat`
  from ((`trin`.`dogovory`
    join `trin`.`org` on ((`trin`.`dogovory`.`kod_org` = `trin`.`org`.`kod_org`))) join `trin`.`org` `org_ispolnit`
      on ((`trin`.`dogovory`.`kod_ispolnit` = `org_ispolnit`.`kod_org`)))
  where (`trin`.`dogovory`.`del` = 0)
  order by `trin`.`dogovory`.`data_sost` desc;

create view view_elem as
  select
    `trin`.`elem`.`kod_elem`   AS `kod_elem`,
    `trin`.`elem`.`obozn`      AS `obozn`,
    `trin`.`elem`.`name`       AS `name`,
    `trin`.`elem`.`shablon`    AS `shablon`,
    `trin`.`elem`.`nomen`      AS `nomen`,
    `trin`.`elem`.`shifr`      AS `shifr`,
    `trin`.`elem`.`time_stamp` AS `time_stamp`
  from `trin`.`elem`
  where (`trin`.`elem`.`del` = 0)
  order by `trin`.`elem`.`obozn`, `trin`.`elem`.`nomen` desc;

create view view_elem_org as
  select
    `view_rplan`.`kod_org`   AS `kod_org`,
    `view_rplan`.`kod_elem`  AS `kod_elem`,
    `view_rplan`.`nazv_krat` AS `nazv_krat`,
    sum(`view_rplan`.`numb`) AS `numb`
  from (`trin`.`view_dogovor_summa_plat`
    join `trin`.`view_rplan` on ((`view_dogovor_summa_plat`.`kod_dogovora` = `view_rplan`.`kod_dogovora`)))
  group by `view_rplan`.`kod_org`, `view_rplan`.`kod_elem`, `view_rplan`.`nazv_krat`
  order by sum(`view_rplan`.`numb`) desc;

create view view_kontakty_dogovora as
  select
    `trin`.`kontakty`.`kod_kontakta`          AS `kod_kontakta`,
    `trin`.`kontakty`.`kod_org`               AS `kod_org`,
    `trin`.`kontakty`.`dolg`                  AS `dolg`,
    `trin`.`kontakty`.`famil`                 AS `famil`,
    `trin`.`kontakty`.`name`                  AS `name`,
    `trin`.`kontakty`.`otch`                  AS `otch`,
    `trin`.`kontakty_dogovora`.`kod_dogovora` AS `kod_dogovora`,
    `trin`.`kontakty_dogovora`.`kod_kont_dog` AS `kod_kont_dog`,
    `trin`.`org`.`nazv_krat`                  AS `nazv_krat`
  from ((`trin`.`kontakty`
    join `trin`.`kontakty_dogovora`
      on ((`trin`.`kontakty`.`kod_kontakta` = `trin`.`kontakty_dogovora`.`kod_kontakta`))) join `trin`.`org`
      on ((`trin`.`kontakty`.`kod_org` = `trin`.`org`.`kod_org`)))
  where ((`trin`.`kontakty`.`del` = 0) and (`trin`.`kontakty_dogovora`.`del` = 0));

create view view_part_summ as
  select
    `trin`.`parts`.`kod_part`                                         AS `kod_part`,
    `trin`.`parts`.`kod_dogovora`                                     AS `kod_dogovora`,
    if((ifnull(`trin`.`parts`.`price_it`, 0) = 0), if((ifnull(`trin`.`parts`.`price`, 0) = 0), round(
        (round((`trin`.`parts`.`price_or` * (1 + `trin`.`parts`.`nds`)), 2) * `trin`.`parts`.`numb`), 2), round((round((
                                                                                                                         `trin`.`parts`.`price`
                                                                                                                         *
                                                                                                                         (
                                                                                                                           1
                                                                                                                           +
                                                                                                                           `trin`.`parts`.`nds`)),
                                                                                                                       2)
                                                                                                                 *
                                                                                                                 `trin`.`parts`.`numb`),
                                                                                                                2)),
       round((`trin`.`parts`.`price_it` * `trin`.`parts`.`numb`), 2)) AS `part_summ`
  from `trin`.`parts`
  where (`trin`.`parts`.`del` = 0);

create view view_phones_kontakts as
  select
    `trin`.`kontakty`.`kod_kontakta` AS `kod_kontakta`,
    `trin`.`kontakty`.`dolg`         AS `dolg`,
    `trin`.`kontakty`.`famil`        AS `famil`,
    `trin`.`kontakty`.`name`         AS `name`,
    `trin`.`kontakty`.`otch`         AS `otch`,
    `trin`.`kontakty_data`.`data`    AS `data`
  from (`trin`.`kontakty`
    join `trin`.`kontakty_data` on ((`trin`.`kontakty`.`kod_kontakta` = `trin`.`kontakty_data`.`kod_kontakta`)))
  where (`trin`.`kontakty`.`del` = 0);

create view view_plat as
  select
    `trin`.`plat`.`nomer`                    AS `nomer`,
    `trin`.`plat`.`summa`                    AS `summa`,
    `trin`.`plat`.`data`                     AS `data`,
    `trin`.`plat`.`prim`                     AS `prim`,
    `trin`.`plat`.`kod_plat`                 AS `kod_plat`,
    `view_dogovory_nvs`.`kod_dogovora`       AS `kod_dogovora`,
    `view_dogovory_nvs`.`nomer`              AS `nomer_dogovora`,
    `view_dogovory_nvs`.`kod_org`            AS `kod_org`,
    `view_dogovory_nvs`.`nazv_krat`          AS `nazv_krat`,
    `view_dogovory_nvs`.`kod_ispolnit`       AS `kod_ispolnit`,
    `view_dogovory_nvs`.`ispolnit_nazv_krat` AS `ispolnit_nazv_krat`,
    `view_plat_raspred`.`summa_raspred`      AS `summa_raspred`
  from ((`trin`.`plat`
    join `trin`.`view_dogovory_nvs` on ((`trin`.`plat`.`kod_dogovora` = `view_dogovory_nvs`.`kod_dogovora`))) left join
    `trin`.`view_plat_raspred` on ((`trin`.`plat`.`kod_plat` = `view_plat_raspred`.`kod_plat`)))
  where (`trin`.`plat`.`del` = 0);

create view view_plat_raspred as
  select
    `trin`.`plat`.`kod_plat`            AS `kod_plat`,
    sum(`trin`.`raschety_plat`.`summa`) AS `summa_raspred`
  from (`trin`.`raschety_plat`
    join `trin`.`plat` on ((`trin`.`plat`.`kod_plat` = `trin`.`raschety_plat`.`kod_plat`)))
  where ((`trin`.`plat`.`del` = 0) and (`trin`.`raschety_plat`.`del` = 0))
  group by `trin`.`plat`.`kod_plat`;

create view view_pplan as
  select
    `trin`.`dogovory`.`kod_dogovora`                                                                     AS `kod_dogovora`,
    `trin`.`dogovory`.`nomer`                                                                            AS `nomer`,
    `trin`.`org`.`kod_org`                                                                               AS `kod_org`,
    `trin`.`org`.`nazv_krat`                                                                             AS `nazv_krat`,
    `trin`.`parts`.`modif`                                                                               AS `modif`,
    `trin`.`parts`.`numb`                                                                                AS `numb`,
    `trin`.`parts`.`data_postav`                                                                         AS `data_postav`,
    round(`trin`.`parts`.`nds`, 2)                                                                       AS `nds`,
    round(ifnull(((`trin`.`parts`.`numb` * `trin`.`parts`.`price`) * (1 + `trin`.`parts`.`nds`)), 0),
          2)                                                                                             AS `part_summa`,
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
    ifnull(`view_sklad_summ_postup`.`summ_postup`,
           0)                                                                                            AS `numb_postup`,
    (`trin`.`parts`.`numb` - ifnull(`view_sklad_summ_postup`.`summ_postup`,
                                    0))                                                                  AS `numb_ostat`,
    `trin`.`parts`.`price_or`                                                                            AS `price_or`,
    `trin`.`parts`.`data_nach`                                                                           AS `data_nach`,
    `trin`.`parts`.`price_it`                                                                            AS `price_it`
  from (((((`trin`.`dogovory`
    join `trin`.`parts` on ((`trin`.`dogovory`.`kod_dogovora` = `trin`.`parts`.`kod_dogovora`))) join `trin`.`org`
      on ((`trin`.`org`.`kod_org` = `trin`.`dogovory`.`kod_org`))) join `trin`.`elem`
      on ((`trin`.`elem`.`kod_elem` = `trin`.`parts`.`kod_elem`))) join `trin`.`org` `ispolnit`
      on ((`ispolnit`.`kod_org` = `trin`.`dogovory`.`kod_ispolnit`))) left join `trin`.`view_sklad_summ_postup`
      on ((`trin`.`parts`.`kod_part` = `view_sklad_summ_postup`.`kod_part`)))
  where (`trin`.`parts`.`del` = 0);

create view view_raschety_plat as
  select
    `trin`.`raschet`.`kod_rascheta`   AS `kod_rascheta`,
    `trin`.`raschet`.`kod_part`       AS `kod_part`,
    `trin`.`raschet`.`summa`          AS `raschet_summa`,
    `trin`.`raschet`.`data`           AS `data_rascheta`,
    `trin`.`raschet`.`type_rascheta`  AS `type_rascheta`,
    `trin`.`raschety_plat`.`kod_plat` AS `kod_plat`,
    `trin`.`raschety_plat`.`summa`    AS `summa_raspred`,
    `trin`.`plat`.`nomer`             AS `nomer`,
    `trin`.`plat`.`data`              AS `data_plat`,
    `trin`.`plat`.`prim`              AS `prim`,
    `trin`.`plat`.`kod_dogovora`      AS `kod_dogovora`
  from ((`trin`.`raschet`
    join `trin`.`raschety_plat` on ((`trin`.`raschet`.`kod_rascheta` = `trin`.`raschety_plat`.`kod_rascheta`))) join
    `trin`.`plat` on ((`trin`.`raschety_plat`.`kod_plat` = `trin`.`plat`.`kod_plat`)))
  where ((`trin`.`raschet`.`del` = 0) and (`trin`.`plat`.`del` = 0));

create view view_raschety_summ_plat as
  select
    `trin`.`raschet`.`kod_rascheta`           AS `kod_rascheta`,
    ifnull(`trin`.`raschety_plat`.`summa`, 0) AS `summa_plat`,
    `trin`.`raschet`.`kod_part`               AS `kod_part`,
    `trin`.`raschet`.`summa`                  AS `summa`
  from (`trin`.`raschet`
    left join `trin`.`raschety_plat` on ((`trin`.`raschety_plat`.`kod_rascheta` = `trin`.`raschet`.`kod_rascheta`)))
  where (`trin`.`raschet`.`del` = 0)
  group by `trin`.`raschet`.`kod_rascheta`;

create view view_rplan as
  select
    `trin`.`dogovory`.`kod_dogovora`                                                                     AS `kod_dogovora`,
    `trin`.`dogovory`.`nomer`                                                                            AS `nomer`,
    `trin`.`org`.`kod_org`                                                                               AS `kod_org`,
    `trin`.`org`.`nazv_krat`                                                                             AS `nazv_krat`,
    `trin`.`parts`.`modif`                                                                               AS `modif`,
    `trin`.`parts`.`numb`                                                                                AS `numb`,
    `trin`.`parts`.`data_postav`                                                                         AS `data_postav`,
    round(`trin`.`parts`.`nds`, 2)                                                                       AS `nds`,
    round(ifnull(((`trin`.`parts`.`numb` * `trin`.`parts`.`price`) * (1 + `trin`.`parts`.`nds`)), 0),
          2)                                                                                             AS `part_summa`,
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
    ifnull(`view_sklad_summ_otgruz`.`summ_otgruz`,
           0)                                                                                            AS `numb_otgruz`,
    (`trin`.`parts`.`numb` - ifnull(`view_sklad_summ_otgruz`.`summ_otgruz`,
                                    0))                                                                  AS `numb_ostat`,
    `trin`.`parts`.`price_or`                                                                            AS `price_or`,
    `trin`.`parts`.`data_nach`                                                                           AS `data_nach`,
    `trin`.`parts`.`price_it`                                                                            AS `price_it`
  from (((((`trin`.`dogovory`
    join `trin`.`parts` on ((`trin`.`dogovory`.`kod_dogovora` = `trin`.`parts`.`kod_dogovora`))) join `trin`.`org`
      on ((`trin`.`org`.`kod_org` = `trin`.`dogovory`.`kod_org`))) join `trin`.`elem`
      on ((`trin`.`elem`.`kod_elem` = `trin`.`parts`.`kod_elem`))) join `trin`.`org` `ispolnit`
      on ((`ispolnit`.`kod_org` = `trin`.`dogovory`.`kod_ispolnit`))) left join `trin`.`view_sklad_summ_otgruz`
      on ((`trin`.`parts`.`kod_part` = `view_sklad_summ_otgruz`.`kod_part`)))
  where (`trin`.`parts`.`del` = 0);

create view view_scheta_dogovora as
  select
    `trin`.`scheta`.`nomer`                  AS `nomer`,
    `trin`.`scheta`.`data`                   AS `data`,
    `view_dogovory_nvs`.`kod_ispolnit`       AS `kod_ispolnit`,
    `view_dogovory_nvs`.`ispolnit_nazv_krat` AS `ispolnit_nazv_krat`,
    `view_dogovory_nvs`.`kod_org`            AS `kod_org`,
    `view_dogovory_nvs`.`data_sost`          AS `data_sost`,
    `view_dogovory_nvs`.`kod_dogovora`       AS `kod_dogovora`,
    `view_dogovory_nvs`.`nazv_krat`          AS `nazv_krat`
  from (`trin`.`view_dogovory_nvs`
    join `trin`.`scheta` on ((`view_dogovory_nvs`.`kod_dogovora` = `trin`.`scheta`.`kod_dogovora`)))
  where (`trin`.`scheta`.`del` = 0);

create view view_scheta_dogovory_all as
  select
    `view_dogovory_nvs`.`kod_org`            AS `kod_org`,
    `view_dogovory_nvs`.`kod_dogovora`       AS `kod_dogovora`,
    `view_dogovory_nvs`.`nomer`              AS `nomer`,
    `view_dogovory_nvs`.`kod_ispolnit`       AS `kod_ispolnit`,
    `view_dogovory_nvs`.`ispolnit_nazv_krat` AS `ispolnit_nazv_krat`,
    `view_dogovory_nvs`.`data_sost`          AS `data_sost`,
    `view_dogovory_nvs`.`nazv_krat`          AS `nazv_krat`
  from `trin`.`view_dogovory_nvs`
  union all select
              `view_scheta_dogovora`.`kod_org`            AS `kod_org`,
              `view_scheta_dogovora`.`kod_dogovora`       AS `kod_dogovora`,
              `view_scheta_dogovora`.`nomer`              AS `nomer`,
              `view_scheta_dogovora`.`kod_ispolnit`       AS `kod_ispolnit`,
              `view_scheta_dogovora`.`ispolnit_nazv_krat` AS `ispolnit_nazv_krat`,
              `view_scheta_dogovora`.`data`               AS `DATA`,
              `view_scheta_dogovora`.`nazv_krat`          AS `nazv_krat`
            from `trin`.`view_scheta_dogovora`
  order by `data_sost` desc, `nomer` desc;

create view view_sklad as
  select
    `trin`.`sklad`.`kod_part`              AS `kod_part`,
    `trin`.`sklad`.`numb`                  AS `numb`,
    `trin`.`elem`.`name`                   AS `name`,
    `trin`.`dogovory`.`kod_dogovora`       AS `kod_dogovora`,
    `trin`.`dogovory`.`nomer`              AS `nomer`,
    `trin`.`elem`.`kod_elem`               AS `kod_elem`,
    `trin`.`sklad`.`naklad`                AS `naklad`,
    `trin`.`org`.`kod_org`                 AS `kod_org`,
    `trin`.`org`.`nazv_krat`               AS `nazv_krat`,
    `trin`.`sklad`.`oper`                  AS `oper`,
    `trin`.`sklad`.`kod_oper`              AS `kod_oper`,
    `view_dogovor_summa`.`dogovor_summa`   AS `dogovor_summa`,
    `view_dogovor_summa_plat`.`summa_plat` AS `summa_plat`,
    `trin`.`sklad`.`data`                  AS `data`,
    `trin`.`sklad`.`kod_oborota`           AS `kod_oborota`,
    `trin`.`sklad`.`poluch`                AS `poluch`
  from ((((((`trin`.`sklad`
    join `trin`.`parts` on ((`trin`.`sklad`.`kod_part` = `trin`.`parts`.`kod_part`))) join `trin`.`elem`
      on ((`trin`.`parts`.`kod_elem` = `trin`.`elem`.`kod_elem`))) join `trin`.`dogovory`
      on ((`trin`.`parts`.`kod_dogovora` = `trin`.`dogovory`.`kod_dogovora`))) join `trin`.`org`
      on ((`trin`.`dogovory`.`kod_org` = `trin`.`org`.`kod_org`))) left join `trin`.`view_dogovor_summa`
      on (((`trin`.`dogovory`.`kod_dogovora` = `view_dogovor_summa`.`kod_dogovora`) and
           (`trin`.`dogovory`.`kod_dogovora` = `view_dogovor_summa`.`kod_dogovora`)))) left join
    `trin`.`view_dogovor_summa_plat` on ((`trin`.`dogovory`.`kod_dogovora` = `view_dogovor_summa_plat`.`kod_dogovora`)))
  where ((`trin`.`sklad`.`kod_oper` = 2) and (`trin`.`sklad`.`del` = 0))
  order by `trin`.`sklad`.`data` desc;

create view view_sklad_otgruzka as
  select
    `trin`.`sklad`.`kod_part` AS `kod_part`,
    `trin`.`sklad`.`numb`     AS `numb`,
    `trin`.`sklad`.`kod_oper` AS `kod_oper`
  from `trin`.`sklad`
  where ((`trin`.`sklad`.`kod_oper` = 2) and (`trin`.`sklad`.`del` = 0));

create view view_sklad_postuplenie as
  select
    `trin`.`sklad`.`kod_part` AS `kod_part`,
    `trin`.`sklad`.`numb`     AS `numb`,
    `trin`.`sklad`.`kod_oper` AS `kod_oper`
  from `trin`.`sklad`
  where ((`trin`.`sklad`.`kod_oper` = 1) and (`trin`.`sklad`.`del` = 0));

create view view_sklad_summ_otgruz as
  select
    `view_sklad_otgruzka`.`kod_part`  AS `kod_part`,
    sum(`view_sklad_otgruzka`.`numb`) AS `summ_otgruz`
  from `trin`.`view_sklad_otgruzka`
  group by `view_sklad_otgruzka`.`kod_part`;

create view view_sklad_summ_postup as
  select
    `view_sklad_postuplenie`.`kod_part`  AS `kod_part`,
    sum(`view_sklad_postuplenie`.`numb`) AS `summ_postup`
  from `trin`.`view_sklad_postuplenie`
  group by `view_sklad_postuplenie`.`kod_part`;