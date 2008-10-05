<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	switch($_GET['module']) {
		default:
			include('./admin/index.php');
			break;

		//
		// News Management Pages
		//

		case 'news':
			include('./admin/news.php');
			break;
		case 'news_new_article':
			include('./admin/news_new_article.php');
			break;
		case 'news_edit_article':
			include('./admin/news_edit_article.php');
			break;

		//
		// Newsletter Management Pages
		//

		case 'newsletter':
			include('./admin/newsletter.php');
			break;

		//
		// Calendar Management Pages
		//

		case 'calendar':
			include('./admin/calendar.php');
			break;
		case 'calendar_new_date':
			include('./admin/calendar_new_date.php');
			break;
		case 'calendar_settings':
			include('./admin/calendar_settings.php');
			break;

		//
		// Page Management Pages
		//

		case 'pages':
			include('./admin/page.php');
			break;

		//
		// File Management Pages
		//

		case 'upload':
			include('./admin/upload.php');
			break;
		case 'filemanager':
			include('./admin/filemanager.php');
			break;
			
		//
		// User Management Pages
		//
		
		case 'user':
			include('./admin/user.php');
			break;
		case 'user_create':
			include('./admin/user_create.php');
			break;
		case 'user_permission':
			include('./admin/user_permission.php');
			break;
		}
	?>