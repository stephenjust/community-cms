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
 * Description of DBConn
 *
 * @author Stephen
 */
class DBConn
{
    private static $instance;
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
        if (!DBConn::$instance) { DBConn::$instance = new DBConn(); 
        }
        
        return DBConn::$instance;
    }
    
    public function query($query, $params = null, $return_type = DBConn::NOTHING) 
    {
        if(empty($query)) {
            throw new Exceptions\DBException("Empty Query"); 
        }     
        try{
            $sth = $this->conn->prepare($query);
            $sth->execute($params);
            if($return_type == self::NOTHING) {
                return; 
            }
            if($return_type == self::ROW_COUNT) {
                return $sth->rowCount(); 
            }
            if($return_type == self::FETCH_ALL) {
                return $sth->fetchAll(\PDO::FETCH_ASSOC);
            }
            if($return_type == self::FETCH) {
                return $sth->fetch(\PDO::FETCH_ASSOC);
            }
        } catch (\PDOException $e){
            throw new Exceptions\DBException($e->errorInfo[0], $e->getCode(), $e);
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
    
    public function execute($sth, $return_type = DBConn::NOTHING) 
    {
        $sth->execute();
        if($return_type == self::NOTHING) {
            return; 
        }
        if($return_type == self::ROW_COUNT) {
            return $sth->rowCount(); 
        }
        if($return_type == self::FETCH_ALL) {
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        }
        if($return_type == self::FETCH) {
            return $sth->fetch(\PDO::FETCH_ASSOC);
        }
    }
    
    public function lastInsertId()
    {
        return $this->conn->lastInsertId();  
    }
}
