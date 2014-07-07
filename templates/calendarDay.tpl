<a href="?{$page->getURLReference()}&amp;view=month&amp;m={$page->getMonth()}&amp;y={$page->getYear()}">Back to month view</a><br /><br />
<table class="cal_day-table" cellspacing="0">
	<thead>
		<tr>
			<th>Time</th>
			<th>Heading</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$events item='event'}
		<tr itemscope itemtype="http://schema.org/Event">
			<td class='time'>
				{if $event->isAllDay()}
					All day
				{else}
					{$event->getStart()|date_format:$time_format} - {$event->getEnd()|date_format:$time_format}
				{/if}
				<meta itemprop="startDate" content="{$event->getStart()|date_format:'Y-m-d'}" />
			</td>
			<td class='head' itemprop="name"><a itemprop="url" href='?{$page->getURLReference()}&amp;view=event&amp;a={$event->getID()}'>{$event->getTitle()}</a></td>
			<td class='description'>{$event->getTruncatedDescription()}</td>
		</tr>
		{foreachelse}
			<tr>
				<td colspan="3">There are no events on this date.</td>
			</tr>
		{/foreach}
	</tbody>
</table>