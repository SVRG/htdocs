<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>ODBC-MySQL</title>
</head>

<?php
include_once "class_db.php";
include_once "classodbc.php";
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 21.09.16
 * Time: 19:57
 * �����! - ��� ������� ��������� ���� INT! ���� ��� ������ �� ������ �� ���������
 */

    $db = new DB();
    $odbc = new ODBC();
    ini_set('max_execution_time', 300); // ��������� ������� ����-���� �� ��������� ������

//----------------------------------------------------------------------------------------------------------------------
    // sql drop table
    $sql = "DROP TABLE adresa";
    $db->query($sql);

    // sql to create table
    $sql = "CREATE TABLE adresa (
    kod_adresa INT(6),
    adres TEXT,
    kod_org INT,
    type INT,
    time_mark TIMESTAMP
    )";

    $db->query($sql);

    // ��������� ������ �� ODBC
    $odbc->sql = "SELECT * FROM ������";
    $odbc->ex();

    $i=1;

    // �������� ������ � MySQL
    for ($i; $i <= $odbc->cnt; $i++)
    {
        $row = $odbc->Row($i);

        $kod_adresa = $row['���_������'];
        $adres = $row['�����'];
        $kod_org = $row['���_�����������'];
        $type = $row['���������'];

        $sql = "INSERT INTO adresa (kod_adresa,adres,kod_org,type) VALUES($kod_adresa,'$adres',$kod_org,$type)";
        $db->query($sql);
        //echo $sql.'<br>';
    }

    $sql = "ALTER TABLE adresa MODIFY kod_adresa INT AUTO_INCREMENT PRIMARY KEY";
    $db->query($sql);

    echo "adresa Inserted: ".$i;

//----------------------------------------------------------------------------------------------------------------------
//

$table = "docum";
$table_odbc = "���������";

// Sourse Names                | Dest Names                 | Dest Type
$id_odbc="���_���������";       $id = "kod_docum";           $id_type = "INT";
$f1_odbc="������������";        $f1 = "name";                $f1_type = "VARCHAR(255)";
$f2_odbc="����";                $f2 = "path";                $f2_type = "VARCHAR(255)";
$f3_odbc="Date_CP";             $f3 = "time_stamp";           $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
$db->query($sql);

// sql to create table
$sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
$db->query($sql);

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];

    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,'$field1','$field2','$field3')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "docum_dogovory";
$table_odbc = "�����������������";

// Sourse Names                | Dest Names                 | Dest Type
$id_odbc="���_�����";           $id = "kod_docum_dog";       $id_type = "INT";
$f1_odbc="���_���������";       $f1 = "kod_docum";           $f1_type = "INT";
$f2_odbc="���_��������";        $f2 = "kod_dogovora";        $f2_type = "INT";
$f3_odbc="DateCP";              $f3 = "time_stamp";           $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
$db->query($sql);

// sql to create table
$sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
$db->query($sql);

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];

    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,$field2,'$field3')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "dogovor_prim";
$table_odbc = "������������������";

// Sourse Names                | Dest Names                 | Dest Type
$id_odbc="���_����������";      $id = "kod_prim";            $id_type = "INT";
$f1_odbc="�����";               $f1 = "text";                $f1_type = "TEXT";
$f2_odbc="���_��������";        $f2 = "kod_dogovora";        $f2_type = "INT";
$f3_odbc="user";                $f3 = "user";                $f3_type = "VARCHAR(20)";
$f4_odbc="����";                $f4 = "data";                $f4_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];
    $field4 = $row[$f4_odbc];

    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4) VALUES($field_id,'$field1',$field2,'$field3','$field4')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";


//----------------------------------------------------------------------------------------------------------------------
//

$table = "dogovory";
$table_odbc = "��������";

