<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}
include (ROOT.'functions/calendar.php');

echo '<h1>Import Events</h1>'."\n";
if (!isset($_GET['id'])) {
	echo 'No event selected.<br />';
	return 1;
}
if (!is_numeric($_GET['id'])) {
	echo 'Invalid source ID.<br />';
	return 1;
}
$source_query = 'SELECT * FROM `' . CALENDAR_SOURCES_TABLE . '`
	WHERE `id` = '.$_GET['id'].' LIMIT 1';
$source_handle = $db->sql_query($source_query);
if ($db->error[$source_handle] === 1) {
	echo 'Failed to read source information.<br />';
	return 1;
}
if ($db->sql_num_rows($source_handle) != 1) {
	echo 'The source you selected does not exist.<br />';
	return 1;
}
$source = $db->sql_fetch_assoc($source_handle);
echo 'Fetching source \''.$source['desc'].'\'... '."\n";
$handle = fopen($source['url'],'r');
echo 'Done.<br />'."\n";

echo 'Reading source file... '."\n";
$contents = stream_get_contents($handle);
echo 'Done.<br />'."\n";

fclose($handle);

$contents = explode("\n",$contents);
$first_line = array_shift($contents);
$first_line = str_replace(array("\n","\r"),NULL,$first_line);
if ($first_line != "BEGIN:VCALENDAR") {
	echo 'Invalid source format.<br />'."\n";
	return 1;
}
while (isset($contents[0])) {
	$current_line = array_shift($contents);
	$current_line = str_replace(array("\n","\r"),NULL,$current_line);
	if (eregi('^PRODID:.*',$current_line) ||
		eregi('^VERSION:.*',$current_line) ||
		eregi('^CALSCALE:.*',$current_line) ||
		eregi('^METHOD:.*',$current_line) ||
		eregi('^X-WR-.*',$current_line) ||
		eregi('^TRANSP:.*',$current_line) ||
		eregi('^STATUS:.*',$current_line) ||
		eregi('^CREATED:.*',$current_line) ||
		eregi('^LAST-MODIFIED:.*',$current_line) ||
		eregi('^SEQUENCE:.*',$current_line) ||
		eregi('^CLASS:.*',$current_line) ||
		eregi('^DTSTAMP:.*',$current_line)) {

	} elseif ($current_line == 'BEGIN:VEVENT') {
		$active_event = 1;
		// Reset all vars...
		$all_day = 0;
		$desc = NULL;
		$location = NULL;
		$summary = NULL;
		$start = NULL;
		$end = NULL;
		$uid = NULL;
		echo '<div class="importable_event" style="border-bottom: 1px solid #000000;">';
	} elseif ($current_line == 'END:VEVENT') {
		// Check if this event already exists (we need a UID for this)
		if ($uid != NULL) {
			$check_query = 'SELECT * FROM `' . CALENDAR_TABLE . '`
				WHERE `imported` = \''.$uid.'\' LIMIT 1';
			$check_handle = $db->sql_query($check_query);
			if ($db->error[$check_handle] === 1) {
				echo 'Failed to check if this event has already been imported.';
				continue;
			}
			if ($db->sql_num_rows($check_handle) == 1) {
				// This is a duplicate event
				$debug->add_trace('Duplicate event \''.$summary.'\'',false,'calendar_import.php');
				continue;
			}
		}
		// Parse available info...
		echo '<strong>'.$summary.'</strong><br />'."\n";
		echo date('Y-m-d H:i:s',$start).' - '.date('Y-m-d H:i:s',$end).'<br />';
		echo $desc.'<br />';
		echo 'Location: '.$location.'<br />';
		echo 'UID: '.$uid.'<br />';
// ----------------------------------------------------------------------------
// TODO
// ----------------------------------------------------------------------------
		$active_event = 0;
		echo '</div>';
	} elseif (eregi('^SUMMARY:.*',$current_line)) {
		$summary = str_replace('SUMMARY:',NULL,$current_line);

// ----------------------------------------------------------------------------

	} elseif (eregi('^DTSTART;VALUE=DATE:[0-9]+',$current_line)) {
		$start = str_replace('DTSTART;VALUE=DATE:',NULL,$current_line);
		$syear = substr($start,0,4);
		$smonth = substr($start,4,2);
		$sday = substr($start,6,2);
		$allday = 1;
		$start = mktime(0,0,0,$smonth,$sday,$syear);
		unset($syear);
		unset($smonth);
		unset($sday);

	} elseif (eregi('^DTSTART:[0-9]{8}T[0-9]{6}Z',$current_line)) {
		$start = str_replace(array('DTSTART:','T','Z'),NULL,$current_line);
		$syear = substr($start,0,4);
		$smonth = substr($start,4,2);
		$sday = substr($start,6,2);
		$shour = substr($start,8,2);
		$smin = substr($start,10,2);
		$allday = 1;
		$start = mktime($shour,$smin,0,$smonth,$sday,$syear);
		unset($syear);
		unset($smonth);
		unset($sday);
		unset($shour);
		unset($smin);

// ----------------------------------------------------------------------------

	} elseif (eregi('^DTEND;VALUE=DATE:[0-9]+',$current_line)) {
		$end = str_replace('DTEND;VALUE=DATE:',NULL,$current_line);
		$eyear = substr($end,0,4);
		$emonth = substr($end,4,2);
		$eday = substr($end,6,2);
		$allday = 1;
		$end = mktime(0,0,0,$emonth,$eday - 1,$eyear);
		unset($eyear);
		unset($emonth);
		unset($eday);

	} elseif (eregi('^DTEND:[0-9]{8}T[0-9]{6}Z',$current_line)) {
		$end = str_replace(array('DTEND:','T','Z'),NULL,$current_line);
		$eyear = substr($end,0,4);
		$emonth = substr($end,4,2);
		$eday = substr($end,6,2);
		$ehour = substr($end,8,2);
		$emin = substr($end,10,2);
		$allday = 1;
		$end = mktime($ehour,$emin,0,$emonth,$eday,$eyear);
		unset($eyear);
		unset($emonth);
		unset($eday);
		unset($ehour);
		unset($emin);

// ----------------------------------------------------------------------------

	} elseif (eregi('^UID:.*',$current_line)) {
		$uid = str_replace('UID:',NULL,$current_line);
	} elseif (eregi('^LOCATION:.*',$current_line)) {
		$location = str_replace('LOCATION:',NULL,$current_line);
	} elseif (eregi('^DESCRIPTION:.*',$current_line)) {
		$desc = str_replace('DESCRIPTION:',NULL,$current_line);
	} elseif ($current_line == 'END:VCALENDAR') {
		break;
	} else {
		
	}
}

?>
