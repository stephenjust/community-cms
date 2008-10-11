<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	function admin_nav() {
		global $CONFIG;
		global $db;
		$result = NULL;
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'admin_pages WHERE on_menu = 1 ORDER BY category,label ASC';
		$page_handle = $db->query($page_query);
		$i = 1;
		$header = NULL;
		while($page_handle->num_rows >= $i) {
			$page_list = $page_handle->fetch_assoc();
			$last_header = $header;
			$header = $page_list['category'];
			if($header != $last_header) {
				$result .= '<span class="nav_header">'.$page_list['category'].'</span><br />';
				}
			$result .= '<a href="admin.php?module='.$page_list['file'].'">'.$page_list['label'].'</a><br />';
			$i++;
			}
		return $result;
		}
	$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'admin_pages WHERE file = "'.$_GET['module'].'" LIMIT 1';
	$page_handle = $db->query($page_query);
	if($page_handle->num_rows != 1) {
		include('./admin/index.php');
		} else {
		$page = $page_handle->fetch_assoc();
		include('./admin/'.$page['file'].'.php');
		}
	switch($_GET['module']) {

		//
		// Newsletter Management Pages
		//

		case 'newsletter':
			include('./admin/newsletter.php');
			break;

		//
		// Page Management Pages
		//

		case 'pages':
			include('./admin/page.php');
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
		case 'user_edit':
			include('./admin/user_edit.php');
			break;
		case 'user_permission':
			include('./admin/user_permission.php');
			break;
		}
	?>