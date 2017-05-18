<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>ODBC-MySQL</title>
</head>

<?php
include_once "class_db_import.php";
include_once "class_odbc.php";
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 21.09.16
 * Time: 19:57
 * Важно! - При вставке проверять поля INT! Если они пустые то строка не вставится
 */

$db = new Db_import();
$odbc = new ODBC();
ini_set('max_execution_time', 1000); // Установка времени тайм-аута во избежания ошибки

$users = 0;
$adresa = 0;
$docum = 0;
$docum_dogovory = 0;
$dogovor_prim = 0;
$dogovory = 0;
$elem = 0;
$kontakty = 0;
$kontakty_dogovora = 0;
$org = 0;
$parts = 0;
$kontakty_data = 0;
$plat = 0;
$raschet = 0;
$raschety_plat = 0;
$scheta = 0;
$sklad = 0;
$org_links = 0;
$docum_elem = 0;
$docum_org = 0;
$view = 0;
//
if ($users == 1) {
    // sql to create table
    $sql = /** @lang SQL */
        "DROP TABLE IF EXISTS `users`;
            CREATE TABLE `users` (
              kod_user INT(11) NOT NULL AUTO_INCREMENT,
              `Name` VARCHAR(20) NOT NULL DEFAULT '',
              `Pass` VARCHAR(10) DEFAULT NULL,
              `FName` VARCHAR(40) DEFAULT NULL,
              `rt` VARCHAR(40) DEFAULT NULL,
              PRIMARY KEY (`kod_user`)
            ) ENGINE=MyISAM AUTO_INCREMENT=53
    ";
    //$db->query($sql);

    $sql = /** @lang SQL */
        "
            INSERT INTO `users` VALUES (1, 'Tikhomirov', 'master123', 'Тихомиров Сергей', 'admin'),
                                       (51, 'Charykova', 'nvs12345', 'Чарыкова Татьяна', 'oper'),
                                        (46, 'Mityushin', 'nvs12345', 'Митюшин Максим', 'oper'),
                                        (50, 'Ukhova', 'nvs12345', 'Ухова Евгения', 'oper'),
                                        (49, 'Vasin', 'nvs12345', 'Васин Андрей', 'oper'),
                                        (45, 'Morgunova', 'nvs12345', 'Моргунова Елена', 'oper');";
    $db->query($sql);
}
//----------------------------------------------------------------------------------------------------------------------
if ($adresa == 1) {
    // sql drop table
    $sql = /** @lang SQL */
        "DROP TABLE IF EXISTS adresa";
    $db->query($sql);

    // sql to create table
    $sql = "CREATE TABLE adresa (
    kod_adresa INT(6),
    adres TEXT,
    kod_org INT,
    type INT,
    time_stamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $db->query($sql);

    // Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM Адреса";
    $odbc->ex();

    // Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $kod_adresa = $row['Код_Адреса'];
        $adres = $row['Адрес'];
        $kod_org = $row['Код_Организации'];
        $type = $row['ТипАдреса'];

        $sql = "INSERT INTO adresa (kod_adresa,adres,kod_org,type) VALUES($kod_adresa,'$adres',$kod_org,$type)";
        $db->query($sql);
        //echo $sql.'<br>';
    }

    $sql = "ALTER TABLE adresa MODIFY kod_adresa INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "adresa Inserted: " . $i;

}
//----------------------------------------------------------------------------------------------------------------------
//

if ($docum == 1) {
    $table = "docum";
    $table_odbc = "Документы";

// Sourse Names                | Dest Names                 | Dest Type
    $id_odbc = "Код_Документа";
    $id = "kod_docum";
    $id_type = "INT";
    $f1_odbc = "Наименование";
    $f1 = "name";
    $f1_type = "VARCHAR(255)";
    $f2_odbc = "Путь";
    $f2 = "path";
    $f2_type = "VARCHAR(255)";
    $f3_odbc = "Date_CP";
    $f3 = "time_stamp";
    $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,'$field1','$field2','$field3')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($docum_dogovory == 1) {
    $table = "docum_dogovory";
    $table_odbc = "ДокументыДоговора";

// Sourse Names                | Dest Names                 | Dest Type
    $id_odbc = "Код_Связи";
    $id = "kod_docum_dog";
    $id_type = "INT";
    $f1_odbc = "Код_Документа";
    $f1 = "kod_docum";
    $f1_type = "INT";
    $f2_odbc = "Код_Договора";
    $f2 = "kod_dogovora";
    $f2_type = "INT";
    $f3_odbc = "DateCP";
    $f3 = "time_stamp";
    $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,$field2,'$field3')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($dogovor_prim == 1) {
    $table = "dogovor_prim";
    $table_odbc = "ПримечаниеДоговора";

// Sourse Names                | Dest Names                 | Dest Type
    $id_odbc = "Код_Примечания";
    $id = "kod_prim";
    $id_type = "INT";
    $f1_odbc = "Текст";
    $f1 = "text";
    $f1_type = "TEXT";
    $f2_odbc = "Код_Договора";
    $f2 = "kod_dogovora";
    $f2_type = "INT";
    $f3_odbc = "user";
    $f3 = "user";
    $f3_type = "VARCHAR(20)";
    $f4_odbc = "Дата";
    $f4 = "time_stamp";
    $f4_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4) VALUES($field_id,'$field1',$field2,'$field3','$field4')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}

