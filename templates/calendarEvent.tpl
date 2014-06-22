<a href="?{$page_url}&amp;view=month&amp;m={$event->getStart()|date_format:"n"}&amp;y={$event->getStart()|date_format:"Y"}">Back to month view</a><br />
<a href="?{$page_url}&amp;view=day&amp;d={$event->getStart()|date_format:"d"}&amp;m={$event->getStart()|date_format:"n"}&amp;y={$event->getStart()|date_format:"Y"}">Back to day view</a><br />
<div class="cal_event" itemscope itemtype="http://schema.org/Event">
	{if isset($editbar)}{$editbar}{/if}
	<h1 class="cal_event-heading" itemprop="name">{$event->getTitle()}</h1>
	{if $event->getImage()}
	<div class="cal_event-image">
		<img src="{$event->getImage()}" class="calendar_event_image" /> 
	</div>
	{/if}
	<div class="cal_event-content">
		{if $show_author}
		<p class="cal_event-author">Posted by {$event->getAuthor()}</p>
		{/if}
		<p class="cal_event-time">
			{if $event->getStart() eq $event->getEnd()}
			All day, {$event->getStart()|date_format:"l, F j Y"}
			{else}
			{$event->getStart()|date_format:$time_format} - {$event->getEnd()|date_format:$time_format} {$event->getStart()|date_format:"l, F j Y"}
			{/if}
		</p>
		<meta itemprop="startDate" content="{$event->getStart()|date_format:"Y-m-d"}" />
		{if !$event->getCategoryHide()}
		<strong>Event category:</strong> {$event->getCategory()}<br />
		{/if}
		<br />
		<span itemprop="description">{$event->getDescription()}</span><br />
		<br />
		{if !$event->getLocationHide() and $event->getLocation()}
		<strong>Location:</strong> <span itemprop="location">{$event->getLocation()}</span>
		{/if}
	</div>
</div>