// Sourse Names                | Dest Names                 | Dest Type
$id_odbc="���_��������";        $id = "kod_dogovora";         $id_type = "INT";
$f1_odbc="�����";               $f1 = "nomer";                $f1_type = "VARCHAR(255)";
$f2_odbc="����_�����������";    $f2 = "data_sost";            $f2_type = "DATE";
$f3_odbc="������";              $f3 = "zakryt";               $f3_type = "INT";
$f4_odbc="����_��������";       $f4 = "data_zakrytiya";       $f4_type = "DATE";
$f5_odbc="���_�����������";     $f5 = "kod_org";              $f5_type = "INT";
$f6_odbc="���_�����������";     $f6 = "kod_ispolnit";         $f6_type = "INT";
$f7_odbc="���_���������������"; $f7 = "kod_gruzopoluchat";    $f7_type = "INT";
$f8_odbc="DateCP";              $f8 = "time_stamp";            $f8_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
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

    if($field7=="") // ���� �� ����� ��� ��������������� ����������� ��� ���������
        $field7=$field5;

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8) VALUES($field_id,'$field1','$field2',$field3,'$field4',$field5,$field6,$field7,'$field8')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "elem";
$table_odbc = "������������_�������";

// Sourse Names                | Dest Names                 | Dest Type
$id_odbc="���_��������";        $id = "kod_elem";             $id_type = "INT";
$f1_odbc="�����������";         $f1 = "obozn";                $f1_type = "VARCHAR(255)";
$f2_odbc="������������";        $f2 = "name";                 $f2_type = "VARCHAR(255)";
$f3_odbc="������";              $f3 = "shablon";              $f3_type = "VARCHAR(255)";
$f4_odbc="NOMEN";               $f4 = "nomen";                $f4_type = "INT";
$f5_odbc="����";                $f5 = "shifr";                $f5_type = "VARCHAR(255)";
$f6_odbc="Date_CP";             $f6 = "time_stamp";            $f6_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";

$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];
    $field4 = $row[$f4_odbc];
    $field5 = $row[$f5_odbc];
    $field6 = $row[$f6_odbc];

    if($field4=="") // ���� �� ����� ��� ������������
        $field4=0;

    if($field5!="") // ���� ���� ����� �� �������� �����������?
        $field1 = $field5;

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6) VALUES($field_id,'$field1','$field2','$field3',$field4,'$field5','$field6')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "kontakty";
$table_odbc = "��������";

// Sourse Names                | Dest Names                 | Dest Type
$id_odbc="���_��������";        $id = "kod_kontakta";           $id_type = "INT";
$f1_odbc="���_�����������";     $f1 = "kod_org";                $f1_type = "INT";
$f2_odbc="���������";           $f2 = "dolg";                   $f2_type = "VARCHAR(255)";
$f3_odbc="�������";             $f3 = "famil";                  $f3_type = "VARCHAR(255)";
$f4_odbc="���";                 $f4 = "name";                   $f4_type = "VARCHAR(255)";
$f5_odbc="��������";            $f5 = "otch";                   $f5_type = "VARCHAR(255)";
$f6_odbc="Date_CP";             $f6 = "time_stamp";              $f6_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];
    $field4 = $row[$f4_odbc];
    $field5 = $row[$f5_odbc];
    $field6 = $row[$f6_odbc];

    if($field1=="") // ���� �� ����� ��� ������������
        $field1=0;

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6) VALUES($field_id,$field1,'$field2','$field3','$field4','$field5','$field6')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "kontakty_dogovora";
$table_odbc = "��������������";

// Sourse Names                | Dest Names                 | Dest Type
$id_odbc="������������������";  $id = "kod_kont_dog";         $id_type = "INT";
$f1_odbc="���_��������";        $f1 = "kod_kontakta";         $f1_type = "INT";
$f2_odbc="���_��������";        $f2 = "kod_dogovora";         $f2_type = "INT";
$f3_odbc="DateCP";              $f3 = "time_stamp";            $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
$db->query($sql);

// sql to create table
$sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
$db->query($sql);

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];

    if($field1=="") // ���� �� ����� ��� ������������
        $field1=0;

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,$field2,'$field3')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "org";
$table_odbc = "�����������";

