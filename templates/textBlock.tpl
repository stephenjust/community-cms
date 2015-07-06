<div id="minitext_block_{$article->getID()}" class="news_block">
	<span class="news_block_title">{$article->getTitle()}</span><br />
        {if $show_border}
            <table class="news_block_container"><tr><td>
        {/if}
	{if $article->isAuthorVisible()}<span class="news_block_author">{$article->getAuthor()}</span><br />{/if}
	{if $article->isDateVisible()}{$article->getDate()|date_format:"M j Y"}<br />{/if}
	<div id="description_{$article->getID()}" class="news_content">{$article->getContent()}</div>
        {if $show_border}
            </td></tr></table>
        {/if}
</div>