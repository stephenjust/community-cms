<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0

/**#@+
 * @ignore
 */
define('SECURITY', 1);
define('ROOT', '../../');
/**#@-*/
require_once ROOT.'vendor/autoload.php';
require ROOT.'include.php';
$referer = $_SERVER['HTTP_REFERER'];
if(preg_match('#/$#', $referer)) {
    $referer .= 'index';
}
$referer_directory = dirname($referer);
if($referer_directory == "") {
    die('Security breach.');
}

$current_directory = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
if(!$current_directory == $referer_directory.'/admin/scripts') {
    die('Security breach');
}

initialize();
if (!isset($_GET['page'])) {
    die('Error: Page not set.<br />');
}
if (!isset($_GET['left']) || !isset($_GET['right'])) {
    die('Error: Blocks not set.<br />');
}

if ($_GET['left'] == '0' || $_GET['right'] == '0') {
    if (DEBUG === 1) { echo 'Loading initial values...<br />'; 
    }
    $blocks_query = 'SELECT `blocks_left`,`blocks_right` FROM `'.PAGE_TABLE.'`
		WHERE `id` = '.(int)$_GET['page'].' LIMIT 1';
    $blocks_handle = $db->sql_query($blocks_query);
    if ($db->error[$blocks_handle] === 1) {
        die ('Failed to load block information.');
    }
    if ($db->sql_num_rows($blocks_handle) != 1) {
        die ('The selected page does not exist.');
    }
    $blocks_result = $db->sql_fetch_assoc($blocks_handle);
    $blocks_left = $blocks_result['blocks_left'];
    $blocks_right = $blocks_result['blocks_right'];
    unset($blocks_query);
    unset($blocks_handle);
    unset($blocks_result);
} else {
    $blocks_left = $_GET['left'];
    $blocks_right = $_GET['right'];
}
// Get list of blocks available to add
$block_list_query = 'SELECT * FROM `'.BLOCK_TABLE.'`';
$block_list_handle = $db->sql_query($block_list_query);
if ($db->error[$block_list_handle] === 1) {
    die ('Failed to read block list');
}
if ($db->sql_num_rows($block_list_handle) == 0) {
    echo 'No blocks available to add.<br />';
} else {
    echo 'Add: <select id="adm_add_block_list">';
    for ($i = 1; $i <= $db->sql_num_rows($block_list_handle); $i++) {
        $block_list = $db->sql_fetch_assoc($block_list_handle);
        $attribute_list = ($block_list['attributes'] == '') ? null : ' ('.$block_list['attributes'].')';
        echo '<option value="'.$block_list['id'].'">'.$block_list['type'].$attribute_list.'</option>';
    }
    echo '</select><br />';
}

if (DEBUG === 1) { echo 'Left: '.$blocks_left.' /Right: '.$blocks_right.'<br />'; 
}

// Turn the lists into arrays
$blocks_left = csv2array($blocks_left);
$blocks_right = csv2array($blocks_right);

// Function to generate tables to add/remove blocks
function block_table($current_blocks, $side) 
{
    global $db;
    $return = '<a href="#" onClick="block_list_add(0,\''.$side.'\')"><img src="admin/templates/default/images/add.png" width="16px" height="16px" border="0"></a><br />';

    $pos = 1;
    $rmpos = 0;
    foreach ($current_blocks as $block) {
        $block_info_query = 'SELECT * FROM `'.BLOCK_TABLE.'` WHERE `id` = '.(int)$block.' LIMIT 1';
        $block_info_handle = $db->sql_query($block_info_query);
        if ($db->error[$block_info_handle] === 1) {
            die ('ERROR: Failed to read block information.');
        }
        if ($db->sql_num_rows($block_info_handle) != 1) {
            echo 'Block '.$block.' does not exist.
				<span id="adm_block_'.$rmpos.'" style="display:none;">'.$block.'</span>
				<a href="#" onClick="block_list_remove('.$rmpos.',\''.$side.'\')">
				<img src="admin/templates/default/images/delete.png" width="16px" height="16px" border="0">
				</a><br />';
            $rmpos++;
            continue;
        }
        $block_info = $db->sql_fetch_assoc($block_info_handle);
        $attribute_list = ($block_info['attributes'] == '') ? null : ' ('.$block_info['attributes'].')';
        $return .= $block_info['type'].$attribute_list.' 
			<span id="adm_block_'.$rmpos.'" style="display:none;">'.$block.'</span>
			<a href="#" onClick="block_list_remove('.$rmpos.',\''.$side.'\')"><img src="admin/templates/default/images/delete.png" width="16px" height="16px" border="0"></a><br />
			<a href="#" onClick="block_list_add('.$pos.',\''.$side.'\')"><img src="admin/templates/default/images/add.png" width="16px" height="16px" border="0"></a><br />';
        unset($block);
        $pos++;
        $rmpos++;
    }

    return $return;
}

// Start drawing the block list
echo '<table width="100%"><tr>'."\n";
// Left list
echo '<td width="50%">Left:<br />'."\n";
echo block_table($blocks_left, 'left');
echo '</td>'."\n";

// Right list
echo '<td width="50%">Right:<br />'."\n";
echo block_table($blocks_right, 'right');
echo '</td>'."\n";
echo '</tr></table>'."\n";


echo '<input type="hidden" name="blocks_left" id="adm_blocklist_left" value="'.array2csv($blocks_left).'" />'."\n";
echo '<input type="hidden" name="blocks_right" id="adm_blocklist_right" value="'.array2csv($blocks_right).'" />'."\n";
clean_up();
?>