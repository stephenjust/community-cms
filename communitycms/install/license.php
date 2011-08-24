<?php
/**
 * Community CMS Installer
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */
$content = '<h1>License Agreement</h1>'."\n";
$content .= 'To use Community CMS, you must agree to the following license terms.
	Community CMS itself is licensed under the GNU GPL v2, although other modules
	used by the CMS may be licensed under other terms. Make sure that you read and
	understand each of the licenses before you agree to them.<br /><br />'."\n";

// GNU GPL
// (for Community CMS, jQuery, jQuery-UI)
$content .= '<div class="license_header">GNU General Public License 2</div>'."\n";
$content .= '<div class="license_applicable">Applies to Community CMS, jQuery, jQuery-UI</div>'."\n";
$content .= '<div class="license_body"><textarea>'."\n";
$content .= file_get_contents(ROOT.'docs/license-gpl2.txt');
$content .= '</textarea></div>'."\n";

// GNU LGPL
// (for TinyMCE)
$content .= '<div class="license_header">GNU Lesser General Public License 2.1</div>'."\n";
$content .= '<div class="license_applicable">Applies to TinyMCE</div>'."\n";
$content .= '<div class="license_body"><textarea>'."\n";
$content .= file_get_contents(ROOT.'docs/license-lgpl21.txt');
$content .= '</textarea></div>'."\n";

// BSD License
// (for Tar plugin)
$content .= '<div class="license_header">New BSD License (Tar)</div>'."\n";
$content .= '<div class="license_applicable">Applies to Archive_Tar</div>'."\n";
$content .= '<div class="license_body"><textarea>'."\n";
$content .= file_get_contents(ROOT.'docs/license-bsd-tar.txt');
$content .= '</textarea></div>'."\n";

// MIT-Style License
// (for jQuery plugins)
$content .= '<div class="license_header">MIT License</div>'."\n";
$content .= '<div class="license_applicable">Applies to jQuery "multi-ddm", Autocomplete Plug-Ins, cycle plugin</div>'."\n";
$content .= '<div class="license_body"><textarea>'."\n";
$content .= file_get_contents(ROOT.'docs/license-mit.txt');
$content .= '</textarea></div>'."\n";

// Creative Commons Attribution 2.5
// (for FamFamFam silk icon set)
$content .= '<div class="license_header">Creative Commons Attribution 2.5</div>'."\n";
$content .= '<div class="license_applicable">Applies to FamFamFam \'Silk\' Icon Set</div>'."\n";
$content .= '<div class="license_body"><a href="http://creativecommons.org/licenses/by/2.5/">View License</a></div>'."\n";

$content .= '<form method="POST" action="index.php?page=1"><input type="submit" value="Agree and Continue" /></form>';
?>