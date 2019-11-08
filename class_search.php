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
        $db = new Db();
        $search = self::getSearch();
        if ($search == "" or strlen($search) < 2)
            return "Ничего не найдено.";
        $where = ""; // Дополнительные условия

        if (isset($_GET['kod_elem'])) {
            $kod_elem = (int)$_GET['kod_elem'];
            $where .= " AND kod_elem=$kod_elem";
        }

        if (isset($_GET['kod_org'])) {
            $where .= " AND kod_org=" . (int)$_GET['kod_org'];
        }

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM view_rplan 
                    WHERE ((nomer LIKE '%$search%') OR (ispolnit_nazv_krat LIKE '%$search%') OR (nazv_krat LIKE '%$search%')) $where
                    ORDER BY kod_dogovora;");
        if ($db->cnt == 0)
            return "";
        return Doc::formRPlan_by_Doc($rows);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Форма результатов поиска по договорам
     * @return string
     * @throws Exception
     */
    public static function formKontSerch()
    {
        $db = new Db();
        $search = self::getSearch();
        if ($search == "" or strlen($search) < 2)
            return "Ничего не найдено.";
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
                                WHERE kontakty.del=0 AND (famil LIKE '%$search%')
                                ORDER BY
                                    kontakty.famil;");
        if ($db->cnt == 0)
            return "";
        $kont = new Kontakt();
        return $kont->getKontaktForm($rows);
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
}