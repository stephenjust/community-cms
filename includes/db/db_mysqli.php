<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.database
 */
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
        global $CONFIG;
        $this->db_connect_id = 'mysqli';
        $this->connect = mysqli_connect(
            $CONFIG['db_host'], $CONFIG['db_user'],
            $CONFIG['db_pass'], $CONFIG['db_name'], $CONFIG['db_host_port']
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
    function sql_affected_rows($query) 
    {
        return mysqli_affected_rows($this->connect);
    }
    function sql_fetch_assoc($query) 
    {
        return mysqli_fetch_assoc($this->query[$query]);
    }
    function sql_fetch_row($query) 
    {
        return mysqli_fetch_row($this->query[$query]);
    }
    function sql_escape_string($string) 
    {
        assert($this->connect);
        return mysqli_real_escape_string($this->connect, $string);
    }
    function sql_insert_id($table,$field) 
    {
        $query = 'SELECT MAX(`'.$field.'`) AS `id` FROM `'.$table.'`';
        $handle = $this->sql_query($query);
        if ($this->error[$handle] === 1) {
            return false;
        }
        if ($this->sql_num_rows($handle) != 1) {
            return false;
        }
        $result = $this->sql_fetch_assoc($handle);
        return $result['id'];
    }

    function sql_prepare($name,$query) 
    {
        global $debug;
        // Validate parameters
        if (!is_string($name)) {
            $debug->addMessage('$name is not a string', true);
            return false;
        }
        if (!is_string($query)) {
            $debug->addMessage('$query is not a string', true);
            return false;
        }
        $prepare_query = 'PREPARE `'.$name.'` FROM "'.$query.'"';
        $prepare_handle = $this->sql_query($prepare_query);
        if (!$this->error[$prepare_handle] === 1) {
            $debug->addMessage('Failed to create prepared statement', true);
            return false;
        }
        return true;
    }
    function sql_prepare_exec($name,$variables,$datatypes) 
    {
        global $debug;
        // Validate parameters
        if (!is_string($name)) {
            $debug->addMessage('$name is not a string', true);
            return false;
        }
        if (!is_array($variables)) {
            $debug->addMessage('$variables is not an array', true);
            return false;
        }
        if (!is_array($datatypes)) {
            $debug->addMessage('$datatypes is not an array', true);
            return false;
        }
        if (count($variables) !== count($datatypes)) {
            $debug->addMessage('Length of $variables and $datatypes are not equal', true);
            return false;
        }
        // Add quotation marks to strings
        foreach ($variables as $var) {
            if (!is_numeric($var)) {
                $var = '"'.$var.'"';
            }
        }
        unset($var);
        $variables_string = implode(', ', $variables);
        $exec_query = 'EXECUTE `'.$name.'` USING "'.$variables_string.'"';
        $exec_handle = $this->sql_query($exec_query);
        if ($this->error[$exec_handle] === 1) {
            $debug->addMessage('Failed to execute prepared statement', true);
            return false;
        }
        return true;
    }
    function sql_prepare_close($name) 
    {
        global $debug;
        if (!is_string($name)) {
            $debug->addMessage('$name is not a string', true);
            return false;
        }
        $prepare_close_query = 'DEALLOCATE PREPARE `'.$name.'`';
        $prepare_close_handle = $this->sql_query($prepare_close_query);
        if ($this->error[$prepare_close_handle] === 1) {
            $debug->addMessage('Failed to deallocate prepared statement', true);
            return false;
        }
        return true;
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
?>
