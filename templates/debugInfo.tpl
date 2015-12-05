<div id="debug_info">
    <h3>Debug Information</h3>
    <div id="debug_logs">
        <h4>Log Messages</h4>
        {foreach from=$logs item=item}
            <span class="item">{$item}</span><br />
        {foreachelse}
            <span class="item">No items to display</span><br />
        {/foreach}
    </div>
    <div id="debug_queries">
        <h4>Failed Queries</h4>
        {foreach from=$failed_queries item=item}
            <span class="item">{$item}</span><br />
        {foreachelse}
            <span class="item">No items to display</span><br />
        {/foreach}
        <h4>All Queries</h4>
        {foreach from=$all_queries item=item}
            <span class="item">{$item}</span><br />
        {foreachelse}
            <span class="item">No items to display</span><br />
        {/foreach}
    </div>
</div>