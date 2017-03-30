<?php
//include_once('Thumbnail.php');

class Func
{

    public $green = '#ADFAC2'; // Зеленый
    public $orange = '#FFD222'; // Оранж
    public $red = '#F18585'; // Красный
    public $grey = '#CCCCCC'; // Серый
    public $yellow = '#FFFF99';// Желтый
//----------------------------------------------------------------
    /**
     * Сколько дней осталось до указанной даты
     * @param $Date
     * @return int
     */
    public static function DaysRem($Date)
    {
        if (!isset($Date)) return 0;

        if ($Date == '-') return 0;

        $d = explode('.', $Date);
        $res = 0;

        if (count($d) == 3)
            if ($d[0] > 0 and $d[1] > 0 and $d[2] > 0) {
                $d1 = mktime(0, 0, 0, $d[1], $d[0], $d[2]);
                $d2 = mktime(0, 0, 0, date('m'), date('d'), date('y'));
                $res = intval(($d1 - $d2) / 86400);
            } else
                return 0;

        return $res;
    }

//--------------------------------------------------------------
    public static function Rub($R)
    {
        $R = round((double)$R, 2);
        return number_format($R, 2, '.', ' ');
    }

//--------------------------------------------------------------
// Преобразуем дату в формат yyyymmdd для MySQL
    public static function DateR($Date = 0)
    {

        if ($Date == 0) return date('y.m.d');

        $date_corrected = explode('.', $Date);
        $date_corrected = $date_corrected[2] . $date_corrected[1] . $date_corrected[0];

        return $date_corrected;
    }

//--------------------------------------------------------------
// Дата в формате yy-mm-dd в формат dd.mm.yyyy
    public static function DateE($Dt = 0)
    {

        $res = '';

        if (!isset($Dt)) return date('y.m.d');

        $d = explode(' ', $Dt);
        $de = explode('-', $d[0]);
        if (count($de) > 1) {
            if ($de[2] > 0 and $de[1] > 0 and $de[0] > 0)
                $res = $de[2] . '.' . $de[1] . '.' . $de[0];
        } else {

            $de = explode('.', $d[0]);

            if (count($de) > 1) {
                if ($de[2] > 0 and $de[1] > 0 and $de[0] > 0)
                    $res = $de[0] . '.' . $de[1] . '.' . $de[2];
            }
        }

        return $res;
    }

//--------------------------------------------------------------
    public static function Proc($R)
    {
        $R = round($R * 100, 0);
        return $R;
    }
//--------------------------------------------------------------
//
    public static function Mstr($Str, $Delim = '-')
    {
        if (isset($Str)) {
            if ($Str == '')
                return $Delim;
            else
                return $Str;
        }
        return $Delim;
    }
//---------------------------------------------------------------
// Создает Форму с одной кнопкой
    public static function ActButton($Act = '', $ButtValue = 'OK', $Val = 'Act')
    {
        $res = "<form id='FID' name='FNAME' method='POST' action='$Act '>
                    <input type='submit' name='Button' id='ID' value='$ButtValue' />
                    <input type='hidden' name='Flag' id='$Val' value='$Val' />
                </form>";

        return $res;
    }
//--------------------------------------------------------------
// Построение Формы
    public static function ActForm($Act = '', $Body = '', $ButtValue = 'OK', $FlagVal = 'OK')
    {
        $res = "<form id='FID' name='FNAME' method='POST' action=' $Act '>
                    $Body
                    <input type='submit' name='Button' id='ID' value=' $ButtValue ' />
                    <input type='hidden' name='Flag' id='Flag' value='$FlagVal' />
               </form>";

        return $res;
    }
//----------------------------------------------------------------
// Текущее число
    public static function NowE($Delim = '.')
    {
        return date('d') . $Delim . date('m') . $Delim . date('Y');
    }
//----------------------------------------------------------------
// Текущее число
    public static function NowDoc($Delim = '_')
    {
        return date('Y') . $Delim . date('m') . $Delim . date('d');
    }
//----------------------------------------------------------------
//
    public static function Cansel($Echo = 1)
    {
        $res = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', '');

        if($Echo==1)
        {
            echo $res;
            return "";
        }

        return $res;
    }

//----------------------------------------------------------------
// Ссылка
    static public function Link($Link = '', $Text = '')
    {
        if ($Link == '') return '';

        if ($Text == '')
            $Text = $Link;

        return '<a href="' . $Link . '" target="_blank">' . $Text . '</a>';
    }

//----------------------------------------------------------------
// Disclaimer: Скрипт принципиально не сохраняет регистр! Кириллица принудительно переводится в нижний, латиница - в верхний.

// Это связано с необходимостью корректной транслитерации двуязычных названий страниц. 

// Использованная локале-независимая функция UpLow($s)

