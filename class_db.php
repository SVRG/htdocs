<?php

/**
 * Database wrapper for a MySQL with PHP tutorial
 *
 * @copyright Eran Galperin
 * @license MIT License
 * @see http://www.binpress.com/tutorial/using-php-with-mysql-the-right-way/17
 */
include_once "class_config.php";
include_once "class_func.php";

class Db
{
    // The database connection
    protected static $connection;

    public $res; // Data
    public $last_id = 0; // Last inserted ID
    public $last_query = "empty";

    public $cnt = 0; // Количество записей

    private $config = array();
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Db constructor.
     */
    public function __construct()
    {
        $this->config = config::$mysql_config;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Connect to the database
     *
     * @return mysqli|bool MySQLi object instance on success / bool false on failure
     */
    public function connect()
    {

        // Try and connect to the database
        if (!isset(self::$connection)) {
            self::$connection = new mysqli('localhost', $this->config['username'], $this->config['password'], $this->config['dbname']);
        }

        // If connection was not successful, handle the error
        if (self::$connection === false) {
            // Handle error - notify administrator, log to a file, show an error screen, etc.
            return false;
        }
        return self::$connection;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Query the database
     *
     * @param $query - query string
     * @param int $echo вывод запроса
     * @return mixed the result of the mysqli::query() function
     */
    public function query($query, $echo = 0)
    {
        // Connect to the database
        $connection = $this->connect();

        // Для обеспечения совместимости кодировок
        $connection->query("SET NAMES 'utf8'"); // cp1251 - для Win

        $query = preg_replace('/\s\s+/', ' ', $query); // В запросе удаляем лишние пробелы

        // Query the database
        $result = $connection->query($query);
        $this->last_query = $query;

        // Обновляем последний добавленный номер
        $this->last_id = $connection->insert_id;

        $this->cnt = 0;
        if (isset($result->field_count))
            $this->cnt = $result->field_count;

        if ($echo == 1)
            exit("$query");

        // Если обновление или добавление то записываем в Лог
        if ((strpos($query, 'UPDATE') !== false) or (strpos($query, 'INSERT') !== false)) {

            $user = func::user();

            // todo - Подумать как в INSERT / UPDATE запросах удалять пробелы в значениях '_VALUE_'

            $safe_str = addslashes($query);
            $connection->query(/** @lang MySQL */
                "INSERT INTO log(log,user) VALUES ('$safe_str','$user');");
        }

        return $result;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Fetch rows from the database (SELECT query)
     *
     * @param string|$query The query string
     * @param int $echo
     * @return array Database rows on success/ bool False on failure
     */
    public function rows($query = "", $echo = 0)
    {
        if ($echo == 1)
            echo $query;

        $rows = array();
        $this->res = $this->query($query);
        if ($this->res === false) {
            $this->cnt = 0;
            return $rows;
        }
        while ($row = $this->res->fetch_assoc()) {
            $rows[] = $row;
        }
        $this->cnt = $this->res->num_rows;

        return $rows;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Fetch the last error from the database
     *
     * @return string Database error message
     */
    public function error()
    {
        $connection = $this->connect();
        return $connection->error;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Quote and escape value for use in a database query
     *
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    public function real_escape_string($value)
    {
        $value = ltrim($value);
        $value = rtrim($value);
        $connection = $this->connect();
        return $connection->real_escape_string($value);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Формирует строку array_string для выбранной записи
     * @param $table
     * @param $key_field
     * @param $key_value
     * @return string
     */
    public static function getHistoryString($table, $key_field, $key_value)
    {
        if (!isset($table, $key_field, $key_value))
            return "";

        $key_value = (int)$key_value;

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM $table WHERE $key_field=$key_value;");

        if ($db->cnt == 0)
            return "";

        $row = $rows[0];
        $res = serialize($row);

        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "INSERT INTO history (table_name, key_field_name, key_field_value, ser_array, kod_user) VALUES('$table','$key_field',$key_value,'$res',$kod_user);");

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
}