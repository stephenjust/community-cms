{foreach from=$articles item=article}
	<div class="news_item_container">
		<a name="article-{$article->getID()}"></a>
		<div class="news_item">
			{$article->getEditBar()}
			{if $article->getImage()}
				<img src='/files/{$article->getImage()}' class="news_image" />
			{/if}
			<span class="news_title">
				{$article->getTitle()} {if !$article->published()}<span class="news_not_published_label">NOT PUBLISHED</span>{/if}
			</span><br />
			{if $article->isDateVisible() || $article->isAuthorVisible()}
			<div class="news_item_top">
				{if $article->isDateVisible()}{$article->getDate()|date_format:"M j Y"}{/if}
				{if $article->isAuthorVisible()}Posted by {$article->getAuthor()}{/if}
			</div>
			{/if}
			<div id="description_{$article->getID()}" class="news_content">{$article->getContent()}</div>
		</div>
	</div>
	<br />
{foreachelse}
	<p>There are no articles to be displayed.</p>
{/foreach}
{$pagination}