<?php
/**
 * Community CMS
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * generate a page
 *
 * This class does ...
 *
 * @author stephen
 */
class page {
    /**
     * Unique identifier for page
     * @var int Page ID
     */
    public $id = NULL;
    /**
     * Marker that records whether the page exists or not
     * @var bool True if page exists, false if it does not.
     */
    public $exists = 0;
    /**
     * Unique string identifier for page
     * @var string Page Text ID
     */
    public $text_id = NULL;
    /**
     * Notification to display in the notification area of the page
     * @var string 
     */
    public $notification = '';
    /**
     * How scripts should reference the page
     * @var string Either Text ID or ID
     */
    public $url_reference = NULL;
    /**
     * Text to display at the top of a page.
     * @var string Text
     */
    public $title = NULL;
    /**
     * Page type
     * @var string Page type
     */
    public $type = 'news';
    function __construct() {

    }
    function __destruct() {

    }
    /**
     * Set type of page to load for pages without ID
     * @param string $type Name of page type to load
     * @return void
     */
    public function set_type($type) {
        switch ($type) {
            default:
                return;
                break;
            case 'settings_main':
                $this->type = 'settings_main.php';
                if(!checkuser(1)) {
                    return;
                }
                $this->exists = 1;
                $this->title = 'Settings - Main';
                break;
            case 'settings_profile':
                $this->type = 'settings_profile.php';
                if(!checkuser(1)) {
                    return;
                }
                $this->exists = 1;
                $this->title = 'Settings - Profile';
                break;
        }
    }
    /**
     * Set the page's ID
     * @param int $id Page id
     * @return void
     */
    public function set_id($id) {
        if($this->id == $id) {
            return;
        }
        $this->id = (int)$id;
        $this->get_page_information();
        return;
    }
    /**
     * Set the page's Text ID
     * @param string $id Text ID
     * @return void 
     */
    public function set_text_id($id) {
        if($this->text_id == $id) {
            return;
        }
        if(strlen($id) > 1) {
            $this->text_id = (string)$id;
            $this->get_page_information();
        }
        return;
    }
    /**
     * If a page exists, collect all information about it from the database.
     * @global resource $db Database connection resource
     * @global array $CONFIG Array of configuration information
     * @return void
     */
    public function get_page_information() {
        global $db;
        global $CONFIG;
        if (isset($this->id)) {
            if ($this->id == 0) {
                return;
            }
            $page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE
                `id` = "'.$this->id.'" LIMIT 1';
            $page_handle = $db->query($page_query);
            if (!$page_handle) {
                return;
            }
            if ($page_handle->num_rows != 1) {
                return;
            }
            $page = $page_handle->fetch_assoc();
            $this->text_id = $page['text_id'];
            $this->exists = 1;
        } elseif (isset($this->text_id)) {
            if (strlen($this->text_id) == 0) {
                return;
            }
            $page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE
                `text_id` = "'.$this->text_id.'" LIMIT 1';
            $page_handle = $db->query($page_query);
            if (!$page_handle) {
                return;
            }
            if ($page_handle->num_rows != 1) {
                return;
            }
            $page = $page_handle->fetch_assoc();
            $this->id = $page['id'];
            $this->exists = 1;
        } else {
            return;
        }
        if (strlen($this->text_id) < 1) {
            $this->url_reference = 'id='.$this->id;
        } else {
            $this->url_reference = 'page='.$this->text_id;
        }
        $this->title = $page['title'];
        $page_type_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pagetypes
            WHERE id = '.$page['type'].' LIMIT 1';
        $page_type_handle = $db->query($page_type_query);
        if(!$page_type_handle) {
            return;
        }
        $page_type = $page_type_handle->fetch_assoc();
        $this->type = $page_type['filename'];
        return;
    }
    public function get_page_content() {
        global $db;
        global $CONFIG;
        if ($this->exists == 0) {
            header("HTTP/1.0 404 Not Found");
            $this->title .= 'Page Not Found';
            $this->notification .= '<strong>Error: </strong>The requested page
                could not be found.<br />';
            return;
        } else {
            if (!isset($_GET['view'])) {
                $_GET['view'] = NULL;
            }
            return get_page_content($this->id,$this->type,$_GET['view']);
            // FIXME: Stub;
            return;
        }
    }
    public function display_header() {
        // FIXME: Stub
    }
    public function display_footer() {
        // FIXME: Stub
    }
}
?>
