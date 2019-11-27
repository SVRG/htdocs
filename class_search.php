<?php
include_once "class_doc.php";


class Search
{
    /**
     * Форма результатов поиска по договорам
     * @return string
     * @throws Exception
     */
    public static function formDocSerch()
    {
        $search = self::getSearch();
        if ($search == "" or strlen($search) < 2)
            return "Ничего не найдено.";
        $where = ""; // Дополнительные условия

        if (isset($_GET['kod_elem'])) {
            $kod_elem = (int)$_GET['kod_elem'];
            $where .= " AND kod_elem=$kod_elem";
        }

        $where_org = "OR (ispolnit_nazv_krat LIKE '%$search%') 
                               OR (nazv_krat LIKE '%$search%')";
        if (isset($_GET['kod_org'])) {
            $where .= " AND (kod_org=" . (int)$_GET['kod_org'] . " OR kod_ispolnit=".(int)$_GET['kod_org'].")";
            $where_org = "";
        }

        if (isset($_GET['doc_type'])) {
            $where .= " AND doc_type=" . (int)$_GET['doc_type'];
        }

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM view_rplan 
                    WHERE ((nomer LIKE '%$search%') 
                               $where_org
                               OR (name LIKE '%$search%' OR shifr LIKE '%$search%' OR modif LIKE '%$search%'))                   
                               $where
                    ORDER BY kod_dogovora DESC;");

        //if(isset($_GET['kod_elem'])) // todo - продумать как выводить по элементу, сейчас все в кучу - входящие и исходящие
        //    return Doc::formRPlan_by_Elem($rows);
        $res = "";
        if ($db->cnt > 0)
            $res = Doc::formRPlan_by_Doc($rows);

        $D = new Doc();
        $res .= $D->formDocList(/** @lang MySQL */
            "SELECT * FROM view_scheta_dogovory_all 
                    WHERE kod_dogovora NOT IN (SELECT view_rplan.kod_dogovora FROM view_rplan 
                                                WHERE ((nomer LIKE '%$search%') 
                                                           $where_org
                                                           OR (name LIKE '%$search%' OR shifr LIKE '%$search%' OR modif LIKE '%$search%'))                   
                                                           $where)
                    AND (nomer LIKE '%$search%')                    
                    $where_org
                    $where
                    ORDER BY kod_dogovora DESC;");

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Форма результатов поиска по договорам
     * @return string
     * @throws Exception
     */
    public static function formKontSerch()
    {
        $search = self::getSearch();
        if ($search == "" or strlen($search) < 2)
            return "Для запроса нужно не менее 2-х символов.";

        $where = "";
        if(isset($_GET['kod_org']))
        {
            $where .= " AND kod_org=".(int)$_GET['kod_org'];
        }

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT
                                    kontakty.kod_kontakta,
                                    kontakty.kod_org,
                                    kontakty.dolg,
                                    kontakty.famil,
                                    kontakty.`name`,
                                    kontakty.otch,
                                    org.nazv_krat
                                FROM
                                    kontakty
                                INNER JOIN org ON kontakty.kod_org = org.kod_org
                                WHERE kontakty.del=0 AND (famil LIKE '%$search%') $where
                                ORDER BY
                                    kontakty.famil;");
        $res = "";
        if ($db->cnt > 0) {
            $kont = new Kontakt();
            $res .= $kont->getKontaktForm($rows);
        }

        // Попытаемся найти по e-mail или телефону
        if(strlen($search) >= 6) {
            $rows = $db->rows(/** @lang MySQL */
                "SELECT   `kontakty`.`kod_kontakta`,
                                         `kontakty`.`name`,
                                         `kontakty`.`famil`,
                                         `kontakty`.`otch`,
                                         `kontakty`.`dolg`,
                                         `org`.`nazv_krat`,
                                         `org`.`kod_org`,
                                         `kontakty_data`.`data`
                                FROM     `kontakty` 
                                INNER JOIN `org`  ON `kontakty`.`kod_org` = `org`.`kod_org` 
                                INNER JOIN `kontakty_data`  ON `kontakty`.`kod_kontakta` = `kontakty_data`.`kod_kontakta` 
                                WHERE kontakty.del=0 AND (data LIKE '%$search%') $where
                                ORDER BY
                                    kontakty.famil;");

            if ($db->cnt > 0) {
                $kont = new Kontakt();
                $res .= $kont->getKontaktForm($rows);
            }
        }
        // todo - добавить поиск по телефону, требуется приводить строку поиска и телефон к единому виду (нет пробелов, тире и букв).

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Фома поисковой строки
     * @return string
     */
    public static function formSearch()
    {
        $search = self::getSearch();
        $res = /** @lang HTML */
            "<form method='post' target='_parent' action='form_doclist.php?search'>
                <div class='btn'>
                    <div><input type='search' name='search' value='$search'></div>
                    <div><input type='submit' value='Поиск'></div>
                </div>    
            </form>";
        return $res;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Получаем строку поиска из переменных
     * @return string
     */
    public static function getSearch()
    {
        $search = "";
        if (isset($_POST['search']))
            $search = func::clearString($_POST['search']);
        elseif (isset($_SESSION['search']))
            $search = func::clearString($_SESSION['search']);

        if ($search != "") {
            $db = new Db();
            $search = $db->real_escape_string($search);

            $_SESSION['search'] = $search;
        } else
            unset($_SESSION['search']);
        return $search;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Результат поиска по организациям
     * @return string
     */
    public static function formOrgSearch()
    {
        $search = self::getSearch();
        if ($search == "" or strlen($search) < 2)
            return "Для запроса нужно не менее 2-х символов.";

        $where = "";
        if(strlen(func::clearNum($search)) == 10) // Вероятно это ИНН
            $where .= " OR (inn LIKE '%$search%')";

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM org WHERE 
                        (poisk LIKE '%$search%') OR 
                        (nazv_krat LIKE '%$search%') OR 
                        (nazv_poln LIKE '%$search%') $where
                    ORDER BY poisk;");
        return Org::formOrgListRows($rows);
    }
}