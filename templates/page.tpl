<!DOCTYPE html>
<html lang="en">
	<head>
		<title>{$page->getTitle()} - {$page->getSiteName()}</title>
		{$page->getMetadata()}
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
		<script type="text/javascript" src="/scripts/jquery.js"></script>
		<script type="text/javascript" src="/scripts/jquery-ui.js"></script>
		<script type="text/javascript" src="/scripts/jquery-cycle.js"></script>
		<script type="text/javascript" src="/scripts/jquery-fe.js"></script>
		<script type="text/javascript" src="/scripts/ajax.js"></script>
		<script type="text/javascript" src="/scripts/cms_fe.js"></script>
		<link rel="stylesheet" type="text/css" href="/templates/css/style.css" />
		<link rel="stylesheet" type="text/css" href="/templates/css/print.css" media="print" />
	</head>
	<body>
		{include file="pageHeader.tpl"}
		{include file="pageBody.tpl"}
		{include file="pageFooter.tpl"}
	</body>
</html>