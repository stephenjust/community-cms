<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.database
 */
namespace CommunityCMS;
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}


/**
 * Data-Base Abstraction Layer class for PostgreSQL
 * @package CommunityCMS.database
 */
class db_postgresql extends db
{
    function __construct() 
    {
        $this->dbms = 'postgresql';
    }
    function sql_connect() 
    {
        $this->db_connect_id = 'pgsql';
        $this->connect = pg_connect(
            'host='.Config::DB_HOST.'port='.Config::DB_HOST_PORT.' '.
            'dbname='.Config::DB_NAME.' user='.Config::DB_USER.' password='.Config::DB_PASS
        );
        return $this->connect;
    }
    function sql_server_info() 
    {
        $v = pg_version($this->connect);
        return $v['client'].' (postgresql)';
    }
    function sql_query($query) 
    {
        // Remove comments
        $query = preg_replace('/\-\-.+\n/', null, $query);
        // Replace any backtick that is not escaped
        $query = preg_replace('/(?<!\\\\)`/', '"', $query);
        $this->query[$this->query_count] = pg_query($this->connect, $query);
        if (!$this->query[$this->query_count]) {
            $this->error[$this->query_count] = 1;
        } else {
            $this->error[$this->query_count] = 0;
        }
        $this->query_text[$this->query_count] = $query;
        return $this->query_count++;
    }
    function sql_num_rows($query) 
    {
        return pg_num_rows($this->query[$query]);
    }
    function sql_affected_rows($query) 
    {
        return pg_affected_rows($this->query[$query]);
    }
    function sql_fetch_assoc($query) 
    {
        return pg_fetch_assoc($this->query[$query]);
    }
    function sql_fetch_row($query) 
    {
        return pg_fetch_row($this->query[$query]);
    }
    function sql_escape_string($string) 
    {
        return pg_escape_string($this->connect, $string);
    }
    function sql_insert_id($table,$field) 
    {
        $query = 'SELECT currval("'.$table.'_'.$field.'_seq")';
        $handle = $this->sql_query($query);
        if ($this->error[$handle] === 1) {
            return false;
        }
        if ($this->sql_num_rows($handle) != 1) {
            return false;
        }
        $result = $db->sql_fetch_assoc($handle);
        return $result[0];
    }

    function sql_prepare($name,$query) 
    {
        // Validate parameters
        if (!is_string($name)) {
            Debug::get()->addMessage('$name is not a string', true);
            return false;
        }
        if (!is_string($query)) {
            Debug::get()->addMessage('$query is not a string', true);
            return false;
        }
        // Replace ? with $[num]
        $param_count = substr_count($query, ' ?');
        for ($i = 1; $i <= $param_count; $i++) {
            $query = str_replace_count('?', '$'.$i, $query, 1);
        }
        $prepare_query = 'PREPARE `'.$name.'` AS '.$query;
        $prepare_handle = $this->sql_query($prepare_query);
        if (!$this->error[$prepare_handle] === 1) {
            Debug::get()->addMessage('Failed to create prepared statement', true);
            return false;
        }
        return true;
    }
    function sql_prepare_exec($name,$variables,$datatypes) 
    {
        // Validate parameters
        if (!is_string($name)) {
            Debug::get()->addMessage('$name is not a string', true);
            return false;
        }
        if (!is_array($variables)) {
            Debug::get()->addMessage('$variables is not an array', true);
            return false;
        }
        if (!is_array($datatypes)) {
            Debug::get()->addMessage('$datatypes is not an array', true);
            return false;
        }
        if (count($variables) !== count($datatypes)) {
            Debug::get()->addMessage('Length of $variables and $datatypes are not equal', true);
            return false;
        }
        for ($i = 0; $i < count($variables); $i++) {
            if (!is_numeric($variables[$i])) {
                $variables[$i] = '\''.$variables[$i].'\'';
            }
        }
        unset($var);
        $variables_string = implode(', ', $variables);
        $exec_query = 'EXECUTE '.$name.'('.$variables_string.')';
        $exec_handle = $this->sql_query($exec_query);
        if ($this->error[$exec_handle] === 1) {
            Debug::get()->addMessage('Failed to execute prepared statement', true);
            return false;
        }
        return true;
    }
    function sql_prepare_close($name) 
    {
        if (!is_string($name)) {
            Debug::get()->addMessage('$name is not a string', true);
            return false;
        }
        $prepare_close_query = 'DEALLOCATE PREPARE \''.$name.'\'';
        $prepare_close_handle = $this->sql_query($prepare_close_query);
        if ($this->error[$prepare_close_handle] === 1) {
            Debug::get()->addMessage('Failed to deallocate prepared statement', true);
            return false;
        }
        return true;
    }

    function sql_close() 
    {
        pg_close($this->connect);
    }
    function _print_error_query($query) 
    {
        return pg_last_error($this->connect);
    }
}
