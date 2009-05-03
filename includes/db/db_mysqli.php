<?php
/**
 * Community CMS
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.database
 */
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * Data-Base Abstraction Layer class for MySQLi
 * @package CommunityCMS.database
 */
class db_mysqli extends db {
    function __construct() {
        $this->dbms = mysqli;
    }
    function sql_connect() {
        // FIXME: Stub
    }
    function sql_server_info() {
        // FIXME: Stub
    }
    function _sql_close() {
        // FIXME: Stub
    }
}
?>
