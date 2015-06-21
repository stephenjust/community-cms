<div class="pagination">
    {if $p->hasPrev()}
        <a href="{$p->prevPage()}">Previous Page</a>
    {/if}
    {if $p->hasNext()}
        <a href="{$p->nextPage()}">Next Page</a>
    {/if}
</div>