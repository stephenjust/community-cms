<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Component\EditBarComponent;
use CommunityCMS\Component\Block\BlockComponent;

/**
 * Generates a page
 */
class Page
{
    /**
     * Unique identifier for page
     * @var integer Page ID
     */
    public static $id = 0;
    /**
     * Marker that records whether the page exists or not
     * @var bool True if page exists, false if it does not.
     */
    public static $exists = false;
    /**
     * Unique string identifier for page
     * @var string Page Text ID
     */
    public static $text_id = null;
    /**
     * Notification to display in the notification area of the page
     * @var string
     */
    public static $notification = '';
    /**
     * How scripts should reference the page
     * @var string Either Text ID or ID
     */
    public static $url_reference = null;
    /**
     * Text to display in the page's title bar
     * @var string text
     */
    public static $title = null;
    /**
     * Page title in database
     * @var string text
     */
    public static $page_title = null;
    /**
     * True if title is to be displayed on page.
     * @var boolean
     */
    public static $showtitle = true;
    /**
     * If 'true' when display_left() called, user menu will be displayed.
     * @var boolean
     */
    public static $showlogin = true;
    /**
     * Page type
     * @var string Page type
     */
    public static $type = 'news.php';
    /**
     * Only print content when displaying page (unused)
     * @var boolean 
     */
    private static $content_only = false;
    /**
     * Stores the content of the body
     * @var string
     */
    public static $content;
    private static $blocksleft = null;
    private static $blocksright = null;
    /**
     * Page meta-description for search engines
     * @var string
     */
    private static $meta_description;

    //	/**
    //	 * Contains the hierarchial structure of page IDs
    //	 * @var array
    //	 */
    //	private static $page_hierarchy = array();
    /**
     * Contains various properties about pages that need to be accessed repeatedly
     * @var array 
     */
    private static $page_properties = array();
    
    /**
     * Set type of page to load for pages without ID
     * @param string $type Name of page type to load
     * @return void
     */
    public static function setType($type) 
    {
        switch ($type) {
        default:
            return;
          break;
        }
    }

    /**
     * set_page - Set the current page by whatever identifier is provided
     * @param mixed   $reference Numeric ID or String
     * @param boolean $is_id     If $reference is a numeric ID or special page, true; else a text ID
     * @return boolean Success
     */
    public static function setPage($reference, $is_id = true) 
    {
        if ($is_id == true) {
            if (!is_numeric($reference)) {
                // Handle special page types
                switch ($reference) {
                default:
                    // Error case
                    Debug::get()->addMessage('Unknown special page type', true);
                    Page::$exists = false;
                    Page::getPageContent();
                    return false;

                case 'change_password':
                    // Change Password
                    Page::$text_id = $reference;
                    Page::$showlogin = false;
                    Page::$url_reference = 'id=change_password';
                    Page::getSpecialPage();
                    return true;
                }
            }
            Page::$id = (int)$reference;
        } else {
            if (strlen($reference) == 0) {
                return false;
            }
            Page::$text_id = (string)$reference;
            Page::$url_reference = 'page='.Page::$text_id;
        }
        Page::getPageInformation();
        return true;
    }

