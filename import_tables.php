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
 * �����! - ��� ������� ��������� ���� INT! ���� ��� ������ �� ������ �� ���������
 * ��������� MySQL ������ ���� utf8
 * ��������� ������� ����� - windows-1251
 *
 *
    Old Server

    Stop mysql server
    Copy contents of datadir to another location on disk (~/mysqldata/*)
    Start mysql server again
    compress the data (tar -czvf mysqldata.tar.gz ~/mysqldata)
    copy the compressed file to new server

    New Server

    install mysql (don't start)
    unzip compressed file (tar -xzvf mysqldata.tar.gz)
    move contents of mysqldata to the datadir
    Make sure your innodb_log_file_size is same on new server, or if it's not, don't copy the old log files (mysql will generate these)
    Start mysql

 */

$db = new Db_import();
//$odbc = new ODBC(); // ������ �� ������ � ODBC, � ��������� ������ ����� �������� ������ ����������
ini_set('max_execution_time', 1000); // ��������� ������� ����-���� �� ��������� ������

$time_stamp = "time_stamp"; // ���� ��� �������� ������� ��������
$time_stamp_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
$time_stamp_str = "$time_stamp $time_stamp_type";

$footer_fields = "$time_stamp_str, del INT(2) DEFAULT 0, kod_user INT, edit INT DEFAULT 0";

$drop               = true; // �������� ������
$users              = 0;
$sessions           = 0;
$log                = 0;
$adresa             = 0;
$docum              = 0;
$docum_dogovory     = 0;
$dogovor_prim       = 0;
$dogovory           = 0;
$elem               = 0;
$kontakty           = 0;
$kontakty_dogovora  = 0;
$org                = 0;
$parts              = 0;
$kontakty_data      = 0;
$plat               = 0;
$raschet            = 0;
$raschety_plat      = 0;
$scheta             = 0;
$sklad              = 0;
$org_links          = 0;
$docum_elem         = 0;
$docum_org          = 0;
$view               = 1;
//----------------------------------------------------------------------------------------------------------------------

/**
 * @return bool
 */
function insert()
{
    global $db;
    global $sql;
    global $table;
    global $id;
    global $field_id;

    $db->query($sql);

    // ��������� ���������� �� ������
    if ($db->cnt("SELECT * FROM $table WHERE $id=$field_id") == 0)
        echo "<br>$table Err: $sql";

    return false;
}
//----------------------------------------------------------------------------------------------------------------------
/**
 *
 */
function odbc_select()
{
    global $odbc;
    global $table_odbc;
    global $id_odbc;
    global $drop;
    global $db;
    global $table;
    global $id;

    if($drop)
        $odbc->sql = "SELECT * FROM $table_odbc ORDER BY $id_odbc ASC";
    else
    {
        $rows_last = $db->rows("SELECT MAX($id) AS last FROM $table;");
        $last = $rows_last[0]['last'];
        $odbc->sql = "SELECT * FROM $table_odbc WHERE $id_odbc>$last ORDER BY $id_odbc ASC";
    }
    $odbc->ex();
}
//----------------------------------------------------------------------------------------------------------------------
function mysql_report()
{
    global $db;
    global $table_odbc;
    global $i;
    global $table;
    global $id;

    $sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "<br>$table_odbc -> $table Inserted: $i";
}
//----------------------------------------------------------------------------------------------------------------------
function drop()
{
    global $drop;
    global $table;
    global $db;

    if($drop)
    {
        $sql = "DROP TABLE IF EXISTS $table";
        $db->query($sql);
    }
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($users == 1) {
    // sql to create table
    if($drop)
    {
        $sql = /** @lang SQL */
            "DROP TABLE IF EXISTS trin.users;";
        $db->query($sql);

        $sql=     "CREATE TABLE users (
                                      kod_user INT(11) NOT NULL AUTO_INCREMENT,
                                      login VARCHAR(20) NOT NULL,
                                      password VARCHAR(80) DEFAULT '',
                                      famil VARCHAR(40) DEFAULT '',
                                      rt VARCHAR(40) DEFAULT 'oper',
                                      salt VARCHAR(40) DEFAULT '',
                                      PRIMARY KEY (kod_user)
                                 );
            ";
        $db->query($sql);

        $sql = /** @lang SQL */
            "INSERT INTO users VALUES(1, 'Tikhomirov', '', '��������� ������', 'admin',''),
                                     (2, 'Charykova', '', '�������� �������', 'oper',''),
                                     (3, 'Mityushin', '', '������� ������', 'oper',''),
                                     (4, 'Ukhova', '', '����� �������', 'oper',''),
                                     (5, 'Vasin', '', '����� ������', 'oper',''),
                                     (6, 'Morgunova', '', '��������� �����', 'oper','');";
        $db->query($sql);

        $db->query("SELECT * FROM users");
        if($db->cnt==0)
            echo $db->last_query;
    }
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($sessions == 1) {

    $table = "sessions";
    $id = "kod_ses";

    if($drop)
    {
        $sql = "DROP TABLE IF EXISTS trin.sessions;";
        $db->query($sql);

        $sql = "CREATE TABLE $table (
                                $id INT AUTO_INCREMENT PRIMARY KEY,
                                login VARCHAR(20) DEFAULT '',
                                $time_stamp_str,
                                ip VARCHAR(20)
                                );";
        $db->query($sql);
    }
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($log == 1) {

    $table = "log";
    $id = "kod_log";

    if($drop)
    {
        $sql = "DROP TABLE IF EXISTS trin.$table;";
        $db->query($sql);

        $sql = "CREATE TABLE $table (
                                $id INT AUTO_INCREMENT PRIMARY KEY,
                                log TEXT DEFAULT '',
                                $time_stamp_str,
                                user VARCHAR(20)
                                );";
        $db->query($sql);
    }
}
//----------------------------------------------------------------------------------------------------------------------
if ($adresa == 1) {
    $table = "adresa";
    $table_odbc = "������";

    $id = "kod_adresa";
    $id_odbc = "���_������";

    // sql drop table
    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE adresa (
                                    kod_adresa INT,
                                    adres TEXT,
                                    kod_org INT,
                                    type INT,
                                    $footer_fields
                                    );";

        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row['���_������'];
        $adres = $row['�����'];
        $kod_org = $row['���_�����������'];
        $type = $row['���������'];

        $sql = "INSERT INTO adresa (kod_adresa,adres,kod_org,type) VALUES($field_id,'$adres',$kod_org,$type)";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//

if ($docum == 1) {
    $table = "docum";
    $table_odbc = "���������";

    $id_odbc = "���_���������";
    $id = "kod_docum";
    $id_type = "INT";

    $f1_odbc = "������������";
    $f1 = "name";
    $f1_type = "VARCHAR(255)";

    $f2_odbc = "����";
    $f2 = "path";
    $f2_type = "VARCHAR(255)";

    $f3_odbc = "Date_CP";

    if($drop)
    {
        drop();

        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$time_stamp) VALUES($field_id,'$field1','$field2','$field3')";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($docum_dogovory == 1) {
    $table = "docum_dogovory";
    $table_odbc = "�����������������";

    $id_odbc = "���_�����";
    $id = "kod_docum_dog";
    $id_type = "INT";

    $f1_odbc = "���_���������";
    $f1 = "kod_docum";
    $f1_type = "INT";

    $f2_odbc = "���_��������";
    $f2 = "kod_dogovora";
    $f2_type = "INT";

    $f3_odbc = "DateCP";

    if ($drop) {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $footer_fields
                                    )";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();


    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$time_stamp) VALUES($field_id,$field1,$field2,'$field3')";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($dogovor_prim == 1) {
    $table = "dogovor_prim";
    $table_odbc = "������������������";

    $id_odbc = "���_����������";
    $id = "kod_prim";
    $id_type = "INT";

    $f1_odbc = "�����";
    $f1 = "text";
    $f1_type = "TEXT";

    $f2_odbc = "���_��������";
    $f2 = "kod_dogovora";
    $f2_type = "INT";

    $f3_odbc = "user";
    $f3 = "user";
    $f3_type = "VARCHAR(20)";

    $f4_odbc = "����";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $f3 $f3_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$time_stamp) VALUES($field_id,'$field1',$field2,'$field3','$field4');";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($dogovory == 1) {
    $table = "dogovory";
    $table_odbc = "��������";

    $id_odbc = "���_��������";
    $id = "kod_dogovora";
    $id_type = "INT";

    $f1_odbc = "�����";
    $f1 = "nomer";
    $f1_type = "VARCHAR(255)";

    $f2_odbc = "����_�����������";
    $f2 = "data_sost";
    $f2_type = "DATE";

    $f3_odbc = "������";
    $f3 = "zakryt";
    $f3_type = "INT DEFAULT 0";

    $f4_odbc = "����_��������";
    $f4 = "data_zakrytiya";
    $f4_type = "DATE";

    $f5_odbc = "���_�����������";
    $f5 = "kod_org";
    $f5_type = "INT";

    $f6_odbc = "���_�����������";
    $f6 = "kod_ispolnit";
    $f6_type = "INT";

    $f7_odbc = "���_���������������";
    $f7 = "kod_gruzopoluchat";
    $f7_type = "INT";

    $f8_odbc = "DateCP";


    if($drop)
    {
        drop();

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
                                    $footer_fields
                                    );";
            $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
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

        if ($field7 == "") // ���� �� ����� ��� ��������������� ����������� ��� ���������
            $field7 = $field5;

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$time_stamp) VALUES($field_id,'$field1','$field2',$field3,'$field4',$field5,$field6,$field7,'$field8');";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($elem == 1) {
    $table = "elem";
    $table_odbc = "������������_�������";

    $id_odbc = "���_��������";
    $id = "kod_elem";
    $id_type = "INT";

    $f1_odbc = "�����������";
    $f1 = "obozn";
    $f1_type = "VARCHAR(255)";

    $f2_odbc = "������������";
    $f2 = "name";
    $f2_type = "VARCHAR(255)";

    $f3_odbc = "������";
    $f3 = "shablon";
    $f3_type = "VARCHAR(255)";

    $f4_odbc = "NOMEN";
    $f4 = "nomen";
    $f4_type = "INT";

    $f5_odbc = "����";
    $f5 = "shifr";
    $f5_type = "VARCHAR(255)";

    $f6_odbc = "Date_CP";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $f3 $f3_type,
                                    $f4 $f4_type,
                                    $f5 $f5_type,
                                    $footer_fields

                                    );";
            $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

// �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];

        if ($field4 == "") // ���� �� ����� ��� ������������
            $field4 = 0;

        if ($field5 == "") // ���� ��� ����� �� ����������� �����������
            $field5 = $field1;

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$time_stamp) VALUES($field_id,'$field1','$field2','$field3',$field4,'$field5','$field6');";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($kontakty==1) {
    $table = "kontakty";
    $table_odbc = "��������";

    $id_odbc = "���_��������";
    $id = "kod_kontakta";
    $id_type = "INT";

    $f1_odbc = "���_�����������";
    $f1 = "kod_org";
    $f1_type = "INT";

    $f2_odbc = "���������";
    $f2 = "dolg";
    $f2_type = "VARCHAR(255)";

    $f3_odbc = "�������";
    $f3 = "famil";
    $f3_type = "VARCHAR(255)";

    $f4_odbc = "���";
    $f4 = "name";
    $f4_type = "VARCHAR(255)";

    $f5_odbc = "��������";
    $f5 = "otch";
    $f5_type = "VARCHAR(255)";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $f3 $f3_type,
                                    $f4 $f4_type,
                                    $f5 $f5_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5) VALUES($field_id,$field1,'$field2','$field3','$field4','$field5');";
        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//

if ($kontakty_dogovora == 1) {
    $table = "kontakty_dogovora";
    $table_odbc = "��������������";

    $id_odbc = "������������������";
    $id = "kod_kont_dog";
    $id_type = "INT";

    $f1_odbc = "���_��������";
    $f1 = "kod_kontakta";
    $f1_type = "INT";

    $f2_odbc = "���_��������";
    $f2 = "kod_dogovora";
    $f2_type = "INT";

    $f3_odbc = "DateCP";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$time_stamp) VALUES($field_id,$field1,$field2,'$field3');";
        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($org == 1) {
    $table = "org";

    $table_odbc = "�����������";

    $id_odbc = "���_�����������";
    $id = "kod_org";
    $id_type = "INT";

    $f1_odbc = "�����";
    $f1 = "poisk";
    $f1_type = "VARCHAR(255)";

    $f2_odbc = "��������_����";
    $f2 = "nazv_krat";
    $f2_type = "VARCHAR(255)";

    $f3_odbc = "��������_����";
    $f3 = "nazv_poln";
    $f3_type = "VARCHAR(255)";

    $f4_odbc = "���";
    $f4 = "inn";
    $f4_type = "VARCHAR(255)";

    $f5_odbc = "���";
    $f5 = "kpp";
    $f5_type = "VARCHAR(255)";

    $f6_odbc = "�_��";
    $f6 = "r_sch";
    $f6_type = "VARCHAR(255)";

    $f7_odbc = "������";
    $f7 = "bank_rs";
    $f7_type = "VARCHAR(255)";

    $f8_odbc = "�_��";
    $f8 = "k_sch";
    $f8_type = "VARCHAR(255)";

    $f9_odbc = "������";
    $f9 = "bank_ks";
    $f9_type = "VARCHAR(255)";

    $f10_odbc = "���";
    $f10 = "bik";
    $f10_type = "VARCHAR(255)";

    $f11_odbc = "����";
    $f11 = "okpo";
    $f11_type = "VARCHAR(255)";

    $f12_odbc = "�����";
    $f12 = "okonh";
    $f12_type = "VARCHAR(255)";

    $f13_odbc = "e_mail";
    $f13 = "e_mail";
    $f13_type = "VARCHAR(255)";

    $f14_odbc = "www";
    $f14 = "www";
    $f14_type = "VARCHAR(255)";

    $f15_odbc = "Date_CP";

    if($drop)
    {
        drop();
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
                                    $footer_fields
                                    );";
        $db->query($sql);
    }



    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
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

        if ($field7 == "") // ���� �� ����� ��� ��������������� ����������� ��� ���������
            $field7 = $field5;

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$f9,$f10,$f11,$f12,$f13,$f14,$time_stamp) 
            VALUES($field_id,'$field1','$field2','$field3','$field4','$field5','$field6','$field7',
            '$field8','$field9','$field10','$field11','$field12','$field13','$field14','$field15');";
        if(insert())
            break;
    }

    mysql_report();
}