//----------------------------------------------------------------------------------------------------------------------
//
if ($dogovory == 1) {
    $table = "dogovory";
    $table_odbc = "Договоры";

// Sourse Names                | Dest Names                 | Dest Type
    $id_odbc = "Код_договора";
    $id = "kod_dogovora";
    $id_type = "INT";
    $f1_odbc = "Номер";
    $f1 = "nomer";
    $f1_type = "VARCHAR(255)";
    $f2_odbc = "Дата_составления";
    $f2 = "data_sost";
    $f2_type = "DATE";
    $f3_odbc = "Закрыт";
    $f3 = "zakryt";
    $f3_type = "INT";
    $f4_odbc = "Дата_закрытия";
    $f4 = "data_zakrytiya";
    $f4_type = "DATE";
    $f5_odbc = "Код_организации";
    $f5 = "kod_org";
    $f5_type = "INT";
    $f6_odbc = "Код_Исполнителя";
    $f6 = "kod_ispolnit";
    $f6_type = "INT";
    $f7_odbc = "Код_Грузополучателя";
    $f7 = "kod_gruzopoluchat";
    $f7_type = "INT";
    $f8_odbc = "DateCP";
    $f8 = "time_stamp";
    $f8_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type,
    $f6 $f6_type,
    $f7 $f7_type,
    $f8 $f8_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];
        $field7 = $row[$f7_odbc];
        $field8 = $row[$f8_odbc];

        if ($field7 == "") // Если не задан код грузополучателя подставляем код заказчика
            $field7 = $field5;

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8) VALUES($field_id,'$field1','$field2',$field3,'$field4',$field5,$field6,$field7,'$field8')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($elem == 1) {
    $table = "elem";
    $table_odbc = "Номенклатура_Экспорт";

// Sourse Names                | Dest Names                 | Dest Type
    $id_odbc = "Код_элемента";
    $id = "kod_elem";
    $id_type = "INT";
    $f1_odbc = "Обозначение";
    $f1 = "obozn";
    $f1_type = "VARCHAR(255)";
    $f2_odbc = "Наименование";
    $f2 = "name";
    $f2_type = "VARCHAR(255)";
    $f3_odbc = "Шаблон";
    $f3 = "shablon";
    $f3_type = "VARCHAR(255)";
    $f4_odbc = "NOMEN";
    $f4 = "nomen";
    $f4_type = "INT";
    $f5_odbc = "Шифр";
    $f5 = "shifr";
    $f5_type = "VARCHAR(255)";
    $f6_odbc = "Date_CP";
    $f6 = "time_stamp";
    $f6_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";

    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type,
    $f6 $f6_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];

        if ($field4 == "") // Если не задан тип номенклатуры
            $field4 = 0;

        if ($field5 != "") // Если есть Шифрт то заменяем обозначение?
            $field1 = $field5;

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6) VALUES($field_id,'$field1','$field2','$field3',$field4,'$field5','$field6')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($kontakty) {
    $table = "kontakty";
    $table_odbc = "Контакты";

// Sourse Names                | Dest Names                 | Dest Type
    $id_odbc = "Код_Контакта";
    $id = "kod_kontakta";
    $id_type = "INT";
    $f1_odbc = "Код_Организации";
    $f1 = "kod_org";
    $f1_type = "INT";
    $f2_odbc = "Должность";
    $f2 = "dolg";
    $f2_type = "VARCHAR(255)";
    $f3_odbc = "Фамилия";
    $f3 = "famil";
    $f3_type = "VARCHAR(255)";
    $f4_odbc = "Имя";
    $f4 = "name";
    $f4_type = "VARCHAR(255)";
    $f5_odbc = "Отчество";
    $f5 = "otch";
    $f5_type = "VARCHAR(255)";

    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type
        )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];

        if ($field1 == "") // Если не задан тип номенклатуры
            $field1 = 0;

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5) VALUES($field_id,$field1,'$field2','$field3','$field4','$field5')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//

