<div class="newsletter">
    {foreach from=$entries key=groupname item=group}
        <span class="group">{$groupname}</span><br />
        {foreach from=$group item=item}
            <a href="{$item->getPath()}">{$item->getLabel()}</a><br />
        {/foreach}
    {foreachelse}
        <p>No newsletters to display.</p>
    {/foreach}
</div>