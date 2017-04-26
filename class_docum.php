<?php
include_once('class_db.php');

class Docum
{
//---------------------------------------------------------------------
//
    /**
     * Документы договора/елемента/организации
     * @param string $Type
     * @param int $ID
     * @param int $Del - кнопка удаления
     * @return string
     */
    public static function formDocum($Type = 'Doc', $ID =1, $Del = 0)
    {

        $sql = '';
        if ($Type == 'Elem')
            $sql =          "SELECT
                                docum_elem.kod_docum,
                                docum_elem.kod_elem,
                                docum.`name`,
                                docum.path
                              FROM
                                docum_elem
                              INNER JOIN docum ON docum_elem.kod_docum = docum.kod_docum
                              WHERE kod_elem=" . $ID;

        elseif ($Type == 'Doc')
            $sql =      "SELECT
                            docum_dogovory.kod_docum,
                            docum_dogovory.kod_dogovora,
                            docum.`name`,
                            docum.path
                          FROM
                            docum
                          INNER JOIN docum_dogovory ON docum_dogovory.kod_docum = docum.kod_docum
                          WHERE docum_dogovory.kod_dogovora=" . $ID;

        elseif ($Type == 'Org')
            $sql =   "SELECT
                        docum_org.kod_docum,
                        docum_org.kod_org,
                        docum.`name`,
                        docum.path
                      FROM
                        docum_org
                      INNER JOIN docum ON docum_org.kod_docum = docum.kod_docum
                      WHERE kod_org=" . $ID;

        if($sql=='')
            return "";

        $db = new DB();
        $rows = $db->rows($sql);

        $res = "";
        if ($Type == 'Doc')
            $res .= Func::ActButton('upload.php?Desc=IncludeToDoc&kod_dogovora=' . $ID, 'Прикрепить Файл');
        elseif ($Type == 'Elem')
            $res .= Func::ActButton('upload.php?Desc=IncludeToElem&kod_elem=' . $ID, 'Прикрепить Файл');
        elseif ($Type == 'Org')
            $res .= Func::ActButton('upload.php?Desc=IncludeToOrg&kod_org=' . $ID, 'Прикрепить Файл');
        elseif ($Type == 'Cont')
            $res .= Func::ActButton('upload.php?Desc=IncludeToCont&kod_kontakta=' . $ID, 'Прикрепить Файл');

        if($db->cnt==0)
            return $res;

        $res .= '<table>';

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            if (file_exists($row['path'])) {
                $name = $row['name'];
                $path = $row['path'];
                $del='';

                if ($Del == 1)
                    $del .= Func::ActForm('', '<input type="hidden" name="DelDocum" id="ID" value="' . $row['kod_docum'] . '" />', 'Del', '1');

                $res .= "<tr>
                            <td> <a href='$path'> $name </a></td>
                            <td>$del</td>
                         </tr>";
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
    public function Delete($kod_docum)
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM docum WHERE kod_docum=$kod_docum");

        if($db->cnt!=1)
            return;

        $row = $rows[0];

        $db->query("DELETE FROM docum WHERE kod_docum= $kod_docum");
        $db->query("DELETE FROM docum_dogovory WHERE kod_docum= $kod_docum");
        $db->query("DELETE FROM docum_elem WHERE kod_docum= $kod_docum");
        $db->query("DELETE FROM docum_org WHERE kod_docum= $kod_docum");

        if (!file_exists($row['path']))
            return;

        $path = realpath($_SERVER["DOCUMENT_ROOT"]).'/'.$row['path'];

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
    function Add($name, $path, $ID, $Dest='Doc')
    {
        $db = new Db();
        if($Dest=='Doc')
        {
            $db->query("INSERT INTO docum (name,path) VALUES('$name', '$path')");
            $db->query("INSERT INTO docum_dogovory (kod_docum,kod_dogovora) VALUES (LAST_INSERT_ID(),$ID)");
        }

        elseif($Dest=='Org')
        {
            $db->query("INSERT INTO docum (name,path) VALUES('$name', '$path')");
            $db->query("INSERT INTO docum_org (kod_docum,kod_org) VALUES (LAST_INSERT_ID(),$ID)");
        }
        elseif($Dest=='Elem')
        {
            $db->query("INSERT INTO docum (name,path) VALUES('$name', '$path')");
            $db->query("INSERT INTO docum_elem (kod_docum,kod_elem) VALUES (LAST_INSERT_ID(),$ID)");
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
    static public function DeleteElemFiles($kod_elem)
    {
        $db = new Db();

        $rows = $db->rows("  SELECT
                                        docum_elem.kod_docum,
                                        docum_elem.kod_elem,
                                        docum.path
                                    FROM
                                        docum_elem
                                    INNER JOIN docum ON docum_elem.kod_docum = docum.kod_docum
                                    WHERE kod_elem=$kod_elem");

        if($db->cnt!=1)
            return;
        $cnt = $db->cnt;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $kod_docum = $row['kod_docum'];

            $path = $row['path'];

            $db->query("DELETE FROM docum WHERE kod_docum= $kod_docum");

            if (!file_exists($path))
                continue;
            unlink("C:/xampp/htdocs/" . $row['path']); // todo - нужно задавать путь!
        }

        $db->query("DELETE FROM docum_elem WHERE kod_elem= $kod_elem");
    }
//-----------------------------------------------------------------------
}