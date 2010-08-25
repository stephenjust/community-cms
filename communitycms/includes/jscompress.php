<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/************************************************************************
 * CSS and Javascript Combinator 0.5
 * Copyright 2006 by Niels Leenheer
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

$cache 	  = true;
$cachedir = dirname(__FILE__) . '/../tmp';
$jsdir    = dirname(__FILE__) . '/..';

// List of JS files
$js_elements = array(
	'admin/scripts/ajax.js',
	'scripts/jquery.js',
	'scripts/jquery-ui.js',
	'scripts/jquery-custom.js');

// Determine directory to use
$base = realpath($jsdir);

// Set type
$type = 'javascript';

// Determine last modification date of the files
$lastmodified = 0;
while (list(,$js_element) = each($js_elements)) {
	$path = realpath($base . '/' . $js_element);

	if (($type == 'javascript' && substr($path, -3) != '.js') ||
		($type == 'css' && substr($path, -4) != '.css')) {
		header ("HTTP/1.0 403 Forbidden");
		echo 'Forbidden 1, '.substr($path, -3).', '.$path.', '.$base.', '.$js_element;
		exit;
	}

	if (substr($path, 0, strlen($base)) != $base || !file_exists($path)) {
		header ("HTTP/1.0 404 Not Found");
		echo 'Not Found 1';
		exit;
	}

	$lastmodified = max($lastmodified, filemtime($path));
}

// Send Etag hash
$hash = $lastmodified . '-' . md5(implode(',',$js_elements));
header ("Etag: \"" . $hash . "\"");

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
	stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"')
{
	// Return visit and no modifications, so do not send anything
	header ("HTTP/1.0 304 Not Modified");
	header ('Content-Length: 0');
}
else
{
	// First time visit or files were modified
	if ($cache)
	{
		// Determine supported compression method
		$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
		$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

		// Determine used compression method
		$encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

		// Check for buggy versions of Internet Explorer
		if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') &&
			preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
			$version = floatval($matches[1]);

			if ($version < 6)
				$encoding = 'none';

			if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1'))
				$encoding = 'none';
		}

		// Try the cache first to see if the combined files were already generated
		$cachefile = 'cache-' . $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');

		if (file_exists($cachedir . '/' . $cachefile)) {
			if ($fp = fopen($cachedir . '/' . $cachefile, 'rb')) {

				if ($encoding != 'none') {
					header ("Content-Encoding: " . $encoding);
				}

				header ("Content-Type: text/" . $type);
				header ("Content-Length: " . filesize($cachedir . '/' . $cachefile));

				fpassthru($fp);
				fclose($fp);
				exit;
			}
		}
	}

	// Get contents of the files
	$contents = '';
	reset($js_elements);
	while (list(,$js_element) = each($js_elements)) {
		$path = realpath($base . '/' . $js_element);
		$contents .= "\n\n" . file_get_contents($path);
	}

	// Send Content-Type
	header ("Content-Type: text/" . $type);

	if (isset($encoding) && $encoding != 'none')
	{
		// Send compressed contents
		$contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
		header ("Content-Encoding: " . $encoding);
		header ('Content-Length: ' . strlen($contents));
		echo $contents;
	}
	else
	{
		// Send regular contents
		header ('Content-Length: ' . strlen($contents));
		echo $contents;
	}

	// Store cache
	if ($cache) {
		if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
			fwrite($fp, $contents);
			fclose($fp);
		}
	}
}

?>