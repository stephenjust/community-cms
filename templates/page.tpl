<!DOCTYPE html>
<html lang="en">
	<head>
		<title>{$page->getTitle()} - {$page->getSiteName()}</title>
		{$page->getMetadata()}
                <script data-main="/scripts/cms" src="/scripts/require.js"></script>
		<link rel="stylesheet" type="text/css" href="/templates/css/style.css" />
		<link rel="stylesheet" type="text/css" href="/templates/css/print.css" media="print" />
	</head>
	<body>
		{include file="pageHeader.tpl"}
		{include file="pageBody.tpl"}
		{include file="pageFooter.tpl"}
	</body>
</html>