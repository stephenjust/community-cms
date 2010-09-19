<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}
/**
 * Include funtions necessary to perform operations on this page
 */
include (ROOT.'functions/calendar.php');

if (!$acl->check_permission('adm_calendar_import')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

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
$event_count = 0;
$active_event = 0;
while (isset($contents[0])) {
	$current_line = array_shift($contents);
	$current_line = str_replace(array("\n","\r"),NULL,$current_line);
	if (preg_match('/^PRODID:.*/i',$current_line) ||
		preg_match('/^VERSION:.*/i',$current_line) ||
		preg_match('/^CALSCALE:.*/i',$current_line) ||
		preg_match('/^METHOD:.*/i',$current_line) ||
		preg_match('/^X-WR-.*/i',$current_line) ||
		preg_match('/^TRANSP:.*/i',$current_line) ||
		preg_match('/^STATUS:.*/i',$current_line) ||
		preg_match('/^CREATED:.*/i',$current_line) ||
		preg_match('/^LAST-MODIFIED:.*/i',$current_line) ||
		preg_match('/^SEQUENCE:.*/i',$current_line) ||
		preg_match('/^CLASS:.*/i',$current_line) ||
		preg_match('/^DTSTAMP:.*/i',$current_line)) {

	} elseif ($current_line == 'BEGIN:VEVENT') {
		if ($active_event == 1) {
			die ('Malformed iCal file');
		}
		$active_event = 1;
		// Reset all vars...
		$all_day = 0;
		$desc = NULL;
		$location = NULL;
		$summary = NULL;
		$start = NULL;
		$end = NULL;
		$uid = NULL;
		$event_count++;
		echo '<div class="importable_event" id="event-'.$event_count.'">'."\n";
		echo '<button onClick="import_event(\'event-'.$event_count.'\')" style="float: right;">Import</button>'."\n";
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
				$debug->add_trace('Duplicate event \''.$summary.'\'',false);
				continue;
			}
		}
		// Parse available info...
		echo '<strong><span id="event-'.$event_count.'-title">'.$summary.'</span></strong><br />'."\n";
		echo date('Y-m-d H:i:s',$start).' - '.date('Y-m-d H:i:s',$end).'<br />';
		echo strip_tags($desc).'<br />';
		if ($location != NULL) {
			echo 'Location: '.$location.'<br />';
		}
		echo 'UID: '.$uid.'<br />';
// ----------------------------------------------------------------------------
// TODO
// ----------------------------------------------------------------------------
		$active_event = 0;
		echo '</div>';
	} elseif (preg_match('/^SUMMARY:.*/i',$current_line)) {
		$summary = str_replace('SUMMARY:',NULL,$current_line);

// ----------------------------------------------------------------------------

	} elseif (preg_match('/^DTSTART;VALUE=DATE:[0-9]+/i',$current_line)) {
		$start = str_replace('DTSTART;VALUE=DATE:',NULL,$current_line);
		$syear = substr($start,0,4);
		$smonth = substr($start,4,2);
		$sday = substr($start,6,2);
		$allday = 1;
		$start = mktime(0,0,0,$smonth,$sday,$syear);
		unset($syear);
		unset($smonth);
		unset($sday);

	} elseif (preg_match('/^DTSTART:[0-9]{8}T[0-9]{6}Z/i',$current_line)) {
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

	} elseif (preg_match('/^DTEND;VALUE=DATE:[0-9]+/i',$current_line)) {
		$end = str_replace('DTEND;VALUE=DATE:',NULL,$current_line);
		$eyear = substr($end,0,4);
		$emonth = substr($end,4,2);
		$eday = substr($end,6,2);
		$allday = 1;
		$end = mktime(0,0,0,$emonth,$eday - 1,$eyear);
		unset($eyear);
		unset($emonth);
		unset($eday);

	} elseif (preg_match('/^DTEND:[0-9]{8}T[0-9]{6}Z/i',$current_line)) {
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

	} elseif (preg_match('/^UID:.*/i',$current_line)) {
		$uid = str_replace('UID:',NULL,$current_line);
	} elseif (preg_match('/^LOCATION:.*/i',$current_line)) {
		$location = str_replace('LOCATION:',NULL,$current_line);
		if (strlen(trim($location)) == 0) {
			$location = NULL;
		}
	} elseif (preg_match('/^DESCRIPTION:.*/i',$current_line)) {
		$desc = str_replace('DESCRIPTION:',NULL,$current_line);
	} elseif ($current_line == 'END:VCALENDAR') {
		break;
	} else {
		
	}
}

?>
