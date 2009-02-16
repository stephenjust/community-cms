<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	global $site_info;
	$event_block = new block;
	$event_block->block_id = $block_info['id'];
	$bl_return = NULL;
	$event_block->get_block_information();
	switch($event_block->attribute['mode']) {
		default:
			$bl_return .= '<strong>ERROR:</strong> Unsupported mode';
			break;
		case 'upcoming':
			$upcoming_event_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar 
WHERE year >= '.date('Y').' AND month >= '.date('m').' AND day >= '.date('d').'
ORDER BY year ASC, month ASC, day ASC LIMIT '.$event_block->attribute['num'];
			$upcoming_event_handle = $db->query($upcoming_event_query);
			if(!$upcoming_event_handle) {
				$bl_return .= 'Failed to load upcoming dates.';
				return $bl_return;
				}
			if($upcoming_event_handle->num_rows == 0) {
				$bl_return .= '&nbsp;&nbsp;No upcoming dates.';
				return $bl_return;
				}
			$template_upcoming_events = new template;
			$template_upcoming_events->load_file('mini_events_upcoming');
			$bl_single_event = $template_upcoming_events->get_range('event');
			$bl_all_events = NULL;
			for($i = 1; $i <= $upcoming_event_handle->num_rows; $i++) {
				$upcoming_event = $upcoming_event_handle->fetch_assoc();
				$template_single_event = clone $template_upcoming_events;
				$template_single_event->template = $bl_single_event;
				$bl_date = $upcoming_event['day'].'/'.$upcoming_event['month'].'/'.$upcoming_event['year'];
				$template_single_event->event_date = $bl_date;
				$template_single_event->event_heading = stripslashes($upcoming_event['header']);
				$bl_all_events .= $template_single_event;
				}
			unset($upcoming_event_handle);
			unset($upcoming_event);
			$template_upcoming_events->replace_range('event',$bl_all_events);
			$bl_return .= $template_upcoming_events;
			unset($template_upcoming_events);
			break;
		case 'past':
			$bl_return .= 'Past events coming soon.';
			break;
		}
	unset($event_block);
	return $bl_return;
	?>