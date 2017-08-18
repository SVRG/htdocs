<?php
include_once('class_db.php');

class Docum
{
    /**
     * Docum constructor.
     */
    public function __construct()
    {

    }
//---------------------------------------------------------------------
//
    /**
     * Документы договора/елемента/организации
     * @param string $Type
     * @param int $ID
     * @return string
     */
    public static function formDocum($Type = 'Doc', $ID = 1)
    {

        $sql = '';
        if ($Type == 'Elem')
            $sql = "SELECT
                                docum_elem.kod_docum,
                                docum_elem.kod_elem,
                                docum.`name`,
                                docum.path,
                                docum.time_stamp
                              FROM
                                docum_elem
                              INNER JOIN docum ON docum_elem.kod_docum = docum.kod_docum
                              WHERE kod_elem=$ID AND docum.del=0
                              ORDER BY docum.time_stamp DESC";

        elseif ($Type == 'Doc')
            $sql = "SELECT
                            docum_dogovory.kod_docum,
                            docum_dogovory.kod_dogovora,
                            docum.`name`,
                            docum.path,
                            docum.time_stamp
                          FROM
                            docum
                          INNER JOIN docum_dogovory ON docum_dogovory.kod_docum = docum.kod_docum
                          WHERE docum_dogovory.kod_dogovora=$ID AND docum.del=0
                          ORDER BY docum.time_stamp DESC";

        elseif ($Type == 'Org')
            $sql = "SELECT
                        docum_org.kod_docum,
                        docum_org.kod_org,
                        docum.`name`,
                        docum.path,
                        docum.time_stamp
                      FROM
                        docum_org
                      INNER JOIN docum ON docum_org.kod_docum = docum.kod_docum
                      WHERE kod_org=$ID AND docum.del=0
                      ORDER BY docum.time_stamp DESC";

        if ($sql == '')
            return "";

        $db = new DB();
        $rows = $db->rows($sql);

        $res = "";
        if ($Type == 'Doc')
            $res .= Func::ActButton('form_upload.php?Desc=IncludeToDoc&kod_dogovora=' . $ID, 'Прикрепить Файл');
        elseif ($Type == 'Elem')
            $res .= Func::ActButton('form_upload.php?Desc=IncludeToElem&kod_elem=' . $ID, 'Прикрепить Файл');
        elseif ($Type == 'Org')
            $res .= Func::ActButton('form_upload.php?Desc=IncludeToOrg&kod_org=' . $ID, 'Прикрепить Файл');
        elseif ($Type == 'Cont')
            $res .= Func::ActButton('form_upload.php?Desc=IncludeToCont&kod_kontakta=' . $ID, 'Прикрепить Файл');

        if ($db->cnt == 0)
            return $res;

        $res .= '<table>';

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            if (file_exists($row['path'])) {
                $name = $row['name'];
                $path = $row['path'];
                $date = "";
                if(isset($row['time_stamp'])) // todo - разобраться, если time_stamp = NULL выдает ошибку Notice: Undefined index: time_stamp
                    $date = func::Date_from_MySQL($row['time_stamp']);

                $del = Func::ActButton2('', "Удалить", 'DelDocum', "kod_docum_del", $row['kod_docum']);

                $res .= "<tr>
                            <td> <a href='$path' target='_blank'> $date </a></td>
                            <td> <a href='$path' target='_blank'> $name </a></td>
                            <td>$del</td>
                         </tr>";
            }
            else
                {
                    self::Delete($row['kod_docum']);
                }
        }

        $res .= '</table>';

        return $res;
    }

//-----------------------------------------------------------------------
//
    /**
     * Удаление файла и записи (документа)
     * @param int $kod_docum
     * @return void
     */
    static public function Delete($kod_docum)
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM docum WHERE kod_docum=$kod_docum AND docum.del=0");

        if ($db->cnt != 1)
            return;

        $row = $rows[0];

        $db->query("UPDATE docum SET del=1 WHERE kod_docum= $kod_docum");
        $db->query("UPDATE docum_dogovory SET del=1 WHERE kod_docum= $kod_docum");
        $db->query("UPDATE docum_elem SET del=1 WHERE kod_docum= $kod_docum");
        $db->query("UPDATE docum_org SET del=1 WHERE kod_docum= $kod_docum");

        if (!file_exists($row['path']))
            return;

        $path = realpath($_SERVER["DOCUMENT_ROOT"]) . '/' . $row['path'];

        unlink($path);
    }
//-----------------------------------------------------------------------
//
    /**
     * Добавление документв
     * @param string $name
     * @param string $path
     * @param int $ID
     * @param string $Dest
     */
    function Add($name, $path, $ID, $Dest = 'Doc')
    {
        $kod_user = func::kod_user();
        $db = new Db();
        $db->query("INSERT INTO docum (name,path,kod_user) VALUES('$name','$path',$kod_user)");
        $last_id = $db->last_id;

        if ($Dest == 'Doc') {
            $db->query("INSERT INTO docum_dogovory (kod_docum,kod_dogovora,kod_user) VALUES ($last_id,$ID,$kod_user)");
        } elseif ($Dest == 'Org') {
            $db->query("INSERT INTO docum_org (kod_docum,kod_org,kod_user) VALUES ($last_id,$ID,$kod_user)");
        } elseif ($Dest == 'Elem') {
            $db->query("INSERT INTO docum_elem (kod_docum,kod_elem,kod_user) VALUES ($last_id,$ID,$kod_user)");
        }
    }

    //-----------------------------------------------------------------------
//
    /**
     * Удаление файлов по коду Элемента
     * @param int $kod_elem
     * @return int|void
     * @internal param $kod_docum
     */
    public function DeleteElemFiles($kod_elem)
    {
        $db = new Db();

        $rows = $db->rows("  SELECT
                                        docum_elem.kod_docum,
                                        docum_elem.kod_elem,
                                        docum.path
                                    FROM
                                        docum_elem
                                    INNER JOIN docum ON docum_elem.kod_docum = docum.kod_docum
                                    WHERE kod_elem=$kod_elem AND docum_elem.del=0
                                    ");

        if ($db->cnt != 1)
            return;
        $kod_user = func::kod_user();

        $cnt = $db->cnt;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $kod_docum = $row['kod_docum'];
            $path = $row['path'];

            $db->query("UPDATE docum SET del=1,kod_user=$kod_user WHERE kod_docum=$kod_docum");

            if (!file_exists($path))
                continue;
            unlink($_SERVER["DOCUMENT_ROOT"] .'/'. $row['path']);
        }

        $db->query("UPDATE docum_elem SET del=1,kod_user=$kod_user WHERE kod_elem=$kod_elem");
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * Обработка событий
     */
    public static function Events()
    {
        $event = false;

        if (isset($_POST['Flag'])) {
            if ($_POST['Flag'] == 'DelDocum' and isset($_POST['kod_docum_del'])) {
                Docum::Delete($_POST['kod_docum_del']);
                $event = true;
            }
        }
        if ($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
}
//----------------------------------------------------------------------------------------------------------------------