    /**
     * If a page exists, collect all information about it from the database.
     * @global db $db Database connection object
     * @return void
     */
    public static function getPageInformation() 
    {
        global $db;

        // Article Page
        if (isset($_GET['showarticle'])) {
            Page::$exists = true;
            Page::$content = include ROOT.'pagetypes/news.php';
            return;
        }

        // Get either the page ID or text ID for use in the section below
        if (Page::$id > 0 && strlen(Page::$text_id) == 0) {
            Debug::get()->addMessage('Using numeric ID to get page information', false);
            $page_query_id = '`page`.`id` = '.Page::$id;
        } elseif (strlen(Page::$text_id) > 0) {
            Debug::get()->addMessage('Using text ID to get page information', false);
            $page_query_id = '`page`.`text_id` = \''.Page::$text_id.'\'';
        } else {
            return;
        }

        // Look up information (including page type) for the current page
        $page_query = 'SELECT `page`.*, `pt`.`filename`
			FROM `'.PAGE_TABLE.'` `page`, `'.PAGE_TYPE_TABLE.'` `pt`
			WHERE '.$page_query_id.'
			AND `page`.`type` = `pt`.`id`
			LIMIT 1';
        $page_handle = $db->sql_query($page_query);
        if ($db->error[$page_handle] == 1) {
            header("HTTP/1.0 404 Not Found");
            Debug::get()->addMessage('Error looking up page information', true);
            return;
        }
        if ($db->sql_num_rows($page_handle) != 1) {
            header("HTTP/1.0 404 Not Found");
            Debug::get()->addMessage('Page is not listed in database', true);
            return;
        }
        $page = $db->sql_fetch_assoc($page_handle);

        // Page was found; populate the class fields
        Page::$id = $page['id'];
        Page::$text_id = $page['text_id'];
        Page::$showtitle = ($page['show_title'] == 1) ? true : false;
        Page::$blocksleft = $page['blocks_left'];
        Page::$blocksright = $page['blocks_right'];
        Page::$exists = true;
        Page::$meta_description = $page['meta_desc'];
        Page::$type = $page['filename'];
        if (strlen(Page::$text_id) == 0) {
            Page::$url_reference = 'id='.Page::$id;
        } else {
            if(isset($_GET['id'])) {
                header("HTTP/1.1 301 Moved Permanently");
                $matches = null;
                $old_page_address = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
                preg_match('/id=[0-9]+/i', $old_page_address, $matches);
                $new_page_address = str_replace($matches, 'page='.Page::$text_id, $old_page_address);
                header('Location: '.$new_page_address);
            }
            Page::$url_reference = 'page='.Page::$text_id;
        }
        Page::$title = $page['title'];
        Page::$page_title = Page::$title;
        if(!isset(Page::$content)) {
            Page::$content = include ROOT.'pagetypes/'.Page::$type;
            if(!Page::$content) {
                // Including the pagetype file failed - either a file is missing,
                // or the included file returned 'false'
                header("HTTP/1.0 404 Not Found");
                Page::$exists = false;
                Page::$notification = '<strong>Error: </strong>System file not found.<br />';
                Debug::get()->addMessage('Including '.Page::$type.' returned false', true);
            }
        }
        return;
    }

    /**
     * Handle "special" pages (i.e. change password page)
     */
    private static function getSpecialPage() 
    {
        Page::$type = 'special.php';
        Page::$showtitle = false;
        Page::$blocksleft = null;
        Page::$blocksright = null;
        Page::$exists = true;
        Page::$meta_description = null;
        if(!isset(Page::$content)) {
            Page::$content = include ROOT.'pagetypes/'.Page::$type;
            if(!Page::$content) {
                Page::$exists = false;
                Page::$notification = '<strong>Error: </strong>System file not found.<br />';
                Debug::get()->addMessage('Including '.Page::$type.' returned false', true);
            }
        }
    }

    public static function getPageContent() 
    {
        if (Page::$exists === false) {
            Page::$title .= 'Page Not Found';
            Page::$notification .= '<strong>Error: </strong>The requested page
				could not be found.<br />';
            return;
        } else {
            return Page::$content;
        }
    }

    public static function displayPage() 
    {
        // Read template.xml for current template to figure out which order
        // to spit out content

        // If $this->content_only === true, only print the part of the template
        // with type="content"

        // FIXME: Stub
    }

