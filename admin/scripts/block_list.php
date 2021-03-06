<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

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
if (FormUtil::get('left') == '0' || FormUtil::get('right') == '0') {
    $query = "SELECT `blocks_left`, `blocks_right` "
        . "FROM `".PAGE_TABLE."` "
        . "WHERE `id` = :id";
    $result = DBConn::get()->query($query, [":id" => FormUtil::get('page')], DBConn::FETCH);
    if (!$result) {
        die ('The selected page does not exist.');
    }
    $blocks_left = $result['blocks_left'];
    $blocks_right = $result['blocks_right'];
} else {
    $blocks_left = FormUtil::get('left');
    $blocks_right = FormUtil::get('right');
}

// Get list of blocks available to add
$block_list_query = 'SELECT * FROM `'.BLOCK_TABLE.'`';
try {
    $results = DBConn::get()->query($block_list_query, [], DBConn::FETCH_ALL);
} catch (Exceptions\DBException $ex) {
    die ('Failed to read block list');
}
if (count($results) == 0) {
    echo 'No blocks available to add.<br />';
} else {
    echo 'Add: <select id="adm_add_block_list">';
    foreach ($results as $block_list) {
        $attribute_list = ($block_list['attributes'] == '') ? null : ' ('.$block_list['attributes'].')';
        echo '<option value="'.$block_list['id'].'">'.$block_list['type'].$attribute_list.'</option>';
    }
    echo '</select><br />';
}

if (DEBUG === 1) {
    echo 'Left: '.$blocks_left.' /Right: '.$blocks_right.'<br />';
}

// Turn the lists into arrays
$blocks_left = explode(',', $blocks_left);
$blocks_right = explode(',', $blocks_right);

// Function to generate tables to add/remove blocks
function block_table(array $current_blocks, $side)
{
    $return = '<a href="#" onClick="block_list_add(0,\''.$side.'\')"><img src="admin/templates/default/images/add.png" width="16px" height="16px" border="0"></a><br />';

    $pos = 1;
    $rmpos = 0;
    foreach ($current_blocks as $block) {
        $block_info_query = 'SELECT * FROM `'.BLOCK_TABLE.'` WHERE `id` = :id LIMIT 1';
        try {
            $block_info = DBConn::get()->query($block_info_query, [":id" => $block], DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            die ('ERROR: Failed to read block information.');
        }
        if (!$block_info) {
            echo 'Block '.$block.' does not exist.
				<span id="adm_block_'.$rmpos.'" style="display:none;">'.$block.'</span>
				<a href="#" onClick="block_list_remove('.$rmpos.',\''.$side.'\')">
				<img src="admin/templates/default/images/delete.png" width="16px" height="16px" border="0">
				</a><br />';
            $rmpos++;
            continue;
        }
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


echo '<input type="hidden" name="blocks_left" id="adm_blocklist_left" value="'.implode(',', $blocks_left).'" />'."\n";
echo '<input type="hidden" name="blocks_right" id="adm_blocklist_right" value="'.implode(',', $blocks_right).'" />'."\n";
