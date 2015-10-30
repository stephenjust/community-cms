{assign 'prev_level' 0}
<ul id="nav-menu" class="nav_menu">
{foreach $pages item=page}
    {if ($page->getLevel() > $prev_level)}
        <ul id="nav-menu-sub-{$page->getId()}" class="nav_submenu">
    {elseif ($page->getLevel() < $prev_level)}
        </ul>
        </li>
    {else}
        </li>
    {/if}
    {assign 'prev_level' $page->getLevel()}

    {assign 'class' 'menuitem'}
    {if $page->getId() == $current}
        {assign 'class' "`$class` current"}
    {/if}
    {if $page->getChildren()}
        {assign 'class' "`$class` haschild"}
    {/if}

    <li id="menuitem_{$page->getId()}" class="{$class}">
        <a href="{$page->getUrl()}">{$page->getTitle()}</a>
        {if $page->getChildren()}
            <div class="childarrow"></div>
        {/if}
{/foreach}
{if count($pages)}
</li>
{/if}
</ul>
