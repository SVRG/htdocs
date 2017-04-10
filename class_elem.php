<?php
include_once "class_func.php";
include_once "class_db.php";
include_once "class_docum.php";
include_once "class_org.php";

class Elem
{
    public $kod_elem;
    public $Mod = '';
    public $Data;       // Данные полей записи

//----------------------------------------------------------------------
// Ссылка на форму
    /**
     * @param string $name
     * @param int $kod_elem
     * @return string
     */
    public function getFormLink($name='',$kod_elem=-1)
    {
        if($name=='' or $kod_elem==-1)
        {
            $this->getData($this->kod_elem);
            $name=$this->Data['name'];
        }

        return "<a href='form_elem.php?kod_elem=$kod_elem'>$name</a>";
    }
//----------------------------------------------------------------------
// Запрос данных
    /**
     * @param int $ID
     * @return mixed
     */
    public function getData($ID = 0)
    {
        if ($ID != 0)
            $this->kod_elem = $ID;

        $db = new Db();
        $rows = $db->rows("SELECT * FROM elem WHERE kod_elem=$this->kod_elem");
        $this->Data = $rows[0];

        return $this->Data;
    }

//----------------------------------------------------------------------
// Наименование Элемента
    /**
     * @param string $field
     * @param int $Link
     * @return string
     * @internal param string $t
     */
    public function Name($field = 'name', $Link = 1) //
    {
        $db = new Db();
        $rows = $db->rows(/** @lang SQL */
            "SELECT * FROM elem WHERE kod_elem=$this->kod_elem");

        if ($db->cnt == 0)
            return "";

        $row = $rows[0];
        $this->Data = $row;

        if ($field == 'shablon' and $this->Mod != "" and $row['shablon'] != "")
            $res = str_replace('[Mod]', $this->Mod, $row['shablon']);
        else
            $res = $row[$field];

        if ($Link == 1)
            return '<a href="form_elem.php?kod_elem=' . $this->kod_elem . '">' . $res . '</a>';

        return $res;
    }

//----------------------------------------------------------------------
// Вывод списка элементов, которые можно поставлять
    /**
     *
     */
    public function formNomen()
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM view_elem WHERE nomen=1");

        $cnt = $db->cnt;

        if($cnt==0)
            return "";

        $res = '<table border=0 cellspacing=5 cellpadding=10 rules="rows" frame="below">
                 <tr bgcolor="#CCCCCC">
                 <td width="10%">Фото</td>
                 <td align="center">Наименование</td>
                 </tr>';

        for ($i = 0; $i < $db->cnt; $i++)
        {
            $row = $rows[$i];
            $this->kod_elem = $row['kod_elem'];

            $name = "";
            if ($row['obozn'] != $row['name'])
                $name = $row['name'];

            $res.=  '<tr>
		  			<td align="left" valign="top">' . $this->formPhoto() . '</td>
		  			<td valign="top"><a href="form_elem.php?kod_elem=' . $row['kod_elem'] . '"><h1>' . $row['obozn'] . '</h1>' . $name . '</td>
		         </tr>';

        }
        $res.=  '</table>';

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Договоры по Элементу
     * @return string
     */
    public function formDocs()
    {
        return Doc::formDocByElem($this->kod_elem);
    }
//----------------------------------------------------------------------
//
    /**
     * Вывод списка-выбора Элементов
     * @return string
     */
    public function formSelList()
    {
        $res = '<select name="kod_elem" id="kod_elem">';

        $db = new Db();

        $sql = "SELECT * FROM view_elem WHERE nomen=1";

        $rows = $db->rows($sql);

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $selected = "";
            $kod_elem = $row['kod_elem'];
            $obozn = $row['obozn'];
            $name = $row['name'];

            if ($row['kod_elem'] == $this->kod_elem)
                $selected = " selected='selected'";
            $res .= "<option value=\"$kod_elem\" $selected > $obozn  \"$name\" </option>";
        }
        $res .= '</select>';

        return $res;
    }
//------------------------------------------------------------------------
// Документы Элемента
    /**
     * @param int $Del
     * @return string
     */
    public function formDocum($Del = 0)
    {
        return Docum::formDocum('Elem', $this->kod_elem, $Del);
    }
//------------------------------------------------------------------------
// Фото Элемента
    /**
     * @return string
     */
    public function formPhoto()
    {
        $sql = "SELECT
                        docum.kod_docum,
                        docum.`name`,
                        docum.path,
                        docum_elem.kod_elem
                    FROM
                        docum_elem
                    INNER JOIN docum ON docum_elem.kod_docum = docum.kod_docum WHERE docum.`name`='Фото' AND docum_elem.kod_elem=$this->kod_elem";
        $link = 'form_elem.php?kod_elem=' . $this->kod_elem;

        $db = new Db();
        $rows = $db->rows($sql);

        $res = '';
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            if (file_exists($row['path']))
                if ($row['name'] == 'Фото')
                    $res .= ' <a href="' . $link . '"><img src="' . $row['path'] . '" width="100" border="0" /></a>';
        }
        return $res;
    }
//------------------------------------------------------------------------
//
    /**
     * @param $obozn
     * @param $name
     * @param $shifr
     * @param int $nomen
     */
    public function AddElem($obozn, $name, $shifr, $nomen = 1, $shablon='')
    {
        $db = new Db();

        $obozn = ltrim($obozn);
        $name = ltrim($name);

        $db->query("INSERT INTO elem (obozn,name,nomen, shifr,shablon) VALUES('$obozn','$name',$nomen,'$shifr','$shablon')",1);

    }
