<?php

/**
 * Database wrapper for a MySQL with PHP tutorial
 *
 * @copyright Eran Galperin
 * @license MIT License
 * @see http://www.binpress.com/tutorial/using-php-with-mysql-the-right-way/17
 */
class Db
{
    // The database connection
    protected static $connection;

    public $res; // Data
    public $last_id = 0; // Last inserted ID
    public $last_query = "empty";

    public $cnt = 0; // Количество записей

    private $config = array("username"=>"root","password"=>"", "dbname"=>"trin");

//----------------------------------------------------------------------------------------------------------------------
    /**
     * Connect to the database
     *
     * @return mysqli MySQLi object instance on success / bool false on failure
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
            //return false;
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
    public function query($query,$echo=0)
    {
        // Connect to the database
        $connection = $this->connect();

        // Для обеспечения совместимости кодировок
        $connection->query("SET NAMES 'utf8'"); // cp1251 - для Win

        $query = preg_replace('/\s\s+/', ' ', $query); // В запросе удаляем лишние пробелы
        // todo - Подумать как в INSERT / UPDATE запросах удалять пробелы в значениях '_VALUE_'

        // Query the database
        $result = $connection->query($query);
        $this->last_query = $query;

        // Обновляем последний добавленный номер
        $this->last_id = $connection->insert_id;
        if(isset($result->num_rows))
            $this->cnt = $result->num_rows;
        else
            $this->cnt = 1;

        if($echo==1)
            echo $query;

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
    public function rows($query="",$echo=0)
    {
        if($echo==1)
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
    public function quote($value)
    {
        $connection = $this->connect();
        return "'" . $connection->real_escape_string($value) . "'";
    }

//----------------------------------------------------------------------------------------------------------------------
}