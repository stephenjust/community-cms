<?php
/**
 * Community CMS Installer
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.install
 */

$content = '<h1>Step 5: Initial Set-Up</h1>'."\n";
$content .= '<form method="post" action="index.php?page=6">'."\n";
$content .= '<table id="init_settings">'."\n";
// ----------------------------------------------------------------------------
$content .= '<tr><td>Website Name</td><td><input type="text" name="sitename" id="sitename" value="Community CMS Powered Web Site" /></td></tr>';
$content .= '<tr><td>Administrator Username</td><td><input type="text" name="adm_user" id="adm_user" /></td></tr>';
$content .= '<tr><td>Administrator Password</td><td><input type="text" name="adm_pass" id="adm_pass" /></td></tr>'."\n";
$content .= '<tr><td>Administrator Email</td><td><input type="text" name="adm_email" id="adm_email" /></td></tr>'."\n";
$content .= '<tr><td><input type="submit" value="Next" /></td><td></td></tr>'."\n";
// ----------------------------------------------------------------------------
$content .= '</table>'."\n";
$content .= '</form>'."\n";
?>