//----------------------------------------------------------------------------------------------------------------------
//
if ($parts == 1) {
    $table = "parts";
    $table_odbc = "������";

    $id_odbc = "���_������";
    $id = "kod_part";
    $id_type = "INT";

    $f1_odbc = "���_��������";
    $f1 = "kod_elem";
    $f1_type = "INT";

    $f2_odbc = "Mod";
    $f2 = "modif";
    $f2_type = "VARCHAR(255)";

    $f3_odbc = "����������";
    $f3 = "numb";
    $f3_type = "DOUBLE";

    $f4_odbc = "����_��������";
    $f4 = "data_postav";
    $f4_type = "DATE";

    $f5_odbc = "����_��";
    $f5 = "price";
    $f5_type = "DOUBLE";

    $f6_odbc = "���_��������";
    $f6 = "kod_dogovora";
    $f6_type = "INT";

    $f7_odbc = "������";
    $f7 = "val";
    $f7_type = "INT";

    $f8_odbc = "���";
    $f8 = "nds";
    $f8_type = "DOUBLE";

    $f9_odbc = "DateCP";

    if($drop)
    {
        drop();
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
                                    $footer_fields
                                    );";
        $db->query($sql);
    }


    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
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

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$time_stamp) 
            VALUES($field_id,$field1,'$field2',$field3,'$field4',$field5,$field6,$field7,$field8,'$field9')";
        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($kontakty_data == 1) {
// !��������� �� ��� ������� �� ��������� � �� �����������
    $table = "kontakty_data";
    $table2 = "org_data";
    $table_odbc = "��������";

    $id_odbc = "���_��������";
    $id = "kod_dat";
    $id_type = "INT";

    $f1_odbc = "���_��������";
    $f1 = "kod_kontakta";
    $f1_type = "INT";

    $f2_odbc = "�������";
    $f2 = "data";
    $f2_type = "VARCHAR(255)";

    $f4_odbc = "���_�����������";
    $f4 = "kod_org";
    $f4_type = "INT";

    $f3_odbc = "Date_CP";

    if($drop)
    {
        $sql = "DROP TABLE IF EXISTS $table";
        $db->query($sql);
        $sql = "DROP TABLE IF EXISTS $table2";
        $db->query($sql);

        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $footer_fields     
                                    );";
        $db->query($sql);

        // sql to create table2
        $sql = "CREATE TABLE $table2 (
                                    $id $id_type,
                                    $f4 $f4_type,
                                    $f2 $f2_type,
                                    $footer_fields
                                    );";
        $db->query($sql);

    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];

        if ($field1 != "") {
            $sql = "INSERT INTO $table ($id,$f1,$f2,$time_stamp) VALUES($field_id,$field1,'$field2','$field3')";
            $db->query($sql);

            // ��������� ���������� �� ������
            if ($db->cnt("SELECT * FROM $table WHERE $id=$field_id") == 0)
                echo "<br>$table Err: $sql";

        }
        elseif ($field4 != "")
        {
            $sql = "INSERT INTO $table2 ($id,$f4,$f2,$time_stamp) VALUES($field_id,$field4,'$field2','$field3')";
            $db->query($sql);

            // ��������� ���������� �� ������
            if ($db->cnt("SELECT * FROM $table2 WHERE $id=$field_id") == 0)
                echo "<br>$table2 Err: $sql";
        }
    }

    $sql = "ALTER TABLE $table2 MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($plat == 1) {
    $table = "plat";

    $table_odbc = "�������";

    $id_odbc = "���_�������";
    $id = "kod_plat";
    $id_type = "INT";

    $f1_odbc = "�����_��";
    $f1 = "nomer";
    $f1_type = "VARCHAR(255)";

    $f2_odbc = "�����";
    $f2 = "summa";
    $f2_type = "DOUBLE";

    $f3_odbc = "����";
    $f3 = "data";
    $f3_type = "DATE";

    $f4_odbc = "����������";
    $f4 = "prim";
    $f4_type = "VARCHAR(255)";

    $f5_odbc = "���_��������";
    $f5 = "kod_dogovora";
    $f5_type = "INT";

    $f6 = "user";
    $f6_type = "VARCHAR(255)";

    $f7_odbc = "Date_CP";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $f3 $f3_type,
                                    $f4 $f4_type,
                                    $f5 $f5_type,
                                    $f6 $f6_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f7_odbc];

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6) 
            VALUES($field_id,'$field1',$field2,'$field3','$field4',$field5,'$field6');";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($raschet == 1) {
    $table = "raschet";
    $table_odbc = "������";

    $id_odbc = "���_�������";
    $id = "kod_rascheta";
    $id_type = "INT";

    $f1_odbc = "���_������";
    $f1 = "kod_part";
    $f1_type = "INT";

    $f2_odbc = "�����";
    $f2 = "summa";
    $f2_type = "DOUBLE";

    $f3_odbc = "����";
    $f3 = "data";
    $f3_type = "DATE";

    $f4_odbc = "���_�������";
    $f4 = "type_rascheta";
    $f4_type = "INT";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $f3 $f3_type,
                                    $f4 $f4_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4) 
                             VALUES($field_id,$field1,$field2,'$field3',$field4);";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($raschety_plat == 1) {

    $table = "raschety_plat";
    $table_odbc = "�������_�������";

    $id_odbc = "���_�����������";
    $id = "kod_rasch_plat";
    $id_type = "INT";

    $f1_odbc = "�����";
    $f1 = "summa";
    $f1_type = "DOUBLE";

    $f2_odbc = "���_�������";
    $f2 = "kod_rascheta";
    $f2_type = "INT";

    $f3_odbc = "���_�������";
    $f3 = "kod_plat";
    $f3_type = "INT";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $f3 $f3_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }


    // ��������� ������ �� ODBC
    odbc_select();

// �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) 
            VALUES($field_id,$field1,$field2,$field3);";

        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($scheta == 1) {
    $table = "scheta";
    $table_odbc = "�����";

    $id_odbc = "���_�����";
    $id = "kod_scheta";
    $id_type = "INT";

    $f1_odbc = "�����";
    $f1 = "nomer";
    $f1_type = "VARCHAR(255)";

    $f2_odbc = "����";
    $f2 = "data";
    $f2_type = "DATE";

    $f3_odbc = "�����";
    $f3 = "summa";
    $f3_type = "DOUBLE";

    $f4_odbc = "����������";
    $f4 = "prim";
    $f4_type = "VARCHAR(255)";

    $f5_odbc = "���_��������";
    $f5 = "kod_dogovora";
    $f5_type = "INT";

    $f6_odbc = "Date_CP";

    if($drop)
    {
        drop();

        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $f3 $f3_type,
                                    $f4 $f4_type,
                                    $f5 $f5_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }


    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];
        $field5 = $row[$f5_odbc];
        $field6 = $row[$f6_odbc];

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$time_stamp) 
            VALUES($field_id,'$field1','$field2',$field3,'$field4',$field5,'$field6');";
        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($sklad == 1) {
    $table = "sklad";
    $table_odbc = "�����";

    $id_odbc = "���_�������";
    $id = "kod_oborota";
    $id_type = "INT";

    $f1_odbc = "���_������";
    $f1 = "kod_part";
    $f1_type = "INT";

    $f2_odbc = "����������";
    $f2 = "numb";
    $f2_type = "INT";

    $f3_odbc = "���_��������";
    $f3 = "kod_oper";
    $f3_type = "INT";

    $f4_odbc = "���������";
    $f4 = "naklad";
    $f4_type = "VARCHAR(255)";

    $f5_odbc = "����";
    $f5 = "data";
    $f5_type = "DATE";

    $f6_odbc = "Operator";
    $f6 = "oper";
    $f6_type = "VARCHAR(255)";

    $f7_odbc = "��������";
    $f7 = "poluch";
    $f7_type = "INT";

    $f8_odbc = "����_����������������";
    $f8 = "data_poluch";
    $f8_type = "DATE";

    $f9_odbc = "DateCP";

    if($drop)
    {
        drop();
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
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
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

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$time_stamp) 
            VALUES($field_id,$field1,$field2,$field3,'$field4','$field5','$field6',$field7,'$field8','$field9');";
        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($org_links == 1) {
    $table = "org_links";
    $table_odbc = "�����������_�����";

    $id_odbc = "���_�����";
    $id = "kod_link";
    $id_type = "INT";

    $f1_odbc = "Master";
    $f1 = "master";
    $f1_type = "INT";

    $f2_odbc = "Slave";
    $f2 = "slave";
    $f2_type = "INT";

    $f3_odbc = "���_�����";
    $f3 = "prim";
    $f3_type = "VARCHAR(255)";

    $f4_odbc = "Date_CP";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $f3 $f3_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];
        $field4 = $row[$f4_odbc];

        // ���������� ������
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$time_stamp) 
            VALUES($field_id,$field1,$field2,'$field3','$field4');";
        if(insert())
            break;
    }

    mysql_report();
}
//----------------------------------------------------------------------------------------------------------------------
//
if ($docum_elem == 1) {
    $table = "docum_elem";
    $table_odbc = "����������������";

    $id_odbc = "���_�����";
    $id = "kod_docum_elem";
    $id_type = "INT";

    $f1_odbc = "���_���������";
    $f1 = "kod_docum";
    $f1_type = "INT";

    $f2_odbc = "���_��������";
    $f2 = "kod_elem";
    $f2_type = "INT";

    $f3_odbc = "DateCP";

    if($drop)
    {
        drop();
        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();

    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$time_stamp) VALUES($field_id,$field1,$field2,'$field3');";
        if(insert())
            break;
    }

    mysql_report();
}
//
//----------------------------------------------------------------------------------------------------------------------
//
if ($docum_org == 1) {
    $table = "docum_org";
    $table_odbc = "��������������������";

    $id_odbc = "���_�����";
    $id = "kod_docum_org";
    $id_type = "INT";

    $f1_odbc = "���_���������";
    $f1 = "kod_docum";
    $f1_type = "INT";

    $f2_odbc = "���_�����������";
    $f2 = "kod_org";
    $f2_type = "INT";

    $f3_odbc = "Date_CP";

    if($drop)
    {
        drop();

        // sql to create table
        $sql = "CREATE TABLE $table (
                                    $id $id_type,
                                    $f1 $f1_type,
                                    $f2 $f2_type,
                                    $footer_fields
                                    );";
        $db->query($sql);
    }

    // ��������� ������ �� ODBC
    odbc_select();


    // �������� ������ � MySQL
    for ($i = 1; $i <= $odbc->cnt; $i++) {
        $row = $odbc->Row($i);

        $field_id = $row[$id_odbc];
        $field1 = $row[$f1_odbc];
        $field2 = $row[$f2_odbc];
        $field3 = $row[$f3_odbc];

        $sql = "INSERT INTO $table ($id,$f1,$f2,$time_stamp) VALUES($field_id,$field1,$field2,'$field3')";

        if(insert())
            break;
    }

    mysql_report();
}
//
//----------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------
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
        WHERE docum_elem.del=0 AND docum.del=0
        ORDER BY docum_elem.kod_docum DESC
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
        WHERE dogovory.del=0
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
            Sum(round(parts.price*parts.numb*(1+parts.nds),2)) AS dogovor_summa
        FROM
            parts
        WHERE parts.del=0
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
        WHERE plat.del=0
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
        WHERE parts.del=0
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
            sklad.kod_oper = 2 AND sklad.del=0
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
            sklad.kod_oper = 1 AND sklad.del=0
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
            elem.shifr,
            parts.kod_part,
            IFNULL(dogovory.zakryt,0) AS zakryt,
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
            WHERE parts.del=0
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
        WHERE elem.del=0
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
            kontakty_dogovora.kod_kont_dog,
            org.nazv_krat
        FROM
            kontakty
        INNER JOIN kontakty_dogovora ON kontakty.kod_kontakta = kontakty_dogovora.kod_kontakta
        INNER JOIN org ON kontakty.kod_org = org.kod_org
        WHERE kontakty.del=0 AND kontakty_dogovora.del=0
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
        WHERE kontakty.del=0
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
        WHERE plat.del=0 AND raschety_plat.del=0
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
            plat.kod_plat,
            view_dogovory_nvs.kod_dogovora,
            view_dogovory_nvs.nomer AS nomer_dogovora,
            view_dogovory_nvs.kod_org,
            view_dogovory_nvs.nazv_krat,
            view_plat_raspred.summa_raspred
        FROM
            plat
        INNER JOIN view_dogovory_nvs ON plat.kod_dogovora = view_dogovory_nvs.kod_dogovora
        LEFT JOIN view_plat_raspred ON plat.kod_plat = view_plat_raspred.kod_plat
        WHERE plat.del=0
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
            WHERE raschet.del=0 AND plat.del=0
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
        WHERE raschet.del=0
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
        WHERE scheta.del=0
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
            sklad.kod_oper = 2 AND sklad.del=0
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