if ($kontakty_dogovora == 1) {
    $table = "kontakty_dogovora";
    $table_odbc = "КонтактныеЛица";

// Sourse Names                | Dest Names                 | Dest Type
    $id_odbc = "КодКонтактногоЛица";
    $id = "kod_kont_dog";
    $id_type = "INT";
    $f1_odbc = "Код_Контакта";
    $f1 = "kod_kontakta";
    $f1_type = "INT";
    $f2_odbc = "Код_Договора";
    $f2 = "kod_dogovora";
    $f2_type = "INT";
    $f3_odbc = "DateCP";
    $f3 = "time_stamp";
    $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        if ($field1 == "") // Если не задан тип номенклатуры
            $field1 = 0;

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,$field2,'$field3')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($org == 1) {
    $table = "org";

    $table_odbc = "Организации";

// Sourse Names                | Dest Names                   | Dest Type
    $id_odbc = "Код_Организации";
    $id = "kod_org";
    $id_type = "INT";
    $f1_odbc = "Поиск";
    $f1 = "poisk";
    $f1_type = "VARCHAR(255)";
    $f2_odbc = "Название_крат";
    $f2 = "nazv_krat";
    $f2_type = "VARCHAR(255)";
    $f3_odbc = "Название_полн";
    $f3 = "nazv_poln";
    $f3_type = "VARCHAR(255)";
    $f4_odbc = "ИНН";
    $f4 = "inn";
    $f4_type = "VARCHAR(255)";
    $f5_odbc = "КПП";
    $f5 = "kpp";
    $f5_type = "VARCHAR(255)";
    $f6_odbc = "Р_сч";
    $f6 = "r_sch";
    $f6_type = "VARCHAR(255)";
    $f7_odbc = "БанкРС";
    $f7 = "bank_rs";
    $f7_type = "VARCHAR(255)";
    $f8_odbc = "К_сч";
    $f8 = "k_sch";
    $f8_type = "VARCHAR(255)";
    $f9_odbc = "БанкКС";
    $f9 = "bank_ks";
    $f9_type = "VARCHAR(255)";
    $f10_odbc = "БИК";
    $f10 = "bik";
    $f10_type = "VARCHAR(255)";
    $f11_odbc = "ОКПО";
    $f11 = "okpo";
    $f11_type = "VARCHAR(255)";
    $f12_odbc = "ОКОНХ";
    $f12 = "okonh";
    $f12_type = "VARCHAR(255)";
    $f13_odbc = "e_mail";
    $f13 = "e_mail";
    $f13_type = "VARCHAR(255)";
    $f14_odbc = "www";
    $f14 = "www";
    $f14_type = "VARCHAR(255)";
    $f15_odbc = "Date_CP";
    $f15 = "time_stamp";
    $f15_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type,
    $f6 $f6_type,
    $f7 $f7_type,
    $f8 $f8_type,
    $f9 $f9_type,
    $f10 $f10_type,
    $f11 $f11_type,
    $f12 $f12_type,
    $f13 $f13_type,
    $f14 $f14_type,
    $f15 $f15_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];
        $field7 = $row[$f7_odbc];
        $field8 = $row[$f8_odbc];
        $field9 = $row[$f9_odbc];
        $field10 = $row[$f10_odbc];
        $field11 = $row[$f11_odbc];
        $field12 = $row[$f12_odbc];
        $field13 = $row[$f13_odbc];
        $field14 = $row[$f14_odbc];
        $field15 = $row[$f15_odbc];

        if ($field7 == "") // Если не задан код грузополучателя подставляем код заказчика
            $field7 = $field5;

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$f9,$f10,$f11,$f12,$f13,$f14,$f15) 
            VALUES($field_id,'$field1','$field2','$field3','$field4','$field5','$field6','$field7',
            '$field8','$field9','$field10','$field11','$field12','$field13','$field14','$field15')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}

