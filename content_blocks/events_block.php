<?php
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

$event_block = new block;
$event_block->block_id = $block_info['id'];
$bl_return = NULL;
$event_block->get_block_information();
switch($event_block->attribute['mode']) {
	default:
		$bl_return .= '<strong>ERROR:</strong> Unsupported mode';
		break;
		unset($event_block);
		return $bl_return;
	case 'upcoming':
		$event_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
			WHERE (year = '.date('Y').' AND month = '.date('m').' AND day >= '.date('d').') OR
			(year = '.date('Y').' AND month > '.date('m').') OR (year > '.date('Y').')
			ORDER BY year ASC, month ASC, day ASC LIMIT '.$event_block->attribute['num'];
		$template_events = new template;
		$template_events->load_file('mini_events_upcoming');
		break;
	case 'past':
		$event_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
			WHERE (year = '.date('Y').' AND month = '.date('m').' AND day < '.date('d').') OR
			(year = '.date('Y').' AND month < '.date('m').') OR (year < '.date('Y').')
			ORDER BY year DESC, month DESC, day DESC LIMIT '.$event_block->attribute['num'];
		$template_events = new template;
		$template_events->load_file('mini_events_past');
		break;
}
$event_handle = $db->sql_query($event_query);
if ($db->error[$event_handle] === 1) {
	$bl_return .= 'Failed to load list of dates.<br />';
	return $bl_return;
}
if($db->sql_num_rows($event_handle) == 0) {
	$bl_return .= '&nbsp;&nbsp;No dates to list.<br />';
	return $bl_return;
}
$bl_single_event = $template_events->get_range('event');
$bl_all_events = NULL;
for($i = 1; $i <= $db->sql_num_rows($event_handle); $i++) {
	$event = $db->sql_fetch_assoc($event_handle);
	$template_single_event = clone $template_events;
	$template_single_event->template = $bl_single_event;
	$bl_date = $event['day'].'/'.$event['month'].'/'.$event['year'];
	$template_single_event->event_date = $bl_date;
	$template_single_event->event_heading = stripslashes($event['header']);
	$bl_all_events .= $template_single_event;
}
unset($event_handle);
unset($event);
$template_events->replace_range('event',$bl_all_events);
$bl_return .= $template_events;
unset($template_events);
unset($event_block);
return $bl_return;
?>