// Sourse Names                | Dest Names                   | Dest Type
$id_odbc="���_�����������";     $id = "kod_org";                $id_type = "INT";
$f1_odbc="�����";               $f1 = "poisk";                  $f1_type = "VARCHAR(255)";
$f2_odbc="��������_����";       $f2 = "nazv_krat";              $f2_type = "VARCHAR(255)";
$f3_odbc="��������_����";       $f3 = "nazv_poln";              $f3_type = "VARCHAR(255)";
$f4_odbc="���";                 $f4 = "inn";                    $f4_type = "VARCHAR(255)";
$f5_odbc="���";                 $f5 = "kpp";                    $f5_type = "VARCHAR(255)";
$f6_odbc="�_��";                $f6 = "r_sch";                  $f6_type = "VARCHAR(255)";
$f7_odbc="������";              $f7 = "bank_rs";                $f7_type = "VARCHAR(255)";
$f8_odbc="�_��";                $f8 = "k_sch";                  $f8_type = "VARCHAR(255)";
$f9_odbc="������";              $f9 = "bank_ks";                $f9_type = "VARCHAR(255)";
$f10_odbc="���";                $f10 = "bik";                   $f10_type = "VARCHAR(255)";
$f11_odbc="����";               $f11 = "okpo";                  $f11_type = "VARCHAR(255)";
$f12_odbc="�����";              $f12 = "okonh";                 $f12_type = "VARCHAR(255)";
$f13_odbc="e_mail";             $f13 = "e_mail";                $f13_type = "VARCHAR(255)";
$f14_odbc="www";                $f14 = "www";                   $f14_type = "VARCHAR(255)";
$f15_odbc="Date_CP";            $f15 = "time_stamp";             $f15_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
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

    if($field7=="") // ���� �� ����� ��� ��������������� ����������� ��� ���������
        $field7=$field5;

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$f9,$f10,$f11,$f12,$f13,$f14,$f15) 
            VALUES($field_id,'$field1','$field2','$field3','$field4','$field5','$field6','$field7',
            '$field8','$field9','$field10','$field11','$field12','$field13','$field14','$field15')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "parts";
$table_odbc = "������";

// Sourse Names                | Dest Names                   | Dest Type
$id_odbc="���_������";          $id = "kod_part";               $id_type = "INT";
$f1_odbc="���_��������";        $f1 = "kod_elem";               $f1_type = "INT";
$f2_odbc="Mod";                 $f2 = "modif";                  $f2_type = "VARCHAR(255)";
$f3_odbc="����������";          $f3 = "numb";                   $f3_type = "DOUBLE";
$f4_odbc="����_��������";       $f4 = "data_postav";            $f4_type = "DATE";
$f5_odbc="����_��";             $f5 = "price";                  $f5_type = "DOUBLE";
$f6_odbc="���_��������";        $f6 = "kod_dogovora";           $f6_type = "INT";
$f7_odbc="������";              $f7 = "val";                    $f7_type = "INT";
$f8_odbc="���";                 $f8 = "nds";                    $f8_type = "DOUBLE";
$f9_odbc="DateCP";              $f9 = "time_stamp";              $f9_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
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

    if($field7=="") // ���� �� ����� ��� ��������������� ����������� ��� ���������
        $field7=$field5;

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$f9) 
            VALUES($field_id,$field1,'$field2',$field3,'$field4',$field5,$field6,$field7,$field8,'$field9')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

// !��������� �� ��� ������� �� ��������� � �� �����������
$table = "kontakty_data";
$table2 = "org_data";
$table_odbc = "��������";

// Sourse Names                | Dest Names                 | Dest Type
$id_odbc="��� ��������";        $id = "kod_dat";              $id_type = "INT";
$f1_odbc="���_��������";        $f1 = "kod_kontakta";         $f1_type = "INT";
$f2_odbc="�������";             $f2 = "data";                 $f2_type = "VARCHAR(255)";
$f3_odbc="Date_CP";             $f3 = "time_stamp";            $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
$f4_odbc="���_�����������";     $f4 = "kod_org";              $f4_type = "INT";


$sql = "DROP TABLE $table";
$db->query($sql);
$sql = "DROP TABLE $table2";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];
    $field4 = $row[$f4_odbc];

    if($field1!="") {
        $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,'$field2','$field3')";
        $db->query($sql);
        // ��������� ���������� �� ������
        $db->query("SELECT * FROM $table WHERE $id=$field_id");
        if($db->cnt!=1)
            echo "!!!!!!!!! Err: ".$sql;
    }
    elseif($field4!="")
    {
        $sql = "INSERT INTO $table2 ($id,$f4,$f2,$f3) VALUES($field_id,$field4,'$field2','$field3')";
        $db->query($sql);
        // ��������� ���������� �� ������
        $db->query("SELECT * FROM $table2 WHERE $id=$field_id");
        if($db->cnt!=1)
            echo "!!!!!!!!! Err: ".$sql;
    }

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