    /**
     * display_header - Print the page header
     */
    public static function displayHeader() 
    {
        $template = new Template;
        $template->loadFile('header');

        $template->js_include = '<script data-main="'.ROOT.'scripts/cms" src="/scripts/require.js"></script>';

        // Include StyleSheets
        $css_include =
        '<link rel="StyleSheet" type="text/css" href="'.$template->path.'style.css" />'."\n".
        '<link rel="StyleSheet" type="text/css" href="'.$template->path.'print.css" media="print" />'."\n";
        if (DEBUG === 1) {
            $css_include .= '<link rel="StyleSheet" type="text/css" href="'.$template->path.'debug.css" />'."\n";
        }
        $template->css_include = $css_include;
        unset($css_include);

        $template->admin_include = null;
        $template->print_header = SysConfig::get()->getValue('site_name');

        // Print Meta Description if available
        $meta_desc = Page::$meta_description;
        $meta_wrapper[1] = '<meta name="description" content="';
        $meta_wrapper[2] = '" />';
        if (strlen($meta_desc) > 1) {
            $template->meta_desc = $meta_wrapper[1].$meta_desc.$meta_wrapper[2];
        } else {
            $template->meta_desc = null;
        }

        if (Page::$exists === false) {
            Page::$title .= 'Page Not Found';
        }
        Page::$title .= ' - '.SysConfig::get()->getValue('site_name');
        $template->page_title = Page::$title;
        echo $template;
        unset($template);
    }

    /**
     * nav_menu - Returns HTML for navigation menu
     * @global db $db Database object
     * @return string HTML for menu
     */
    private static function navMenu() 
    {
        global $db;

        // Prepare menu and submenu templates
        $template = new Template;
        if (!$template->loadFile('nav_bar')) {
            return false;
        }
        $menu_template = $template->splitRange('nav_menu');
        $submenu_template = $template->splitRange('nav_submenu');
        unset($template);

        // Handle main menu
        // Split template into components
        $menu_template->nav_menu_id = 'nav-menu';
        $menu_item_template = $menu_template->splitRange('menu_item');
        $cmenu_item_template = $menu_template->splitRange('current_menu_item');
        $menus_item_template = $menu_template->splitRange('menu_item_with_child');
        $cmenus_item_template = $menu_template->splitRange('current_menu_item_with_child');

        $nav_menu = PageUtil::getPagesAndChildren(0, true);

        $menu = null;
        foreach ($nav_menu AS $nav_menu_item) {
            $haschild = 0;
            if ($nav_menu_item['has_children'] == true && Page::$id == $nav_menu_item['id']) {
                $item_template = clone $cmenus_item_template;
                $haschild = 1;
            } elseif ($nav_menu_item['has_children'] == true) {
                $item_template = clone $menus_item_template;
                $haschild = 1;
            } elseif (Page::$id == $nav_menu_item['id']) {
                $item_template = clone $cmenu_item_template;
            } else {
                $item_template = clone $menu_item_template;
            }
            $pm = new PageManager($nav_menu_item['id']);
            $item_template->menu_item_url = $pm->getUrl();
            $item_template->menu_item_label = $pm->getTitle();
            $item_template->menu_item_id = 'menuitem_'.$nav_menu_item['id'];
            // Generate hidden child div
            if ($haschild == 1) {
                $item_template->child_placeholder = Page::navChildMenu($nav_menu_item['id']);
            } else {
                $item_template->child_placeholder = null;
            }
            $menu .= (string)$item_template;
            unset($item_template);
        } // FOR
        $menu_template->menu_placeholder = $menu;
        return $menu_template;
    }

