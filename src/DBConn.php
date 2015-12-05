<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.database
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2013 Glenn De Jonghe
 * @copyright 2014-2015 Stephen Just
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Class to manage connections to a database through PDO
 *
 * @author Stephen
 */
class DBConn
{
    private $all_queries = [];
    private $failed_queries = [];

    /**
     * Shared instance of the class
     * @var DBConn
     */
    private static $instance;

    /**
     * PDO Connection
     * @var \PDO
     */
    private $conn;

    const NOTHING = 1;
    const FETCH_ALL = 2;
    const FETCH = 3;
    const ROW_COUNT = 4;

    private function __construct()
    {
        $conn_string = sprintf("mysql:host=%s;dbname=%s", Config::DB_HOST, Config::DB_NAME);
        $this->conn = new \PDO($conn_string, Config::DB_USER, Config::DB_PASS);
        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->conn->exec("set names utf8");
    }

    /**
     * Get the DBConn instance
     * @return DBConn
     */
    public static function get()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Execute an SQL query
     * @param string $query SQL query
     * @param array $params Query parameters
     * @param int $return_type
     * @return mixed Depends on return_type
     * @throws Exceptions\DBException
     */
    public function query($query, array $params = null, $return_type = self::NOTHING)
    {
        if (empty($query)) {
            throw new Exceptions\DBException("Empty Query");
        }
        if (defined("DEBUG")) {
            $this->all_queries[] = $query;
        }
        try {
            $sth = $this->conn->prepare($query);
            $sth->execute($params);
            switch ($return_type) {
                case self::NOTHING:
                    return;
                case self::ROW_COUNT:
                    return $sth->rowCount();
                case self::FETCH_ALL:
                    return $sth->fetchAll(\PDO::FETCH_ASSOC);
                case self::FETCH:
                    return $sth->fetch(\PDO::FETCH_ASSOC);
            }
        } catch (\PDOException $ex) {
            $this->failed_queries[] = $query;
            throw new Exceptions\DBException($ex->getMessage(), (int) $ex->getCode(), $ex);
        }
    }

    /**
     * Prepare a PDO statement
     * @param string $stmt
     * @return \PDOStatement
     */
    public function prepare($stmt)
    {
        return $this->conn->prepare($stmt);
    }

    /**
     * Execute a PDO statement
     * @param \PDOStatement $sth
     * @param int $return_type
     * @return mixed Depends on return_type
     */
    public function execute(\PDOStatement $sth, $return_type = self::NOTHING)
    {
        $sth->execute();
        switch ($return_type) {
            case self::NOTHING:
                return;
            case self::ROW_COUNT:
                return $sth->rowCount();
            case self::FETCH_ALL:
                return $sth->fetchAll(\PDO::FETCH_ASSOC);
            case self::FETCH:
                return $sth->fetch(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * Get the ID of the previously inserted record
     * @return string
     */
    public function lastInsertId()
    {
        return $this->conn->lastInsertId();
    }

    /**
     * Get information about the sql server
     * @return string
     */
    public function serverInfo()
    {
        return $this->conn->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Get all executed and failed queries
     * @return array
     */
    public function getQueryHistory()
    {
        return [
            "all" => $this->all_queries,
            "failed" => $this->failed_queries
        ];
    }
}
