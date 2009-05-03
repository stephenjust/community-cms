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
 * Primary Data-Base Abstraction Layer class
 * @package CommunityCMS.database
 */
class db {
    /**
     * Data-Base system to use
     */
    var $dbms = '';
    /**
     * Name of Data-Base connection
     */
    var $db_connect_id = '';

    function sql_close() {
        // FIXME: Stub
    }
}
?>
