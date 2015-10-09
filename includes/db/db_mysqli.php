<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.database
 */

namespace CommunityCMS;
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * Data-Base Abstraction Layer class for MySQLi
 * @package CommunityCMS.database
 */
class db_mysqli extends db
{
    function __construct() 
    {
        $this->dbms = 'mysqli';
    }
    function sql_connect() 
    {
        $this->db_connect_id = 'mysqli';
        $this->connect = mysqli_connect(
            Config::DB_HOST, Config::DB_USER,
            Config::DB_PASS, Config::DB_NAME, Config::DB_HOST_PORT
        );
        return $this->connect;
    }
    function sql_server_info() 
    {
        return mysqli_get_server_info($this->connect).' (mysqli)';
    }
    function sql_query($query) 
    {
        if (is_bool($this->connect)) {
            $this->error[$this->query_count] = 1;
        }
        $this->query[$this->query_count] = mysqli_query($this->connect, $query);
        if (!$this->query[$this->query_count]) {
            $this->error[$this->query_count] = 1;
            $this->errormsgs[$this->query_count] = $this->_print_error_query();
        } else {
            $this->error[$this->query_count] = 0;
        }
        $this->query_text[$this->query_count] = $query;
        return $this->query_count++;
    }
    function sql_num_rows($query) 
    {
        return mysqli_num_rows($this->query[$query]);
    }
    function sql_fetch_assoc($query) 
    {
        return mysqli_fetch_assoc($this->query[$query]);
    }
    function sql_escape_string($string) 
    {
        assert($this->connect);
        return mysqli_real_escape_string($this->connect, $string);
    }

    /**
     * sql_close (mysqli) - Close the MySQLi connection
     */
    function sql_close() 
    {
        mysqli_close($this->connect);
    }
    function _print_error_query($query = null) 
    {
        return mysqli_error($this->connect);
    }
}