$sql = "ALTER TABLE $table2 MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "plat";
$table_odbc = "�������";

// Sourse Names                | Dest Names                   | Dest Type
$id_odbc="���_�������";         $id = "kod_plat";               $id_type = "INT";
$f1_odbc="�����_��";            $f1 = "nomer";                  $f1_type = "VARCHAR(255)";
$f2_odbc="�����";               $f2 = "summa";                  $f2_type = "DOUBLE";
$f3_odbc="����";                $f3 = "data";                   $f3_type = "DATE";
$f4_odbc="����������";          $f4 = "prim";                   $f4_type = "VARCHAR(255)";
$f5_odbc="���_��������";        $f5 = "kod_dogovora";           $f5_type = "INT";
$f6_odbc="Date_CP";             $f6 = "time_stamp";              $f6_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
$f7_odbc="";                    $f7 = "user";                   $f7_type = "VARCHAR(255)";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];
    $field4 = $row[$f4_odbc];
    $field5 = $row[$f5_odbc];
    $field6 = $row[$f6_odbc];
    $field7 = $row[$f7_odbc];

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7) 
            VALUES($field_id,'$field1',$field2,'$field3','$field4',$field5,'$field6','$field7')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "raschet";
$table_odbc = "������";

// Sourse Names                | Dest Names                   | Dest Type
$id_odbc="���_�������";         $id = "kod_rascheta";           $id_type = "INT";
$f1_odbc="���_������";          $f1 = "kod_part";               $f1_type = "INT";
$f2_odbc="�����";               $f2 = "summa";                  $f2_type = "DOUBLE";
$f3_odbc="����";                $f3 = "data";                   $f3_type = "DATE";
$f4_odbc="���_�������";         $f4 = "type_rascheta";          $f4_type = "INT";
$f5_odbc="";                    $f5 = "time_stamp";              $f5_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
$f6_odbc="";                    $f6 = "user";                   $f6_type = "VARCHAR(255)";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];
    $field4 = $row[$f4_odbc];
    $field5 = $row[$f5_odbc];
    $field6 = $row[$f6_odbc];

    if($field4=="") // ���� �� ����� ��� ��������������� ����������� ��� ���������
        $field4=1;

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6) 
            VALUES($field_id,$field1,$field2,'$field3',$field4,'$field5','$field6')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "raschety_plat";
$table_odbc = "�������_�������";

// Sourse Names                | Dest Names                   | Dest Type
$id_odbc="���_�����������";     $id = "kod_rasch_plat";         $id_type = "INT";
$f1_odbc="�����";               $f1 = "summa";                  $f1_type = "DOUBLE";
$f2_odbc="���_�������";         $f2 = "kod_rascheta";           $f2_type = "INT";
$f3_odbc="���_�������";         $f3 = "kod_plat";               $f3_type = "INT";
$f4_odbc="";                    $f4 = "time_stamp";              $f4_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
$f5_odbc="";                    $f5 = "user";                   $f5_type = "VARCHAR(255)";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) 
            VALUES($field_id,$field1,$field2,$field3)";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "scheta";
$table_odbc = "�����";

// Sourse Names                | Dest Names                   | Dest Type
$id_odbc="���_�����";           $id = "kod_scheta";             $id_type = "INT";
$f1_odbc="�����";               $f1 = "nomer";                  $f1_type = "VARCHAR(255)";
$f2_odbc="����";                $f2 = "data";                   $f2_type = "DATE";
$f3_odbc="�����";               $f3 = "summa";                  $f3_type = "DOUBLE";
$f4_odbc="����������";          $f4 = "prim";                   $f4_type = "VARCHAR(255)";
$f5_odbc="���_��������";        $f5 = "kod_dogovora";           $f5_type = "INT";
$f6_odbc="Date_CP";             $f6 = "time_stamp";              $f6_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];
    $field4 = $row[$f4_odbc];
    $field5 = $row[$f5_odbc];
    $field6 = $row[$f6_odbc];

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6) 
            VALUES($field_id,'$field1','$field2',$field3,'$field4',$field5,'$field6')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "sklad";
$table_odbc = "�����";

