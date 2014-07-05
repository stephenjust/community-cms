{function name='menu' level=0}
	{if count($data->getChildren())}
		{if $level == 0}
			<ul id="nav-menu" class="nav_menu">
		{else}
			<ul id="nav-menu-sub-{$data->getID()}" class="nav_submenu">
		{/if}
		{foreach from=$data->getChildren() item=menu_item}
			{if $menu_item->getChildren()}
				{assign 'class' "`$class` haschild"}
			{/if}
			{if $menu_item->isCurrent()}
				{assign 'class' 'menuitem_current'}
			{else}
				{assign 'class' 'menuitem'}
			{/if}
			{if !$menu_item->getChildren()}
				<li id="menuitem_{$menu_item->getID()}" class="{$class}"><a href="{$menu_item->getTarget()}">{$menu_item->getLabel()}</a></li>
			{else}
				<li id="menuitem_{$menu_item->getID()}" class="{$class}_haschild">{menu data=$menu_item level=$level+1}<a href="{$menu_item->getTarget()}">{$menu_item->getLabel()}</a><div class="childarrow"></div></li>
			{/if}
		{/foreach}
		</ul>
	{/if}
{/function}

{menu data=$menu}