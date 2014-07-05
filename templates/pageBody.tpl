<div id="left">
	<div class="nav_menu_top"></div>
	<div class="nav_menu">
		{include file='navMenu.tpl' menu=$page->getNavMenu()}
	</div>
	<div class="nav_menu">
		{$page->getUserBox()}
	</div>
	<div class="nav_menu_bottom"></div>
	<div class="left_content">
		{$page->getLeftContent()}
	</div>
</div>
<div id="right">
	{$page->getRightContent()}
</div>
<div id="center">
	<div id="page_path">{$page->getPagePath()}</div>
	{$page->getEditBar()}
	{if $page->isTitleVisible()}<div id="body_title"><h1>{$page->getTitle()}</h1></div>{/if}
	{if $page->getNotifications()}<div class="notification">{$page->getNotifications()}</div>{/if}
	{$page->getPageMessage()}
	{$page->getContent()}
</div>