    private static function navChildMenu($parent) 
    {
        global $db;

        if (!is_numeric($parent) || is_array($parent)) {
            return false;
        }
        $parent = (int)$parent;
        $return = null;

        $items_query = 'SELECT * FROM `'.PAGE_TABLE.'`
			WHERE `parent` = '.$parent.' AND `menu` = 1 ORDER BY `list` ASC';
        $items_handle = $db->sql_query($items_query);
        if ($db->error[$items_handle] == 1) {
            return false;
        }
        if ($db->sql_num_rows($items_handle) == 0) {
            return false;
        }

        // Read template
        $template = new Template();
        $template->loadFile('nav_bar');
        // Grab the sub-menu part of the template
        $sub_template = $template->splitRange('nav_submenu');
        unset($template);

        // Pull out the styles for the types of items contained within
        $item_temp = $sub_template->splitRange('menu_item');
        $currentitem_temp = $sub_template->splitRange('current_menu_item');
        $itemchild_temp = $sub_template->splitRange('menu_item_with_child');
        $currentitemchild_temp = $sub_template->splitRange('current_menu_item_with_child');

        $sub_template->nav_menu_id = 'nav-menu-sub-'.$parent;

        // Populate the menu with items
        $menu_items = null;
        for ($i = 1; $i <= $db->sql_num_rows($items_handle); $i++) {
            $items_result = $db->sql_fetch_assoc($items_handle);
            $haschild = 0;
            $extra_text = null;
            // Select the proper template
            if (Page::hasChildren($items_result['id']) === true && Page::$id !== $items_result['id']) {
                $this_item = clone $itemchild_temp;
                $this_item->child_placeholder = Page::navChildMenu($items_result['id']);
            } elseif (Page::hasChildren($items_result['id']) === true && Page::$id === $items_result['id']) {
                $this_item = clone $currentitemchild_temp;
                $this_item->child_placeholder = Page::navChildMenu($items_result['id']);
            } elseif (Page::hasChildren($items_result['id']) === false && Page::$id !== $items_result['id'])
            $this_item = clone $item_temp;
            else {
                $this_item = clone $currentitem_temp; 
            }

            $this_item->menu_item_id = 'menuitem_'.$items_result['id'];
            if ($items_result['type'] == 0) {
                $link = explode('<LINK>', $items_result['title']); // Check if menu entry is a link
                $link_path = $link[1];
                $link_name = $link[0];
                unset($link);
            } else {
                if(strlen($items_result['text_id']) > 0) {
                    $link_path = "index.php?page=".$items_result['text_id'];
                } else {
                    $link_path = "index.php?id=".$items_result['id'];
                }
                $link_name = $items_result['title'];
            } // IF is link
            $this_item->menu_item_url = $link_path;
            $this_item->menu_item_label = $link_name;
            $menu_items .= (string)$this_item;
            unset($this_item);
        }
        $sub_template->menu_placeholder = $menu_items;

        // Output the template
        return $sub_template;
    }

    /**
    * Test if there are any children to the given page
    * @global db $db Database connection object
    * @param integer $id                    Page ID of page to test
    * @param boolean $visible_children_only Only consider items that will appear in the menu
    * @return boolean
    */
    public static function hasChildren($id, $visible_children_only = false) 
    {
        global $db;

        if (!is_numeric($id) || is_array($id)) {
            return false;
        }
        $id = (int)$id;

        if (isset(Page::$page_properties[$id]['haschild']) && !$visible_children_only) {
            return Page::$page_properties[$id]['haschild'];
        } elseif (isset(Page::$page_properties[$id]['haschild_vis']) && $visible_children_only) {
            return Page::$page_properties[$id]['haschild_vis'];
        }
        
        $visible = null;
        if ($visible_children_only == true) {
            $visible = 'AND `menu` = 1 ';
        }

        $query = 'SELECT * FROM `'.PAGE_TABLE.'`
			WHERE `parent` = '.$id.' '.$visible.'LIMIT 1';
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            return false;
        }
        if ($db->sql_num_rows($handle) == 0) {
            $return = false; 
        }
        else {
            $return = true; 
        }