//------------------------------------------------------------------------
// Сохранить изменения
    /**
     * @param string $obozn
     * @param string $name
     * @param string $shablon
     * @param string $shifr
     */
    public function Save($obozn = '', $name = '', $shablon = '', $shifr = '')
    {
        $db = new Db();

        $ID = $this->kod_elem;

        if ($obozn == '' or $name == '') return;

        $db->query("UPDATE elem SET obozn = '$obozn', name = '$name', shablon='$shablon', shifr='$shifr' WHERE kod_elem = $ID");

    }
//------------------------------------------------------------------------
//
    /**
     * Список Организаций которые покупали данный элемент
     * @return string
     */
    public function formOrgByElem()
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM view_elem_org WHERE kod_elem=" . $this->kod_elem);

        if ($db->cnt == 0)
            return "";

        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">
		            <tr bgcolor="#CCCCCC">
		                <td>Название</td>
		                <td>Количество</td>
		            </tr>';

        $sum = 0;
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            $nazv_krat = $row['nazv_krat'];
            $numb = (int)$row['numb'];
            $kod_org = (int)$row['kod_org'];

            $org_link = "form_org.php?kod_org=" . $kod_org;

            $res .= "<tr>
                        <td><a href='$org_link'> $nazv_krat </a></td>
                        <td align='right'> $numb </td>
        		  	</tr>";
            $sum += (int)$numb;
        }
        $res .= '<tr bgcolor="#CCCCCC">
                    <td align="right">Сумма</td>
                    <td align="right">' . $sum . '</td>
                 </tr>';

        $res .= '</table>';
        return $res;
    }

//-----------------------------------------------------------------------------
// Форма внесения изменений
    /**
     * @param int $Edit
     * @return string
     */
    public function formAddEdit($Edit=0)
    {

        $shifr = "";
        $obozn = "";
        $name = "";
        $shablon = "";
        $FormName = "formAdd";

        if($Edit==1){
            $this->getData();
            $row = $this->Data;
            $shifr = $row['shifr'];
            $obozn = $row['obozn'];
            $name = $row['name'];
            $shablon = $row['shablon'];
            $FormName = "formEdit";
        }

        $res = '
            <form id="form1" name="form1" method="post" action="">
                  <table width="521" border="0">
                      <tr>
                        <td width="200">Шифр</td>
                        <td width="400">
                          <label>
                            <input type="text" name="shifr" id="shifr" value="' . $shifr . '" />
                          </label>
                        </td>
                    </tr>
                    <tr>
                        <td width="200">Обозначение (ДМ)*</td>
                        <td width="400">
                          <label>
                            <input type="text" name="obozn" id="obozn" value="' . $obozn . '" />
                            </label>
                        </td>
                      </tr>
                      <tr>
                        <td>Наименование по ТУ*</td>
                        <td>
                          <label>
                          <textarea rows=7 cols=60 name="name" id="name">' . $name . '</textarea>  
                            </label>
                         </td>
                      </tr>
                        <tr>
                        <td>Шаблон Модификаций [Mod]</td>
                        <td>
                          <label>
                          <textarea rows=2 cols=60 name="shablon" id="shablon">' . $shablon . '</textarea>  
                            </label>
                         </td>
                      </tr>
                  </table>
                  <input id="'.$FormName.'" type="hidden" value="'.$FormName.'" name="'.$FormName.'"/>
                  <input type="submit" value="Сохранить" />
                </form>';

        return $res . Func::Cansel(0);
    }
//------------------------------------------------------------------
// Удаление элемента с заменой
// Заменяем указанный элемент в партиях на код Комплектующие=1001, в модификации добавляем наименование
    /**
     * @param int $kod_elem - код удаляемого элемента
     * @param int $kod_dest - код элекмента на который надо заменить
     */
    public function DeleteReplace($kod_elem, $kod_dest=1001)
    {
        $db = new Db();

        $elem_name = $db->rows("SELECT * FROM elem WHERE kod_elem = $kod_elem"); // Получаем название удаляемого элемента

        $name = $elem_name[0]['name'];
        $obozn = $elem_name[0]['obozn'];

        $new_name = $obozn;
        if(substr_count($name,$obozn)==0)
            $new_name = $name.' '.$obozn;

        $db->query("DELETE FROM elem WHERE kod_elem=$kod_elem"); // Удаляем элемент
        Docum::DeleteElemFiles($kod_elem); // Удаляем связные документы

        $rows = $db->rows("SELECT * FROM parts WHERE kod_elem = $kod_elem"); // Получаем списое партий, где участвовал удаленный элемент
        if($db->cnt==0)
            return;

        $cnt = $db->cnt;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $modif = $row['modif'];
            if($modif!='')
                $modif='('.$modif.')';

            $modif = $new_name.$modif;
            $kod_part = $row['kod_part'];

            $db->query("UPDATE parts SET kod_elem=$kod_dest, modif='$modif' WHERE kod_part=$kod_part"); // заменяем код уделенного элемента
        }

    }
//----------------------------------------------------------------------------------------------------------------------
    public function Events()
    {
        $event = false;
        if(isset($_GET['setCompl'], $_GET['kod_elem']))
        {
            $this->DeleteReplace($_GET['kod_elem']);
            $event = true;
        }

        if (isset($_POST['DelDocum'])) {
            $docum = new Docum();
            $docum->Delete($_POST['DelDocum']);
            $event = true;
        }

        if (isset($_POST['formEdit'], $_POST['obozn'], $_POST['name'])) {
            $this->Save($_POST['obozn'], $_POST['name'], $_POST['shablon'], $_POST['shifr']);
            $event = true;
        }

        if (isset($_POST['formAdd'], $_POST['obozn'], $_POST['name'])) {
            $this->AddElem($_POST['obozn'], $_POST['name'], $_POST['shifr'], 1, $_POST['shablon']);
            $event = true;
        }

        if($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//----------------------------------------------------------------------------------------------------------------------
}