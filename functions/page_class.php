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
    public $page_id;
    /**
     * Marker that records whether the page exists or not
     * @var int Set if page exists, unset if it does not.
     */
    public $page_exists;
    /**
     * Unique string identifier for page
     * @var string Page Text ID
     */
    public $page_text_id;
    /**
     * Notification to display in the notification area of the page
     * @var string 
     */
    public $notification;
    function __construct() {
        $this->page_id = NULL;
        $this->page_text_id = NULL;
        $this->notification = '';
    }
    function __destruct() {

    }
    function __get($name) {
        return $this->$name;
    }
    function __set($name,$value) {
        switch($name) {
            default:
                $this->$name = $value;
                break;
            case 'page_id':
                $this->$name = (int)$value;
                $this->get_page_information();
                break;
            case 'page_text_id':
                $this->$name = (string)$value;
                $this->get_page_information();
                break;
        }
    }
    /**
     * If a page exists, collect all information about it from the database.
     * @global resource $db
     * @global array $CONFIG
     * @return void
     */
    private function get_page_information() {
        global $db;
        global $CONFIG;
        if (isset($this->page_id)) {
            if ($this->page_id == 0) {
                return;
            }
            $page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE
                `id` = '.$this->page_id.' LIMIT 1';
            $page_handle = $db->query($page_query);
            if (!$page_handle) {
                return;
            }
            if ($page_handle->num_rows != 1) {
                return;
            }
            $page = $page_handle->fetch_assoc();
            $this->page_text_id = $page['text_id'];
        } elseif (isset($this->page_text_id)) {
            if (strlen($this->page_text_id) == 0) {
                return;
            }
            $page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE
                `text_id` = '.$this->page_text_id.' LIMIT 1';
            $page_handle = $db->query($page_query);
            if (!$page_handle) {
                return;
            }
            if ($page_handle->num_rows != 1) {
                return;
            }
            $page = $page_handle->fetch_assoc();
            $this->page_id = $page['id'];
        } else {
            return;
        }
        $this->page_exists = 1;
        return;
    }
	function get_page_content($id,$type = 1,$view = "") {
		if($type == "") {
			$type = 0;
			}
		$id = (int)$id;
		global $CONFIG;
		global $db;
		global $NOTIFICATION;
		if(isset($_POST['vote']) && isset($_POST['vote_poll'])) {
			$question_id = $_POST['vote_poll'];
			$answer_id = $_POST['vote'];
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$query = 'INSERT INTO '.$CONFIG['db_prefix'].'poll_responses (question_id ,answer_id ,value ,ip_addr) VALUES ('.$question_id.', '.$answer_id.', NULL, \''.ip2long($user_ip).'\');';
			$handle = $db->query($query);
			if(!$handle) {
				$NOTIFICATION .= 'Failed to submit your vote.<br />';
				} else {
				$NOTIFICATION .= 'Thank you for voting.<br />';
				}
			}
		$page_type_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pagetypes WHERE id = '.$type.' LIMIT 1';
		$page_type_handle = $db->query($page_type_query);
		try {
			if($page_type_handle->num_rows == 1) {
				$page_type = $page_type_handle->fetch_assoc();
				$page = include(ROOT.'pagetypes/'.$page_type['filename']);
				} else {
				header("HTTP/1.0 404 Not Found");
				global $page_not_found;
				$page_not_found = 1;
				throw new Exception('Page not found.');
				}
			}
		catch(Exception $e) {
			$NOTIFICATION .= '<b>Error:</b> '.$e->getMessage();
			$page = NULL;
			}
		return $page;
		}
}
?>