        if (!$visible_children_only) {
            Page::$page_properties[$id]['haschild'] = $return; 
        }
        else {
            Page::$page_properties[$id]['haschild_vis'] = $return; 
        }
        return $return;
    }

    public static function displayLeft() 
    {
        $template = new Template;
        $template->loadFile('left');
        $template->nav_bar = Page::navMenu();

        // Hide login box when it may cause issues
        if (Page::$showlogin === true) {
            $template->nav_login = Page::displayLoginBox();
        } else {
            $template->nav_login = null;
        }

        // Prepare blocks
        $left_blocks_content = null;
        $left_blocks = explode(',', Page::$blocksleft);
        foreach ($left_blocks as $block) {
            if ($block == null) {
                continue;
            }
            $left_blocks_content .= BlockComponent::getComponent(new Block($block))->render();
        }
        $template->left_content = $left_blocks_content;
        echo $template;
    }

    public static function displayRight() 
    {
        $template = new Template;
        $template->loadFile('right');

        // Prepare blocks
        $right_blocks_content = null;
        $right_blocks = explode(',', Page::$blocksright);
        foreach ($right_blocks as $block) {
            if ($block == null) {
                continue;
            }
            $right_blocks_content .= BlockComponent::getComponent(new Block($block))->render();
        }
        $template->right_content = $right_blocks_content;
        echo $template;
    }

    public static function displayContent()
    {
        global $db;

        $template = new Template;
        $template->loadFile('content');
        if (Page::$id != 0) {
            $pm = new PageManager(Page::$id);
            $template->page_path = $pm->getPath();
        } else {
            $template->page_path = Page::$page_title;
        }

        // Display the page title if the configuration says to
        if (Page::$showtitle === true) {
            $template->body_title = Page::$page_title;
            // Remove marker comments
            $template->body_title_start = null;
            $template->body_title_end = null;
        } else {
            // Remove comments referring to 'body_title'
            $template->replaceRange('body_title', null);
        }

        // Display page edit bar
        $edit_bar = new EditBarComponent();
        $edit_bar->setLabel('Page');
        if (Page::$id != 0) {
            $permission_list = array('admin_access','page_edit');
            $edit_bar->addControl(
                'admin.php?module=page&action=edit&id='.Page::$id,
                'edit.png', 'Edit', $permission_list
            );
            unset($permission_list);
        }
        $template->page_edit_bar = $edit_bar->render();

        // Display page notifications
        if (strlen(Page::$notification) > 0) {
            $template->notification = Page::$notification;
            $template->notification_start = null;
            $template->notification_end = null;
        } else {
            $template->replaceRange('notification', null);
        }

        if (Page::$type != 'special.php') {
            $pmt = new Tpl();
            $pmt->assign('pageMessage', PageMessage::getByPage(Page::$id));
            $template->page_message = $pmt->fetch('pageMessage.tpl');
        } else {
            $template->page_message = null;
        }
        $template->content = Page::getPageContent();

        // This must be done after $template->content is set because the
        // following could be used within the content.
        $template->page_id = Page::$id;
        $template->page_ref = Page::$url_reference;

        echo $template;
    }

    public static function displayFooter()
    {
        $template = new Template;
        $template->loadFile('footer');
        $template->footer = SysConfig::get()->getValue('footer');
        echo $template;
    }

    public static function displayDebug()
    {
        global $db;

        $template = new Template;
        $template->loadFile('debug');
        $template->debug_queries = $db->print_queries();
        $template->debug_query_stats = $db->print_query_stats();
        $template->debug_log = Debug::get()->displayTraces();
        echo $template;
    }

    /**
    * displayLoginBox - Generate and return content of login box area
    * @global db $db
    * @return string
    */
    public static function displayLoginBox()
    {
        $tpl = new Tpl();
        $tpl->assign("login_target", "index.php?{$_SERVER['QUERY_STRING']}&amp;login=1");
        $tpl->assign("logout_target", "index.php?{$_SERVER['QUERY_STRING']}&amp;login=2");
        $tpl->assign("change_password_target", "index.php?id=change_password");
        $tpl->assign("admin_target", "admin.php");
        $tpl->assign("admin_status", acl::get()->check_permission("admin_access"));
        $tpl->assign("user", (isset($_SESSION['name'])) ? $_SESSION['name'] : "Anonymous");
        if (!UserSession::get()->logged_in) {
            return $tpl->fetch("login.tpl");
        } else {
            return $tpl->fetch("userbox.tpl");
        }
    }
}
