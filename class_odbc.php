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
    public function xodbc_fetch_array($result, $rownumber = -1)
    {
        $rs_assoc = array();

        if ($rownumber < 0) {
            odbc_fetch_into($result, $rs);
        } else {
            odbc_fetch_into($result, $rs, $rownumber);
        }
        foreach ($rs as $key => $value) {
            $rs_assoc[odbc_field_name($result, $key + 1)] = $value;
        }
        return $rs_assoc;
    }

//--------------------------------------------------------------
    public function ex($Type = 's') // i-INSERT, s-SELECT
    {
        $this->sql = stripslashes($this->sql);

        //$this->sql = str_replace('"','»',$this->sql);


        if ($Type == 's') {
            //$xn = odbc_prepare($this->x, $this->sql);
            $this->res = odbc_exec($this->x, $this->sql);
            $this->cnt = $this->xodbc_num_rows($this->res);
            //odbc_close($this->x);
        } elseif ($Type == 'i') {
            odbc_exec($this->x, $this->sql);
            //odbc_close($this->x);
        } elseif ($Type == 'p') {
            $this->res = odbc_execute($this->x, $this->sql);
            $this->cnt = $this->xodbc_num_rows($this->res);
        }

//return $this->res;
    }

//--------------------------------------------------------------
    public function Row($i)
    {
        $row = odbc_fetch_array($this->res, $i);
        return $row;
    }

//--------------------------------------------------------------
    public function Close()
    {
        odbc_close($this->x);
    }
//--------------------------------------------------------------
//
    public function exINS()
    {
        try {
            $dbh = new PDO('odbc:trin, UID=Admin, PWD=master',
                array(PDO::ATTR_PERSISTENT => true));
            echo "Connected\n";
            //$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $dbh->beginTransaction();
            $dbh->exec($this->sql);
            $dbh->commit();

        } catch (Exception $e) {
            $dbh->rollBack();
            echo "Failed: " . $e->getMessage();
        }
    }

}