//----------------------------------------------------------------------------------------------------------------------
//
if ($parts == 1) {
    $table = "parts";
    $table_odbc = "Партии";

// Sourse Names                | Dest Names                   | Dest Type
    $id_odbc = "Код_партии";
    $id = "kod_part";
    $id_type = "INT";
    $f1_odbc = "Код_элемента";
    $f1 = "kod_elem";
    $f1_type = "INT";
    $f2_odbc = "Mod";
    $f2 = "modif";
    $f2_type = "VARCHAR(255)";
    $f3_odbc = "Количество";
    $f3 = "numb";
    $f3_type = "DOUBLE";
    $f4_odbc = "Дата_поставки";
    $f4 = "data_postav";
    $f4_type = "DATE";
    $f5_odbc = "Цена_ТФ";
    $f5 = "price";
    $f5_type = "DOUBLE";
    $f6_odbc = "Код_договора";
    $f6 = "kod_dogovora";
    $f6_type = "INT";
    $f7_odbc = "Валюта";
    $f7 = "val";
    $f7_type = "INT";
    $f8_odbc = "НДС";
    $f8 = "nds";
    $f8_type = "DOUBLE";
    $f9_odbc = "DateCP";
    $f9 = "time_stamp";
    $f9_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type,
    $f6 $f6_type,
    $f7 $f7_type,
    $f8 $f8_type,
    $f9 $f9_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];
        $field7 = $row[$f7_odbc];
        $field8 = $row[$f8_odbc];
        $field9 = $row[$f9_odbc];

        if ($field7 == "") // Если не задан код грузополучателя подставляем код заказчика
            $field7 = $field5;

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$f9) 
            VALUES($field_id,$field1,'$field2',$field3,'$field4',$field5,$field6,$field7,$field8,'$field9')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($kontakty_data == 1) {
// !Разбиваем на две таблицы по контактам и по организации
    $table = "kontakty_data";
    $table2 = "org_data";
    $table_odbc = "Телефоны";

// Sourse Names                | Dest Names                 | Dest Type
    $id_odbc = "Код Телефона";
    $id = "kod_dat";
    $id_type = "INT";
    $f1_odbc = "Код_Контакта";
    $f1 = "kod_kontakta";
    $f1_type = "INT";
    $f2_odbc = "Телефон";
    $f2 = "data";
    $f2_type = "VARCHAR(255)";
    $f3_odbc = "Date_CP";
    $f3 = "time_stamp";
    $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    $f4_odbc = "Код_Организации";
    $f4 = "kod_org";
    $f4_type = "INT";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS $table2";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
    $db->query($sql);

// sql to create table2
    $sql = "CREATE TABLE $table2 (
    $id $id_type,
    $f4 $f4_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];

        if ($field1 != "") {
            $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,'$field2','$field3')";
            $db->query($sql);
            // Проверяем записалась ли строка
            $db->query("SELECT * FROM $table WHERE $id=$field_id");
            if ($db->cnt != 1)
                echo "!!!!!!!!! Err: " . $sql;
        } elseif ($field4 != "") {
            $sql = "INSERT INTO $table2 ($id,$f4,$f2,$f3) VALUES($field_id,$field4,'$field2','$field3')";
            $db->query($sql);
            // Проверяем записалась ли строка
            $db->query("SELECT * FROM $table2 WHERE $id=$field_id");
            if ($db->cnt != 1)
                echo "!!!!!!!!! Err: " . $sql;
        }

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    $sql = "ALTER TABLE $table2 MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($plat == 1) {
    $table = "plat";

    $table_odbc = "Платежи";

// Sourse Names                | Dest Names                   | Dest Type
    $id_odbc = "Код_платежа";
    $id = "kod_plat";
    $id_type = "INT";
    $f1_odbc = "Номер_ПП";
    $f1 = "nomer";
    $f1_type = "VARCHAR(255)";
    $f2_odbc = "Сумма";
    $f2 = "summa";
    $f2_type = "DOUBLE";
    $f3_odbc = "Дата";
    $f3 = "data";
    $f3_type = "DATE";
    $f4_odbc = "Примечание";
    $f4 = "prim";
    $f4_type = "VARCHAR(255)";
    $f5_odbc = "Код_договора";
    $f5 = "kod_dogovora";
    $f5_type = "INT";
    $f6_odbc = "Date_CP";
    $f6 = "time_stamp";
    $f6_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    $f7_odbc = "";
    $f7 = "user";
    $f7_type = "VARCHAR(255)";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type,
    $f6 $f6_type,
    $f7 $f7_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6) 
            VALUES($field_id,'$field1',$field2,'$field3','$field4',$field5,'$field6')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($raschet == 1) {
    $table = "raschet";
    $table_odbc = "Расчет";

// Sourse Names                | Dest Names                   | Dest Type
    $id_odbc = "Код_расчета";
    $id = "kod_rascheta";
    $id_type = "INT";
    $f1_odbc = "Код_партии";
    $f1 = "kod_part";
    $f1_type = "INT";
    $f2_odbc = "Сумма";
    $f2 = "summa";
    $f2_type = "DOUBLE";
    $f3_odbc = "Дата";
    $f3 = "data";
    $f3_type = "DATE";
    $f4_odbc = "Тип_расчета";
    $f4 = "type_rascheta";
    $f4_type = "INT";
    $f5_odbc = "";
    $f5 = "time_stamp";
    $f5_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    $f6_odbc = "";
    $f6 = "user";
    $f6_type = "VARCHAR(255)";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type,
    $f6 $f6_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];

        if ($field4 == "") // Если не задан код грузополучателя подставляем код заказчика
            $field4 = 1;

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4) 
            VALUES($field_id,$field1,$field2,'$field3',$field4)";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($raschety_plat == 1) {

    $table = "raschety_plat";
    $table_odbc = "Расчеты_Платежи";

// Sourse Names                | Dest Names                   | Dest Type
    $id_odbc = "Код_поступления";
    $id = "kod_rasch_plat";
    $id_type = "INT";
    $f1_odbc = "Сумма";
    $f1 = "summa";
    $f1_type = "DOUBLE";
    $f2_odbc = "Код_Расчета";
    $f2 = "kod_rascheta";
    $f2_type = "INT";
    $f3_odbc = "Код_Платежа";
    $f3 = "kod_plat";
    $f3_type = "INT";
    $f4_odbc = "";
    $f4 = "time_stamp";
    $f4_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    $f5_odbc = "";
    $f5 = "user";
    $f5_type = "VARCHAR(255)";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) 
            VALUES($field_id,$field1,$field2,$field3)";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($scheta == 1) {
    $table = "scheta";
    $table_odbc = "Счета";

