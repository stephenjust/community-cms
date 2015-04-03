<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @copyright Copyright (C) 2013 Glenn De Jonghe
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt
 * @author    stephenjust@gmail.com
 * @package   CommunityCMS.main
 */

require_once ROOT.'config.php';

class DBException extends Exception
{
}

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
        global $CONFIG;
        $conn_string = sprintf("mysql:host=%s;dbname=%s", $CONFIG['db_host'], $CONFIG['db_name']);
        $this->conn = new PDO($conn_string, $CONFIG['db_user'], $CONFIG['db_pass']);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->exec("set names utf8");
    }
    
    /**
     * Get the DBConn instance
     * @return \DBConn
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
            throw new DBException("Empty Query"); 
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
                return $sth->fetchAll(PDO::FETCH_ASSOC); 
            }
            if($return_type == self::FETCH) {
                return $sth->fetch(PDO::FETCH_ASSOC); 
            }
        } catch (PDOException $e){
            throw new DBException($e->errorInfo[0]);
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
            return $sth->fetchAll(PDO::FETCH_ASSOC); 
        }
        if($return_type == self::FETCH) {
            return $sth->fetch(PDO::FETCH_ASSOC); 
        }
    }
    
    public function lastInsertId()
    {
        return $this->conn->lastInsertId();  
    }
}

?>