    static public function UpLow(&$string, $registr = 'up')
    {
        $upper = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $lower = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяabcdefghijklmnopqrstuvwxyz';

        if ($registr == 'up') $string = strtr($string, $lower, $upper);
        else $string = strtr($string, $upper, $lower);

    }


// Функция обратимой перекодировки кириллицы в транслит.
    static public function rus2lat($s)
    {
// Сначала всё переводим в верхний регистр, причём не с помощью глючной strtoupper
        Func::UpLow($s);
//а потом только кириллицу в нижний

        $s = str_replace("ЫА", "yha", $s);
        $s = str_replace("ЫО", "yho", $s);
        $s = str_replace("ЫУ", "yhu", $s);
        $s = str_replace("Ё", "yo", $s);
        $s = str_replace("Ж", "zh", $s);
        $rus = "АБВГДЕЗИЙКЛМНОПРСТУФХЦ";
        $lat = "abvgdezijklmnoprstufxc";
        $s = strtr($s, $rus, $lat);
        $s = str_replace("Ч", "ch", $s);
        $s = str_replace("Ш", "sh", $s);
        $s = str_replace("Щ", "shh", $s);
        $s = str_replace("Ъ", "qh", $s);
        $s = str_replace("Ы", "y", $s);
        $s = str_replace("Ь", "q", $s);
        $s = str_replace("Э", "eh", $s);
        $s = str_replace("Ю", "yu", $s);
        $s = str_replace("Я", "ya", $s);
        $s = str_replace(" ", "_", $s); // сохраняем пробел от перехода в %20
        $s = str_replace(",", ".h", $s); // сохраняем запятую
        $s = str_replace("№", "N", $s); // сохраняем N
        $s = str_replace("(", "_", $s); // сохраняем (
        $s = str_replace(")", "_", $s); // сохраняем )
//$s=str_replace(""","&quot;",$s); // сохраняем кавычки
        $s = rawurlencode($s); // Разрешённые символы URL - латинские буквы, точка, минус и подчёркивание
        return $s;
    }

//----------------------------------------------------------------
    static public function lat2rus($s)
    { // Функция обратной перекодировки транслита в кириллицу.
        $s = rawurldecode($s);
        $s = str_replace("_", ",", $s);// возвращаем запятую
        $s = str_replace("_", " ", $s);// возвращаем пробел
        $s = str_replace("_", "(", $s);// возвращаем пробел
        $s = str_replace("_", ")", $s);// возвращаем пробел
        $s = str_replace("yh", "Ы", $s);
        $s = str_replace("yu", "Ю", $s);
        $s = str_replace("ya", "Я", $s);
        $s = str_replace("yo", "Ё", $s);
        $s = str_replace("shh", "Щ", $s);
        $s = str_replace("eh", "Э", $s);
        $s = str_replace("sh", "Ш", $s);
        $s = str_replace("ch", "Ч", $s);
        $s = str_replace("qh", "Ъ", $s);
        $s = str_replace("zh", "Ж", $s);
        $lat = "abvgdezijklmnoprstufxcyq";
        $rus = "АБВГДЕЗИЙКЛМНОПРСТУФХЦЫЬ";
        $s = strtr($s, $lat, $rus);
        return $s;
    } // function lat2rus($s)
//----------------------------------------------------------------
    static public function _strip($s)
    {
        $s = str_replace(" ", " ", $s);
        return $s;
    }
}
