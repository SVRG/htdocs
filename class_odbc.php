<?php
include_once('class_func.php');

class ODBC
{
    public $x;
    public $sql = '';
    public $cnt = 0;
    public $res;

//--------------------------------------------------------------
    public function __construct()
    {
        $this->x = odbc_connect("trin", "Admin", "master") or die(odbc_error());
    }

//--------------------------------------------------------------
    public function xodbc_num_rows($sql_id, $CurrRow = 0)
    {
        $NumRecords = 0;
        odbc_fetch_row($sql_id, 0);
        while (odbc_fetch_row($sql_id)) {
            $NumRecords++;
        }
        odbc_fetch_row($sql_id, $CurrRow);
        return $NumRecords;
    }

//--------------------------------------------------------------
    public function ex($Type = 's') // i-INSERT, s-SELECT
    {
        $this->sql = stripslashes($this->sql);

        if ($Type == 's') {
            $this->res = odbc_exec($this->x, $this->sql);
            $this->cnt = $this->xodbc_num_rows($this->res);
        } elseif ($Type == 'i') {
            odbc_exec($this->x, $this->sql);
        } elseif ($Type == 'p') {
            $this->res = odbc_execute($this->x, $this->sql);
            $this->cnt = $this->xodbc_num_rows($this->res);
        }
    }

//--------------------------------------------------------------
    public function Row($i)
    {
        $row = odbc_fetch_array($this->res, $i);
        return $row;
    }

}