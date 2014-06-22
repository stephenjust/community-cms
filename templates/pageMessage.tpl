{foreach from=$pageMessage item=pm}
    <div class="page_message">{$pm->getContent()}</div>
	<br />
	<br />
{/foreach}