// Sourse Names                | Dest Names                   | Dest Type
    $id_odbc = "Код_Счета";
    $id = "kod_scheta";
    $id_type = "INT";
    $f1_odbc = "Номер";
    $f1 = "nomer";
    $f1_type = "VARCHAR(255)";
    $f2_odbc = "Дата";
    $f2 = "data";
    $f2_type = "DATE";
    $f3_odbc = "Сумма";
    $f3 = "summa";
    $f3_type = "DOUBLE";
    $f4_odbc = "Примечание";
    $f4 = "prim";
    $f4_type = "VARCHAR(255)";
    $f5_odbc = "Код_Договора";
    $f5 = "kod_dogovora";
    $f5_type = "INT";
    $f6_odbc = "Date_CP";
    $f6 = "time_stamp";
    $f6_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type,
    $f6 $f6_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6) 
            VALUES($field_id,'$field1','$field2',$field3,'$field4',$field5,'$field6')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($sklad == 1) {
    $table = "sklad";
    $table_odbc = "Склад";

// Sourse Names                   | Dest Names                   | Dest Type
    $id_odbc = "Код_оборота";
    $id = "kod_oborota";
    $id_type = "INT";
    $f1_odbc = "Код_партии";
    $f1 = "kod_part";
    $f1_type = "INT";
    $f2_odbc = "Количество";
    $f2 = "numb";
    $f2_type = "INT";
    $f3_odbc = "Код_операции";
    $f3 = "kod_oper";
    $f3_type = "INT";
    $f4_odbc = "Накладная";
    $f4 = "naklad";
    $f4_type = "VARCHAR(255)";
    $f5_odbc = "Дата";
    $f5 = "data";
    $f5_type = "DATE";
    $f6_odbc = "Operator";
    $f6 = "oper";
    $f6_type = "VARCHAR(255)";
    $f7_odbc = "Получено";
    $f7 = "poluch";
    $f7_type = "INT";
    $f8_odbc = "Дата_ОтметкиПолучения";
    $f8 = "data_poluch";
    $f8_type = "DATE";
    $f9_odbc = "DateCP";
    $f9 = "time_stamp";
    $f9_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type,
    $f5 $f5_type,
    $f6 $f6_type,
    $f7 $f7_type,
    $f8 $f8_type,
    $f9 $f9_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];
        $field7 = $row[$f7_odbc];
        $field8 = $row[$f8_odbc];
        $field9 = $row[$f9_odbc];

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$f9) 
            VALUES($field_id,$field1,$field2,$field3,'$field4','$field5','$field6',$field7,'$field8','$field9')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($org_links == 1) {
    $table = "org_links";
    $table_odbc = "Организации_связь";

// Sourse Names                   | Dest Names                   | Dest Type
    $id_odbc = "Код_Связи";
    $id = "kod_link";
    $id_type = "INT";
    $f1_odbc = "Master";
    $f1 = "master";
    $f1_type = "INT";
    $f2_odbc = "Slave";
    $f2 = "slave";
    $f2_type = "INT";
    $f3_odbc = "Тип_Связи";
    $f3 = "prim";
    $f3_type = "VARCHAR(255)";
    $f4_odbc = "Date_CP";
    $f4 = "time_stamp";
    $f4_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type,
    $f4 $f4_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];

        // Записываем строку
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4) 
            VALUES($field_id,$field1,$field2,'$field3','$field4')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql . "<br>";

        //if($i<10)
        //    echo $sql.'<br>';
    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($docum_elem == 1) {
    $table = "docum_elem";
    $table_odbc = "ДокументыИзделия";

// Sourse Names                | Dest Names                   | Dest Type
    $id_odbc = "Код_Связи";
    $id = "kod_docum_elem";
    $id_type = "INT";
    $f1_odbc = "Код_Документа";
    $f1 = "kod_docum";
    $f1_type = "INT";
    $f2_odbc = "Код_Элемента";
    $f2 = "kod_elem";
    $f2_type = "INT";
    $f3_odbc = "DateCP";
    $f3 = "time_stamp";
    $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,$field2,'$field3')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//
