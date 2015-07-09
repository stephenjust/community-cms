<div class="news_block">
    <div id="news-scroller">
        <div id="news-scroller-content">
            {foreach from=$articles item=article}
                <div>
                    <span class="title">{$article->getTitle()}</span><br />
                        {if $article->isAuthorVisible()}<span class="author">{$article->getAuthor()}</span><br />{/if}
                        {if $article->isDateVisible()}{$article->getDate()|date_format:"M j Y"}<br />{/if}
                    <div id="description_{$article->getID()}" class="content">{$article->getContent()}</div>
                </div>
            {/foreach}
        </div>
    </div>
    <div id="scroll_prev">&lt;</div> <div id="scroll_next">&gt;</div>
</div>
