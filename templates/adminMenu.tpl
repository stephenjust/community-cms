<div id="menu">
    {foreach from=$menu.categories item=category}
        <div>
            <h3>{$category.name}</h3>
            <div>
                {foreach from=$category.pages item=page}
                    {if array_key_exists("url", $page)}
                        <a href="{$page.url}">{$page.label}</a><br />
                    {elseif array_key_exists("module", $page)}
                        <a href="admin.php?module={$page.module}">{$page.label}</a><br />
                    {/if}
                {/foreach}
            </div>
        </div>
    {/foreach}
</div>