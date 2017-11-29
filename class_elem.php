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
            $kod_elem = $this->kod_elem;
            $this->getData($this->kod_elem);
            $name=$this->Data['name'];
        }

        return "<a href='form_elem.php?kod_elem=$kod_elem'>$name</a>";
    }
//----------------------------------------------------------------------
// Запрос данных
    /**
     * @param int $kod_elem
     * @return array
     */
    public function getData($kod_elem = 0)
    {
        if ($kod_elem != 0)
            $this->kod_elem = $kod_elem;

        $db = new Db();
        $rows = $db->rows("SELECT * FROM elem WHERE kod_elem=$this->kod_elem AND del=0");
        $this->Data = $rows[0];

        return $this->Data;
    }

//----------------------------------------------------------------------
// Наименование Элемента
    /**
     * @param string $field
     * @param int $Link
     * @return string
     */
    public function Name($field = 'name', $Link = 1) //
    {
        $db = new Db();
        $rows = $db->rows(/** @lang SQL */
            "SELECT * FROM elem WHERE kod_elem=$this->kod_elem AND del=0");

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
//
    /**
     * Вывод списка элементов, которые можно поставлять + остальные
     *
     */
    public function formNomen()
    {
        $db = new Db();
        $rows = $db->rows("SELECT
                                    view_elem.name AS elem_name,
                                    view_elem.kod_elem,
                                    view_elem.shifr,
                                    view_elem.nomen,
                                    photo.path
                                  FROM view_elem 
                                  LEFT JOIN (SELECT * FROM view_docum_elem WHERE name='Фото' ORDER BY view_docum_elem.kod_docum DESC) AS photo ON view_elem.kod_elem=photo.kod_elem
                                  GROUP BY view_elem.kod_elem
                                  ORDER BY shifr ASC");

        $cnt = $db->cnt;

        if($cnt==0)
            return "";

        $all=false;
        if(isset($_GET['all']))
            $all = true;

        $res = '<table border=0 cellspacing=5 cellpadding=10 rules="rows" frame="below">
                 <tr bgcolor="#CCCCCC">
                     <td width="10%">Фото</td>
                     <td align="center">Наименование</td>
                 </tr>';

        $other = "";

        for ($i = 0; $i < $db->cnt; $i++)
        {
            $row = $rows[$i];
            $kod_elem = $row['kod_elem'];
            $shifr = $row['shifr'];
            $img = "";
            $link = "form_elem.php?kod_elem=$kod_elem";
            if(isset($row['path']))
            {
                $path = $row['path'];
                $img = "<a href='$link'><img src='$path' width='100' border='0' /></a>";
            }


            $name = "";
            if ($row['shifr'] != $row['elem_name'])
                $name = $row['elem_name'];

            $row_nomen = "<tr>
                            <td align='left' valign='top'>$img</td>
                            <td valign='top'><a href='$link'><h1> $shifr </h1> $name </td>
                         </tr>";

            if($row['nomen']==1)
                $res.= $row_nomen;
            elseif($row['nomen']==0 and $all)
                $other.=$row_nomen;
        }

        if($all)
        {
            $res .= '<tr bgcolor="#CCCCCC">
                     <td width="10%">Остальная номенклатура</td>
                     <td align="center"></td>
                    </tr>';
            $res.=$other;
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

        $sql = "SELECT * FROM view_elem WHERE nomen=1 ORDER BY shifr";

        $rows = $db->rows($sql);

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $kod_elem = $row['kod_elem'];
            //$obozn = $row['obozn'];
            $shifr = $row['shifr'];
            $name = $row['name'];

            $selected = "";
            if ($row['kod_elem'] == $this->kod_elem)
                $selected = " selected='selected'";

            $res .= "<option value=\"$kod_elem\" $selected > $shifr  \"$name\" </option>";
        }
        $res .= '</select>';

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Вывод списка-выбора Элементов
     * @return string
     */
    public function formSelList2()
    {
        $db = new Db();

        $sql = "SELECT * FROM view_elem WHERE nomen=1 ORDER BY shifr";

        $rows = $db->rows($sql);

        if($db->cnt==0)
            return "";

        $res = "<select id='kod_elem' name='kod_elem' placeholder=\"Выбрать элемент...\">
";
        for ($i = 0; $i < $db->cnt; $i++) {
            $name = self::getSearchName($rows[$i]);
            $kod_elem = $rows[$i]['kod_elem'];

            $selected = "";
            if ($rows[$i]['kod_elem'] == $this->kod_elem)
                $selected = " selected='selected'";

            $res .= "<option value='$kod_elem' $selected>$name</option>\r\n";
        }
        $res .= '</select>
        <script type="text/javascript">
                        var kod_elem, $kod_elem;
    
                        $kod_elem = $("#kod_elem").selectize({
                            onChange: function(value) {
            if (!value.length) return;
        }
                        });
                        kod_elem = $kod_elem[0].selectize;
                </script>';

        return $res;
    }
//------------------------------------------------------------------------
//
    /**
     * Документы Элемента
     * @return string
     */
    public function formDocum()
    {
        return Docum::formDocum('Elem', $this->kod_elem);
    }
//------------------------------------------------------------------------
//
    /**
     * Фото Элемента
     * @return string
     */
    public function formPhoto()
    {
        $sql = "SELECT
                        docum.`name`,
                        docum.path
                    FROM
                        docum
                    INNER JOIN docum_elem ON docum_elem.kod_docum = docum.kod_docum 
                    WHERE docum.`name`='Фото' AND docum_elem.kod_elem=$this->kod_elem AND docum.del=0";
        $link = 'form_elem.php?kod_elem=' . $this->kod_elem;

        $db = new Db();
        $rows = $db->rows($sql);
        if($db->cnt==0)
            return "";

        $res = '';
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            if (file_exists($row['path']))
                $res .= ' <a href="' . $link . '"><img src="' . $row['path'] . '" width="100" border="0" /></a>';
        }
        return $res;
    }
//------------------------------------------------------------------------
//
    /**
     * Добавление элемента
     * @param string $obozn
     * @param string $name
     * @param string $shifr
     * @param int $nomen
     * @param string $shablon
     */
    public function AddElem($obozn, $name, $shifr, $nomen = 1, $shablon='')
    {
        $db = new Db();

        $obozn = ltrim($obozn);
        $name = ltrim($name);
        $kod_user = func::kod_user();

        $db->query("INSERT INTO elem (obozn,name,nomen, shifr,shablon,kod_user) VALUES('$obozn','$name',$nomen,'$shifr','$shablon',$kod_user)");

    }
//------------------------------------------------------------------------
//
    /**
     * Сохранить изменения
     * @param string $obozn
     * @param string $name
     * @param string $shablon
     * @param string $shifr
     */
    public function Save($obozn = '', $name = '', $shablon = '', $shifr = '')
    {
        $db = new Db();

        $kod_elem = $this->kod_elem;

        if ($obozn == '' or $name == '') return;
        $kod_user = func::kod_user();

        $db->query("UPDATE elem SET obozn = '$obozn', name = '$name', shablon='$shablon', shifr='$shifr', kod_user=$kod_user WHERE kod_elem = $kod_elem");

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
//
    /**
     * Форма добавления / внесения изменений
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
        $btn_nomen = "";

        if($Edit==1){
            $this->getData();
            $row = $this->Data;
            $shifr = $row['shifr'];
            $obozn = $row['obozn'];
            $name = $row['name'];
            $shablon = $row['shablon'];
            $FormName = "formEdit";

            if($row['nomen']==1)
                $btn_nomen = func::ActButton2('','Удалить из номенклатуры',"UnsetNomen","kod_elem_set",$row['kod_elem']);
            elseif($row['nomen']==0)
                $btn_nomen = func::ActButton2('','Добавить в номенклатуру',"SetNomen","kod_elem_set",$row['kod_elem']);
        }

        $res = '
            <form id="form1" name="form1" method="post" action="">
                  <table width="521" border="0">
                      <tr>
                        <td width="200">Шифр</td>
                        <td width="400">
                          <label>
                            <input name="shifr" id="shifr" value="' . $shifr . '" />
                          </label>
                        </td>
                    </tr>
                    <tr>
                        <td width="200">Обозначение (ДМ)*</td>
                        <td width="400">
                          <label>
                            <input name="obozn" id="obozn" value="' . $obozn . '" />
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
        $res.=$btn_nomen;
        return $res . Func::Cansel(0);
    }
//------------------------------------------------------------------
//

    /**
     * Удаление элемента с заменой
     * Заменяем указанный элемент в партиях на код Комплектующие=1001, в модификации добавляем наименование
     * @param int $kod_elem - код удаляемого элемента
     * @param int $kod_dest - код элемента на который надо заменить
     */
    public function DeleteReplace($kod_elem, $kod_dest=1001)
    {
        $db = new Db();

        $elem_name = $db->rows("SELECT * FROM elem WHERE kod_elem = $kod_elem AND del=0"); // Получаем название удаляемого элемента

        $name = $elem_name[0]['name'];
        $obozn = $elem_name[0]['obozn'];
        $kod_user = func::kod_user();

        $new_name = $obozn;
        if(substr_count($name,$obozn)==0)
            $new_name = $name.' '.$obozn;

        $db->query("UPDATE elem SET del=1, kod_user=$kod_user WHERE kod_elem=$kod_elem"); // Удаляем элемент
        $Docum = new Docum();
        $Docum->DeleteElemFiles($kod_elem); // Удаляем связные документы

        $rows = $db->rows("SELECT * FROM parts WHERE kod_elem=$kod_elem AND del=0"); // Получаем список партий, где участвовал удаленный элемент
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

            $db->query("UPDATE parts SET kod_elem=$kod_dest, modif='$modif', edit=1, kod_user=$kod_user WHERE kod_part=$kod_part"); // заменяем код уделенного элемента
        }

    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Обработка событий
     */
    public function Events()
    {
        $event = false;
        if(isset($_GET['setCompl'], $_GET['kod_elem']))
        {
            if((int)$_GET['setCompl']>0)
                $this->DeleteReplace($_GET['kod_elem'],(int)$_GET['setCompl']);
            else
                $this->DeleteReplace($_GET['kod_elem']);
            header('Location: http://' . $_SERVER['HTTP_HOST'] . "/form_nomen.php");
        }

        if (isset($_POST['formEdit'], $_POST['obozn'], $_POST['name'])) {
            $this->Save($_POST['obozn'], $_POST['name'], $_POST['shablon'], $_POST['shifr']);
            $event = true;
        }

        if (isset($_POST['formAdd'], $_POST['obozn'], $_POST['name'])) {
            $this->AddElem($_POST['obozn'], $_POST['name'], $_POST['shifr'], 1, $_POST['shablon']);
            $event = true;
        }

        if(isset($_POST['Flag']))
        {
            $flag = $_POST['Flag'];

            if($flag=='SetNomen' and isset($_POST['kod_elem_set']))
            {
                $this->setNomen($_POST['kod_elem_set'],1);
                $event = true;
            }
            elseif($flag=='UnsetNomen' and isset($_POST['kod_elem_set']))
            {
                $this->setNomen($_POST['kod_elem_set'],0);
                $event = true;
            }
            elseif ($flag=="AddSubElem" and isset($_POST['kod_elem_base'],$_POST['kod_elem']))
            {
                $this->addSubElem($_POST['kod_elem']);
                $event = true;
            }
            elseif($flag=="DelFromSpec" and isset($_POST['kod_spec_del']))
            {
                $this->delSpec($_POST['kod_spec_del']);
                $event = true;
            }
        }

        if($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Установить признак номенклатуры
     * @param $kod_elem
     * @param int $nomen - 0-не в номенклатуре, 1-в номенклатуре(продаем)
     */
    public function setNomen($kod_elem,$nomen=0)
    {
        $db = new Db();

        $db->query("UPDATE elem SET nomen = $nomen WHERE kod_elem = $kod_elem");

    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Выдает строку для поиска по элементам - Шифр - Обозначение - Наименование - Код
     * @param $row
     * @return mixed|string
     */
    public static function getSearchName($row)
    {
        if(count($row)===0)
            return "";

        $name = $row['name'];
        $obozn = $row['obozn'];
        $shifr = $row['shifr'];
        $kod_elem = $row['kod_elem'];

        if($shifr!=="" and $name==$shifr)
            $name = "";

        if($obozn!=="" and strpos($name,$obozn)!==false)
            $obozn = "";

        if($shifr==$obozn)
            $obozn = "";

        return "$shifr $obozn $name $kod_elem";
    }
//------------------------------------------------------------------------
//
    /**
     * Добавление элемента в спецификацию
     * @param $kod_elem - код подчиненного элемента
     * @param int $quantity - количество/применяемость
     * @param int $type - тип (обязательный или нет)
     */
    public function addSubElem($kod_elem, $quantity=1,$type=1)
    {
        if($kod_elem==$this->kod_elem) // Самого в себя нельзя добавить
            return;
        // todo - возможно потребуется проверка вложений, нет ли вхождений в подчиненные элементы

        $db = new Db();

        $kod_user = func::kod_user();
        // Проверка - может элемент уже есть в спецификации
        $db->rows("SELECT * FROM specs WHERE kod_elem_base=$this->kod_elem AND kod_elem_sub=$kod_elem");
        if($db->cnt>0)
            return;

        $db->query("INSERT INTO specs (kod_elem_base,kod_elem_sub,quantity,type,kod_user) VALUES($this->kod_elem,$kod_elem,$quantity,$type,$kod_user)");
    }
//------------------------------------------------------------------------
    public function formSpecTotal()
    {
        $res = "";
        if(isset($_SESSION['MM_UserGroup'])) // todo - придумать глобальную политику прав
            if($_SESSION['MM_UserGroup']==="admin")
                $res = func::ActButton2("","Добавить в спецификацию","AddSubElem","kod_elem",$this->kod_elem );

        if(isset($_POST['kod_elem']))
            if($_POST['kod_elem']==$this->kod_elem)
            {
                $res.= "<form method='post'>";
                $res.= $this->formSelList2(); // kod_elem
                $res.= "<input type='hidden' name='kod_elem_base' value='$this->kod_elem'>";
                $res.= "<input type='hidden' name='Flag' value='AddSubElem'>";
                $res.= "<input type='submit' value='Добавить'>";
                $res.= "</form>";
                $res.= func::Cansel();
            }

        $spec = $this->formSpec();
        if($spec!=="")
            $res.="<h3>Спецификация</h3>".$spec;

        $spec = $this->formSpecSub();
        if($spec!=="")
            $res.="<h3><a href='form_main.php?sgp=8&kod_elem=$this->kod_elem'>Входит в состав</a></h3>".$spec;

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Вывод списка элементов, которые входят в состав данного элемента
     *
     */
    public function formSpec()
    {
        $db = new Db();
        $rows = $db->rows("SELECT
                                      view_elem.name AS elem_name,
                                      view_elem.kod_elem,
                                      view_elem.shifr,
                                      view_elem.nomen,
                                      photo.path,
                                      specs.quantity,
                                      specs.kod_spec
                                    FROM specs
                                      INNER JOIN view_elem ON kod_elem_sub=view_elem.kod_elem
                                      LEFT JOIN (SELECT * FROM view_docum_elem WHERE name='Фото' ORDER BY view_docum_elem.kod_docum DESC) AS photo ON view_elem.kod_elem=photo.kod_elem
                                    WHERE specs.kod_elem_base=$this->kod_elem AND specs.del=0
                                    ORDER BY shifr ASC");

        $cnt = $db->cnt;

        if($cnt==0)
            return "";

        $res = $this->getSpec($rows);

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Вывод списка элементов, в состав которых входит данный элемент
     *
     */
    public function formSpecSub()
    {
        $db = new Db();
        $rows = $db->rows("SELECT
                                      view_elem.name AS elem_name,
                                      view_elem.kod_elem,
                                      view_elem.shifr,
                                      view_elem.nomen,
                                      photo.path,
                                      specs.quantity,
                                      specs.kod_spec
                                    FROM specs
                                      INNER JOIN view_elem ON kod_elem_base=view_elem.kod_elem
                                      LEFT JOIN (SELECT * FROM view_docum_elem WHERE name='Фото' ORDER BY view_docum_elem.kod_docum DESC) AS photo ON view_elem.kod_elem=photo.kod_elem
                                    WHERE specs.kod_elem_sub=$this->kod_elem AND specs.del=0
                                    ORDER BY shifr ASC");

        $cnt = $db->cnt;

        if($cnt==0)
            return "";

        $res = $this->getSpec($rows);

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param $kod_spec
     */
    public function delSpec($kod_spec)
    {
        $db = new Db();
        $db->query("UPDATE specs SET del=1 WHERE kod_spec=$kod_spec"); // Удаляем элемент
    }
//----------------------------------------------------------------------------------------------------------------------

    public function getSpec($rows)
    {
        $all=false;
        if(isset($_GET['all']))
            $all = true;

        $res = '<table border=0 cellspacing=0 cellpadding=0 rules="rows" frame="below" width="100%">
                 <tr bgcolor="#CCCCCC">
                     <td width="10%">Фото</td>
                     <td align="center">Наименование</td>
                     <td align="center">Применяемость</td>
                 </tr>';

        $other = "";
        $cnt = count($rows);

        for ($i = 0; $i < $cnt; $i++)
        {
            $row = $rows[$i];
            $kod_elem = $row['kod_elem'];
            $shifr = $row['shifr'];
            $img = "";
            $link = "form_elem.php?kod_elem=$kod_elem";
            if(isset($row['path']))
            {
                $path = $row['path'];
                $img = "<a href='$link'><img src='$path' width='100' border='0' /></a>";
            }

            $btn_nomen = "";
            if(isset($_SESSION['MM_UserGroup'])) // todo - придумать глобальную политику прав
                if($_SESSION['MM_UserGroup']==="admin")
                    $btn_nomen = func::ActButton2('','Удалить',"DelFromSpec","kod_spec_del",$row['kod_spec']);

            $name = "";
            if ($row['shifr'] != $row['elem_name'])
                $name = $row['elem_name'];

            $quantity = $row['quantity'];

            $row_nomen = "<tr>
                            <td align='left' valign='top'>$img</td>
                            <td valign='top'><a href='$link'><h1> $shifr </h1> $name </td>
                            <td valign='top'>$quantity $btn_nomen</td>
                         </tr>";

            if($row['nomen']==1)
                $res.= $row_nomen;
            elseif($row['nomen']==0 and $all)
                $other.=$row_nomen;
        }

        if($all)
        {
            $res .= '<tr bgcolor="#CCCCCC">
                     <td width="10%">Остальная номенклатура</td>
                     <td align="center"></td>
                    </tr>';
            $res.=$other;
        }

        $res.=  '</table>';
        return $res;
    }
}