// Sourse Names                   | Dest Names                   | Dest Type
$id_odbc="���_�������";             $id = "kod_oborota";            $id_type = "INT";
$f1_odbc="���_������";              $f1 = "kod_part";               $f1_type = "INT";
$f2_odbc="����������";              $f2 = "numb";                   $f2_type = "INT";
$f3_odbc="���_��������";            $f3 = "kod_oper";               $f3_type = "INT";
$f4_odbc="���������";               $f4 = "naklad";                 $f4_type = "VARCHAR(255)";
$f5_odbc="����";                    $f5 = "data";                   $f5_type = "DATE";
$f6_odbc="Operator";                $f6 = "oper";                   $f6_type = "VARCHAR(255)";
$f7_odbc="��������";                $f7 = "poluch";                 $f7_type = "INT";
$f8_odbc="����_����������������";   $f8 = "data_poluch";            $f8_type = "DATE";
$f9_odbc="DateCP";                  $f9 = "time_stamp";              $f9_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
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
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4,$f5,$f6,$f7,$f8,$f9) 
            VALUES($field_id,$field1,$field2,$field3,'$field4','$field5','$field6',$field7,'$field8','$field9')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "org_links";
$table_odbc = "�����������_�����";

// Sourse Names                   | Dest Names                   | Dest Type
$id_odbc="���_�����";               $id = "kod_link";               $id_type = "INT";
$f1_odbc="Master";                  $f1 = "master";                 $f1_type = "INT";
$f2_odbc="Slave";                   $f2 = "slave";                  $f2_type = "INT";
$f3_odbc="���_�����";               $f3 = "prim";                   $f3_type = "VARCHAR(255)";
$f4_odbc="Date_CP";                 $f4 = "time_stamp";              $f4_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
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

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];
    $field4 = $row[$f4_odbc];

    // ���������� ������
    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3,$f4) 
            VALUES($field_id,$field1,$field2,'$field3','$field4')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql."<br>";

    //if($i<10)
    //    echo $sql.'<br>';
}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";

//----------------------------------------------------------------------------------------------------------------------
//

$table = "docum_elem";
$table_odbc = "����������������";

// Sourse Names                | Dest Names                   | Dest Type
$id_odbc="���_�����";           $id = "kod_docum_elem";         $id_type = "INT";
$f1_odbc="���_���������";       $f1 = "kod_docum";              $f1_type = "INT";
$f2_odbc="���_��������";        $f2 = "kod_elem";               $f2_type = "INT";
$f3_odbc="DateCP";              $f3 = "time_stamp";              $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
$db->query($sql);

// sql to create table
$sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
$db->query($sql);

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];

    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,$field2,'$field3')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";
//
//----------------------------------------------------------------------------------------------------------------------
//
$table = "docum_org";
$table_odbc = "��������������������";

// Sourse Names                | Dest Names                   | Dest Type
$id_odbc="���_�����";           $id = "kod_docum_org";          $id_type = "INT";
$f1_odbc="���_���������";       $f1 = "kod_docum";              $f1_type = "INT";
$f2_odbc="���_�����������";     $f2 = "kod_org";                $f2_type = "INT";
$f3_odbc="DateCP";              $f3 = "time_stamp";              $f3_type = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";


$sql = "DROP TABLE $table";
$db->query($sql);

// sql to create table
$sql = "CREATE TABLE $table (
    $id $id_type,
    $f1 $f1_type,
    $f2 $f2_type,
    $f3 $f3_type
    )";
$db->query($sql);

// ��������� ������ �� ODBC
$odbc->sql = "SELECT * FROM $table_odbc";
$odbc->ex();

// �������� ������ � MySQL
$i=1;
for ($i; $i <= $odbc->cnt; $i++)
{
    $row = $odbc->Row($i);

    $field_id = $row[$id_odbc];
    $field1 = $row[$f1_odbc];
    $field2 = $row[$f2_odbc];
    $field3 = $row[$f3_odbc];

    $sql = "INSERT INTO $table ($id,$f1,$f2,$f3) VALUES($field_id,$field1,$field2,'$field3')";
    $db->query($sql);

    // ��������� ���������� �� ������
    $db->query("SELECT * FROM $table WHERE $id=$field_id");
    if($db->cnt!=1)
        echo "!!!!!!!!! Err: ".$sql;

}

$sql = "ALTER TABLE $table MODIFY $id INT AUTO_INCREMENT PRIMARY KEY";
$db->query($sql);

echo "$table_odbc -> $table Inserted: $i";
?>
</html>
