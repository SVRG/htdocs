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
        $search = $db->real_escape_string($_POST['search']);
        if($search == "" or strlen($search)<2)
            return "Ничего не найдено.";
        $rows = $db->rows(/** @lang MySQL */ "SELECT * FROM view_rplan WHERE (nomer LIKE '%$search%') OR (ispolnit_nazv_krat LIKE '%$search%') OR (nazv_krat LIKE '%$search%') ORDER BY kod_dogovora;");
        if($db->cnt == 0)
            return "";
        return Doc::formRPlan_by_Doc($rows);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Фома поисковой строки
     * @return string
     */
    public static function formSearch()
    {
        $search = "";
        if(isset($_POST['search']))
            $search = $_POST['search'];
        $res = /** @lang HTML */
            "<form method='post' target='_blank' action='form_doclist.php'>
                <div class='btn'>
                    <div><input type='search' name='search' value='$search'></div>
                    <div><input type='submit' value='Поиск'></div>
                </div>    
            </form>";
        return $res;
    }
}