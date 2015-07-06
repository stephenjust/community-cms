<div class="events_upcoming_block">
    <span class="header">Event Categories:</span><br />
    <table class="events">
        {foreach from=$categories item=category}
            <tr>
                <td>
                    <img src="<!-- $IMAGE_PATH$ -->icon_{$category->getIcon()}.png"
                         alt="{$category->getName()}" />
                </td>
                <td>{$category->getName()}</td>
            </tr>
        {foreachelse}
            <tr>
                <td>No categories to display.</td>
            </tr>
        {/foreach}
    </table>
</div>