//----------------------------------------------------------------------------------------------------------------------
//
if ($docum_org == 1) {
    $table = "docum_org";
    $table_odbc = "ДокументыОрганизации";

// Sourse Names                | Dest Names                   | Dest Type
    $id_odbc = "Код_Связи";
    $id = "kod_docum_org";
    $id_type = "INT";
    $f1_odbc = "Код_Документа";
    $f1 = "kod_docum";
    $f1_type = "INT";
    $f2_odbc = "Код_Организации";
    $f2 = "kod_org";
    $f2_type = "INT";
    $f3_odbc = "Date_CP";
    $f3 = "time_stamp";
    $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


    $sql = "DROP TABLE IF EXISTS $table";
    $db->query($sql);

// sql to create table
    $sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
    $db->query($sql);

// Запросить данные из ODBC
    $odbc->sql = "SELECT * FROM $table_odbc";
    $odbc->ex();

// Вставить данные в MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,$field2,'$field3')";
        $db->query($sql);

        // Проверяем записалась ли строка
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if ($db->cnt != 1)
            echo "!!!!!!!!! Err: " . $sql;

    }

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "$table_odbc -> $table Inserted: $i";
}
//
//----------------------------------------------------------------------------------------------------------------------
//
if ($view == 1) {
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_docum_elem";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_docum_elem AS 
        SELECT
            docum_elem.kod_elem,
            docum.`name`,
            docum.path,
            docum.kod_docum
        FROM
            docum
        INNER JOIN docum_elem ON docum_elem.kod_docum = docum.kod_docum 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_docum_elem");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_dogovory_nvs";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_dogovory_nvs AS 
        SELECT
            dogovory.kod_dogovora,
            dogovory.nomer,
            dogovory.data_sost,
            dogovory.kod_org,
            org.nazv_krat,
            dogovory.zakryt,
            dogovory.kod_ispolnit,
            org_ispolnit.nazv_krat AS ispolnit_nazv_krat
        FROM
            dogovory
        INNER JOIN org ON dogovory.kod_org = org.kod_org
        INNER JOIN org AS org_ispolnit ON dogovory.kod_ispolnit = org_ispolnit.kod_org
        ORDER BY
            dogovory.data_sost DESC
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_dogovory_nvs");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_dogovor_summa";
    $db->query($sql);

// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_dogovor_summa AS 
        SELECT
            parts.kod_dogovora,
            Sum(parts.price*parts.numb*(1+parts.nds)) AS dogovor_summa
        FROM
            parts
        GROUP BY
            parts.kod_dogovora
        ORDER BY
            parts.kod_dogovora DESC 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_dogovor_summa");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_dogovor_summa_plat";
    $db->query($sql);

// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_dogovor_summa_plat AS 
        SELECT
            Sum(plat.summa) AS summa_plat,
            plat.kod_dogovora
        FROM
            plat
        GROUP BY
            plat.kod_dogovora
        ORDER BY
            plat.kod_dogovora DESC
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_dogovor_summa_plat");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_dogovor_data";
    $db->query($sql);

// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_dogovor_data AS
        SELECT
            view_dogovory_nvs.kod_dogovora,
            view_dogovory_nvs.nomer,
            view_dogovory_nvs.data_sost,
            view_dogovory_nvs.kod_org,
            view_dogovory_nvs.zakryt,
            view_dogovory_nvs.nazv_krat,
            view_dogovory_nvs.kod_ispolnit,
            view_dogovory_nvs.ispolnit_nazv_krat,
            ROUND(IFNULL(dogovor_summa,0),2) AS dogovor_summa,
            ROUND(IFNULL(summa_plat,0),2) AS summa_plat,
            ROUND(IFNULL(dogovor_summa,0),2)-ROUND(IFNULL(summa_plat,0),2) AS dogovor_ostat
        FROM
            view_dogovory_nvs
        LEFT JOIN view_dogovor_summa ON view_dogovor_summa.kod_dogovora = view_dogovory_nvs.kod_dogovora
        LEFT JOIN view_dogovor_summa_plat ON view_dogovor_summa_plat.kod_dogovora = view_dogovory_nvs.kod_dogovora 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_dogovor_data");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_dogovory_elem";
    $db->query($sql);

// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_dogovory_elem AS 
        SELECT
            view_dogovory_nvs.kod_dogovora,
            view_dogovory_nvs.nomer,
            view_dogovory_nvs.data_sost,
            view_dogovory_nvs.kod_org,
            view_dogovory_nvs.nazv_krat,
            view_dogovory_nvs.zakryt,
            view_dogovory_nvs.kod_ispolnit,
            view_dogovory_nvs.ispolnit_nazv_krat,
            parts.kod_elem
        FROM
            parts
        INNER JOIN view_dogovory_nvs ON view_dogovory_nvs.kod_dogovora = parts.kod_dogovora 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_dogovory_elem");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_sklad_otgruzka";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_sklad_otgruzka AS 
        SELECT
            sklad.kod_part,
            sklad.numb,
            sklad.kod_oper
        FROM
            sklad
        WHERE
            sklad.kod_oper = 2 
                ";
    $db->query($sql);
    $db->query("SELECT * FROM view_sklad_otgruzka");
    if($db->cnt==0)
        echo $db->last_query;
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_sklad_summ_otgruz";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "create view view_sklad_summ_otgruz as 
            SELECT
                `view_sklad_otgruzka`.`kod_part`  AS `kod_part`,
                sum(`view_sklad_otgruzka`.`numb`) AS `summ_otgruz`
              FROM `trin`.`view_sklad_otgruzka`
              GROUP BY `view_sklad_otgruzka`.`kod_part`;";
    $db->query($sql);
    $db->query("SELECT * FROM view_sklad_summ_otgruz");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_sklad_postuplenie";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_sklad_postuplenie AS 
        SELECT
            sklad.kod_part,
            sklad.numb,
            sklad.kod_oper
        FROM
            sklad
        WHERE
            sklad.kod_oper = 1
                ";
    $db->query($sql);
    $db->query("SELECT * FROM view_sklad_postuplenie");
    if($db->cnt==0)
        echo $db->last_query;
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_rplan";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "
            CREATE 
            VIEW view_rplan AS
            SELECT
            dogovory.kod_dogovora,
            dogovory.nomer,
            org.kod_org,
            org.nazv_krat,
            parts.modif,
            parts.numb,
            parts.data_postav,
            ROUND(parts.nds, 2) AS nds,
            ROUND(
                IFNULL(
                    parts.numb * parts.price * (1 + parts.nds),
                    0
                ),
                2
            ) AS part_summa,
            parts.val,
            parts.price,
            elem.kod_elem,
            elem.obozn,
            parts.kod_part,
            dogovory.zakryt,
            dogovory.kod_ispolnit,
            elem.`name`,
            ispolnit.nazv_krat AS ispolnit_nazv_krat,
            IFNULL(
                view_sklad_summ_otgruz.summ_otgruz,
                0
            ) AS numb_otgruz,
            (
                parts.numb - IFNULL(
                    view_sklad_summ_otgruz.summ_otgruz,
                    0
                )
            ) AS numb_ostat
            FROM
                dogovory
            INNER JOIN parts ON dogovory.kod_dogovora = parts.kod_dogovora
            INNER JOIN org ON org.kod_org = dogovory.kod_org
            INNER JOIN elem ON elem.kod_elem = parts.kod_elem
            INNER JOIN org AS ispolnit ON ispolnit.kod_org = dogovory.kod_ispolnit
            LEFT JOIN view_sklad_summ_otgruz ON parts.kod_part = view_sklad_summ_otgruz.kod_part
            ORDER BY
                dogovory.kod_dogovora DESC  
            ";
    $db->query($sql);
    $db->query("SELECT * FROM view_rplan");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_elem";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_elem AS 
        SELECT
            elem.kod_elem,
            elem.obozn,
            elem.`name`,
            elem.shablon,
            elem.nomen,
            elem.shifr,
            elem.time_stamp
        FROM
            elem
        ORDER BY
            elem.obozn ASC,
            elem.nomen DESC 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_elem");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_elem_org";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_elem_org AS 
        SELECT
            view_rplan.kod_org,
            view_rplan.kod_elem,
            view_rplan.nazv_krat,
            Sum(view_rplan.numb) AS numb
        FROM
            view_dogovor_summa_plat
        INNER JOIN view_rplan ON view_dogovor_summa_plat.kod_dogovora = view_rplan.kod_dogovora
        GROUP BY
            view_rplan.kod_org,
            view_rplan.kod_elem,
            view_rplan.nazv_krat
        ORDER BY
            numb DESC
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_elem_org");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_kontakty_dogovora";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_kontakty_dogovora AS 
        SELECT
            kontakty.kod_kontakta,
            kontakty.kod_org,
            kontakty.dolg,
            kontakty.famil,
            kontakty.`name`,
            kontakty.otch,
            kontakty_dogovora.kod_dogovora,
            org.nazv_krat
        FROM
            kontakty
        INNER JOIN kontakty_dogovora ON kontakty.kod_kontakta = kontakty_dogovora.kod_kontakta
        INNER JOIN org ON kontakty.kod_org = org.kod_org 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_kontakty_dogovora");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_org_nomen";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_org_nomen AS 
        SELECT
            view_rplan.kod_org,
            view_rplan.kod_elem,
            Sum(view_rplan.numb) AS numb,
            view_rplan.`name`
        FROM
            view_rplan
        GROUP BY
            view_rplan.kod_org,
            view_rplan.kod_elem,
            view_rplan.`name`
        ORDER BY
        numb DESC 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_org_nomen");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_phones_kontakts";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_phones_kontakts AS 
        SELECT
            kontakty.kod_kontakta,
            kontakty.dolg,
            kontakty.famil,
            kontakty.`name`,
            kontakty.otch,
            kontakty_data.data
        FROM
            kontakty
        INNER JOIN kontakty_data ON kontakty.kod_kontakta = kontakty_data.kod_kontakta
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_phones_kontakts");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_plat";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_plat AS 
        SELECT
            plat.nomer,
            plat.summa,
            plat.`data`,
            plat.prim,
            view_dogovory_nvs.kod_dogovora,
            view_dogovory_nvs.nomer AS nomer_dogovora,
            view_dogovory_nvs.kod_org,
            view_dogovory_nvs.nazv_krat,
            view_plat_raspred.summa_raspred,
            plat.kod_plat
        FROM
            plat
        INNER JOIN view_dogovory_nvs ON plat.kod_dogovora = view_dogovory_nvs.kod_dogovora
        LEFT JOIN view_plat_raspred ON plat.kod_plat = view_plat_raspred.kod_plat 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_plat");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_plat_raspred";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_plat_raspred AS 
        SELECT
            plat.kod_plat,
            SUM(raschety_plat.summa) AS summa_raspred
        FROM
            raschety_plat
        INNER JOIN plat ON plat.kod_plat = raschety_plat.kod_plat
        GROUP BY
            plat.kod_plat 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_plat_raspred");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_raschety_plat";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_raschety_plat AS 
        SELECT
            raschet.kod_rascheta,
            raschet.kod_part,
            raschet.summa AS raschet_summa,
            raschet.`data` AS data_rascheta,
            raschet.type_rascheta,
            raschety_plat.kod_plat,
            raschety_plat.summa AS summa_raspred,
            plat.nomer,
            plat.`data` AS data_plat,
            plat.prim,
            plat.kod_dogovora
        FROM
            raschet
        INNER JOIN raschety_plat ON raschet.kod_rascheta = raschety_plat.kod_rascheta
        INNER JOIN plat ON raschety_plat.kod_plat = plat.kod_plat
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_raschety_plat");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_raschety_summ_plat";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_raschety_summ_plat AS 
        SELECT
            raschet.kod_rascheta,
            IFNULL(raschety_plat.summa, 0) AS summa_plat,
            raschet.kod_part,
            raschet.summa
        FROM
            raschet
        LEFT JOIN raschety_plat ON raschety_plat.kod_rascheta = raschet.kod_rascheta
        GROUP BY
            raschet.kod_rascheta 
        ";
    $db->query($sql);
    $db->query("SELECT * FROM view_raschety_summ_plat");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_scheta_dogovora";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_scheta_dogovora AS 
        SELECT
            scheta.nomer,
            scheta.`data`,
            view_dogovory_nvs.kod_ispolnit,
            view_dogovory_nvs.ispolnit_nazv_krat,
            view_dogovory_nvs.kod_org,
            view_dogovory_nvs.data_sost,
            view_dogovory_nvs.kod_dogovora,
            view_dogovory_nvs.nazv_krat
        FROM
          view_dogovory_nvs
        INNER JOIN scheta ON view_dogovory_nvs.kod_dogovora = scheta.kod_dogovora 
                ";
    $db->query($sql);
    $db->query("SELECT * FROM view_scheta_dogovora");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_scheta_dogovory_all";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_scheta_dogovory_all AS 
        SELECT
            kod_org,
            kod_dogovora,
            nomer,
            kod_ispolnit,
            ispolnit_nazv_krat,
            data_sost,
            nazv_krat
        FROM
            view_dogovory_nvs
        UNION ALL
            SELECT
                kod_org,
                kod_dogovora,
                nomer,
                kod_ispolnit,
                ispolnit_nazv_krat,
                DATA,
                nazv_krat
            FROM
                view_scheta_dogovora
            ORDER BY
                data_sost DESC 
                ";
    $db->query($sql);
    $db->query("SELECT * FROM view_scheta_dogovory_all");
    if($db->cnt==0)
        echo $db->last_query;
//
//----------------------------------------------------------------------------------------------------------------------
//
//drop view
    $sql = /** @lang SQL */
        "DROP VIEW IF EXISTS view_sklad";
    $db->query($sql);
// sql to create view
    $sql = /** @lang SQL */
        "CREATE 
        VIEW view_sklad AS 
        SELECT
            sklad.kod_part,
            sklad.numb,
            elem.`name`,
            dogovory.kod_dogovora,
            dogovory.nomer,
            elem.kod_elem,
            sklad.naklad,
            org.kod_org,
            org.nazv_krat,
            sklad.oper,
            sklad.kod_oper,
            view_dogovor_summa.dogovor_summa,
            view_dogovor_summa_plat.summa_plat,
            sklad.`data`,
            sklad.kod_oborota,
            sklad.poluch
        FROM
            sklad
        INNER JOIN parts ON sklad.kod_part = parts.kod_part
        INNER JOIN elem ON parts.kod_elem = elem.kod_elem
        INNER JOIN dogovory ON parts.kod_dogovora = dogovory.kod_dogovora
        INNER JOIN org ON dogovory.kod_org = org.kod_org
        LEFT JOIN view_dogovor_summa ON dogovory.kod_dogovora = view_dogovor_summa.kod_dogovora
        AND dogovory.kod_dogovora = view_dogovor_summa.kod_dogovora
        LEFT JOIN view_dogovor_summa_plat ON dogovory.kod_dogovora = view_dogovor_summa_plat.kod_dogovora
        WHERE
            sklad.kod_oper = 2
        ORDER BY
            sklad.`data` DESC
                ";
    $db->query($sql);
    $db->query("SELECT * FROM view_sklad");
    if($db->cnt==0)
        echo $db->last_query;
//

}
?>
</html>



