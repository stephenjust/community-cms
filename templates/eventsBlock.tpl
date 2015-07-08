<div class="events_upcoming_block">
    <span class="header">{$title}:</span><br />
    <table class="events">
        <tr>
            <th>Date:</th><th>Heading:</th>
        </tr>
        {foreach from=$events item=event}
        <tr>
            <td>{$event->getStart()|date_format:"d/m/Y"}</td>
            <td>{$event->getTitle()}</td>
        </tr>
        {foreachelse}
            <tr>
                <td colspan="2">No events to display.</td>
            </tr>
        {/foreach}
    </table>
</div>